<?php
/**
 * Email renewals functions
 *
 * Some of the renewals functions are from Pippin Williamson and his Software License Plugin.
 * I have modified some of them and added new ones.
 *
 * @author      Sami Keijonen
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDMembers\Renewals
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if send renewal reminders is enabled
 *
 * @since  1.0.0
 * @return boolean Renewal reminders
 */
function edd_members_renewals_allowed() {
	if( edd_get_option( 'edd_members_send_renewal_reminders' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Retrieve renewal notices
 *
 * @since  1.0.0
 * @return array Renewal notice periods
 */
function edd_members_get_renewal_notice_periods() {
	$periods = array(
		'+1day'    => __( 'One day before expiration', 'edd-members' ),
		'+2days'   => __( 'Two days before expiration', 'edd-members' ),
		'+3days'   => __( 'Three days before expiration', 'edd-members' ),
		'+1week'   => __( 'One week before expiration', 'edd-members' ),
		'+2weeks'  => __( 'Two weeks before expiration', 'edd-members' ),
		'+1month'  => __( 'One month before expiration', 'edd-members' ),
		'+2months' => __( 'Two months before expiration', 'edd-members' ),
		'+3months' => __( 'Three months before expiration', 'edd-members' ),
		'expired'  => __( 'At the time of expiration', 'edd-members' ),
		'-1day'    => __( 'One day after expiration', 'edd-members' ),
		'-2days'   => __( 'Two days after expiration', 'edd-members' ),
		'-3days'   => __( 'Three days after expiration', 'edd-members' ),
		'-1week'   => __( 'One week after expiration', 'edd-members' ),
		'-2weeks'  => __( 'Two weeks after expiration', 'edd-members' ),
		'-1month'  => __( 'One month after expiration', 'edd-members' ),
		'-2months' => __( 'Two months after expiration', 'edd-members' ),
		'-3months' => __( 'Three months after expiration', 'edd-members' ),
	);
	return apply_filters( 'edd_members_get_renewal_notice_periods', $periods );
}

/**
 * Retrieve the renewal label for a notice
 *
 * @since  1.0.0
 * @return String
 */
function edd_members_get_renewal_notice_period_label( $notice_id = 0 ) {
	
	$notice  = edd_members_get_renewal_notice( $notice_id );
	$periods = edd_members_get_renewal_notice_periods();
	$label   = $periods[ $notice['send_period'] ];

	return apply_filters( 'edd_members_get_renewal_notice_period_label', $label, $notice_id );
}

/**
 * Retrieve a renewal notice
 *
 * @since  1.0.0
 * @return array Renewal notice details
 */
function edd_members_get_renewal_notice( $notice_id = 0 ) {

	$notices  = edd_members_get_renewal_notices();

	$defaults = array(
		'subject'      => __( 'Your membership is about to expire', 'edd-members' ),
		'send_period'  => '+1week',
		'message'      => 'Hello {name},

Your membership is about to expire.

If you wish to renew your membership, simply click the link below and follow the instructions.

Your license expires on: {edd_members_expiration}.

Renew now: {edd_members_page}.'
	);

	$notice   = isset( $notices[ $notice_id ] ) ? $notices[ $notice_id ] : $notices[0];

	$notice   = wp_parse_args( $notice, $defaults );

	return apply_filters( 'edd_members_renewal_notice', $notice, $notice_id );

}

/**
 * Retrieve renewal notice periods
 *
 * @since  1.0.0
 * @return array Renewal notices defined in settings
 */
function edd_members_get_renewal_notices() {

	// Get notices from options
	$notices = get_option( 'edd_members_renewal_notices', array() );

	if( empty( $notices ) ) {

		$message = 'Hello {name},

Your membership is about to expire.

If you wish to renew your license, simply click the link below and follow the instructions.

Your license expires on: {edd_members_expiration}.

Renew now: {edd_members_page}.';

		$notices[0] = array(
			'send_period' => '+1week',
			'subject'     => __( 'Your membership is about to expire', 'edd-members' ),
			'message'     => $message
		);

	}

	return apply_filters( 'edd_members_get_renewal_notices', $notices );
}

/**
 * Check email reminders once a day.
 *
 * Uses EDD daily cron job. Send email notification id expire date and notice period "match".
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_scheduled_reminders() {

	if( ! edd_members_renewals_allowed() ) {
		return;
	}

	$edd_members_emails = new EDD_Members_Emails;

	$notices = edd_members_get_renewal_notices();

	foreach( $notices as $notice_id => $notice ) {
		
		// Get users that match current date and sent period
		$user_emails = edd_members_get_expiring_users( $notice['send_period'] );
		
		// Bail if none found
		if( ! $user_emails ) {
			continue;
		}
		
		// Loop all found users and sent emails
		foreach( $user_emails as $user_email ) {

			$edd_members_emails->send_renewal_reminder( sanitize_email( $user_email->user_email ), $notice_id );

		}

	}

}
add_action( 'edd_daily_scheduled_events', 'edd_members_scheduled_reminders' );

/**
 * Get expiring users based on notice period .
 *
 * @since  1.0.0
 * @return Array
 */
function edd_members_get_expiring_users( $period = '+1week' ) {

	if ( 'expired' == $period ) {
		$period = '+0days';
	}
	
	// Get current date +/- period time without seconds (2015-04-16 00:00)
	$start_date = strtotime( $period, strtotime( date( 'Y-m-d' ) ) );
	
	// Add +1 day (2015-04-17 00:00)
	$end_date = strtotime( '+1 day', $start_date );
	
	// Compare date values between start and and date, this means one whole day
	$args = array(
		'meta_query'      => array(
			array(
				'key'     => '_edd_members_expiration_date',
				'value'   =>  array( $start_date, $end_date ),
				'compare' => 'BETWEEN',
			),
			'fields'      => array( 'user_email' )
		)
	);

	$args  = apply_filters( 'edd_members_expiring_users_args', $args );

	$query = get_users( $args );
	if( ! $query ) {
		return false; // no expiring dates found
	}

	return $query;
}

/**
 * Check expired users.
 *
 * @since  1.0.0
 * @return array
 */
function edd_members_check_for_expired_users() {

	$args = array(
		'meta_query'      => array(
			array(
				'key'     => '_edd_members_expiration_date',
				'value'   => strtotime( date( 'Y-m-d' ) ),
				'compare' => '<'
			)
		),
		'fields'          => array( 'ID', 'user_email' )
	);

	$query = get_users( $args );
	if( ! $query ) {
		return false; // no expiring dates found
	}

	foreach( $query as $user_id ) {
		// Perhaps add new user_meta 'expired'
	}
}
add_action( 'edd_daily_scheduled_events', 'edd_members_check_for_expired_users' );