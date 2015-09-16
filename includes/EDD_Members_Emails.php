<?php
/**
 * Send email class
 *
 * This is modified version of email class from Pippin Williamson and his Software License Plugin.
 *
 * @author      Sami Keijonen
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDMembers\Renewals
 * @since       1.0.0
 */

class EDD_Members_Emails {

	function __construct() {

		add_action( 'edd_add_email_tags', array( $this, 'add_email_tag' ), 100 );

	}

	public function add_email_tag() {

		edd_add_email_tag( 'edd_members_expiration', esc_html__( 'Show user expire date', 'edd-members' ), array( $this, 'edd_members_expiration_render' ) );

	}

	public function edd_members_expiration_render( $payment_id = 0 ) {
		
		// Get user id
		$user_id = edd_get_payment_user_id( $payment_id );
		
		// Get expire date
		$exprire_date = edd_members_get_expire_date( $user_id  );

		return $exprire_date;

	}

	public function send_renewal_reminder( $user_email = null, $notice_id = 0 ) {
		
		// Bail if there is no email
		if( empty( $user_email ) ) {
			return;
		}

		$send = true;

		$send = apply_filters( 'edd_members_send_renewal_reminder', $send, $user_email, $notice_id );

		if( ! $send ) {
			return;
		}

		// Email to
		$email_to = $user_email;

		$notice   = edd_members_get_renewal_notice( $notice_id );
		$message  = ! empty( $notice['message'] ) ? $notice['message'] : esc_html__( "Hello {name},\n\nYour membership is about to expire.\n\nYour membership expires on: {edd_members_expiration}.", "edd-members" );
		$message  = $this->filter_reminder_template_tags( $message, $user_email );

		$subject  = ! empty( $notice['subject'] ) ? $notice['subject'] : esc_html__( 'Your membership is about to expire', 'edd-members' );
		$subject  = $this->filter_reminder_template_tags( $subject, $user_email );

		if( class_exists( 'EDD_Emails' ) ) {
			
			EDD()->emails->__set( 'heading', esc_html__( 'Membership Reminder', 'edd-members' ) );
			EDD()->emails->send( $email_to, $subject, $message );

		} else {

			$from_name  = get_bloginfo( 'name' );
			$from_email = get_bloginfo( 'admin_email' );
			$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
			$headers   .= "Reply-To: ". $from_email . "\r\n";

			wp_mail( $email_to, $subject, $message, $headers );

		}
		
		// User id
		$user_id = edd_members_get_user_info_by_email( $user_email, 'ID' );
		
		// Save in user meta just in case if we need to prevent sending emails more than once
		update_user_meta( $user_id, sanitize_key( '_edd_members_renewal_sent_' . $notice['send_period'] ), time() );

	}

	public function filter_reminder_template_tags( $text = '', $user_email = null ) {
		
		// User display name
		$user_display_name = edd_members_get_user_info_by_email( $user_email, 'display_name' );
		
		// Use id
		$user_id = edd_members_get_user_info_by_email( $user_email, 'ID' );
		
		// Get expire date
		$exprire_date = edd_members_get_expire_date( $user_id );

		// Retrieve the customer name
		if ( $user_display_name ) {
			$customer_name = $user_display_name;
		} else {
			$customer_name = $user_email;
		}
		
		$text = str_replace( '{name}', $customer_name, $text );
		$text = str_replace( '{edd_members_expiration}', $exprire_date, $text );

		return $text;
	}


}
$edd_members_emails = new EDD_Members_Emails;
