<?php
/**
 * Helper Functions
 *
 * @package     EDDMembers\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load members only template.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_private_template( $content ) {

	/* If is singular post type what have been selected in the settings, load templates/content-private.php file. 
	 * This file can be overwitten in themes edd-members-templates folder.
	 */
	
	// Check for private content
	$edd_members_is_private_content = edd_members_is_private_content();
	
	// Load private template if the content is private
	if ( $edd_members_is_private_content ) {
		
		$templates = new EDD_Members_Template_Loader;
		
		ob_start();
		$content = $templates->get_template_part( 'content', 'private' );
		return ob_get_clean();
		
	}
	
	return $content;
	
}
add_filter( 'the_content', 'edd_members_private_template', 99 );
add_filter( 'bbp_get_topic_content', 'edd_members_private_template', 99 ); // Also support for bbPress topics
add_filter( 'bbp_get_reply_content', 'edd_members_private_template', 99 ); // Also support for bbPress replies

/**
 * Check for private content.
 *
 * @since  1.0.0
 * @return boolean
 */
function edd_members_is_private_content() {

	/* Note! User with cap 'edd_members_show_all_content' can always see content.
	 * Admins have this cap by default.
	 */
	
	// Get post meta for singular posts that have been checked private
	$edd_members_check_as_private = get_post_meta( get_the_ID(), '_edd_members_check_as_private', true );
	
	// Get private post types from settings
	$edd_members_private_post_type = edd_get_option( 'edd_members_private_post_type' );
	
	// Check for singular post that have been checked private. @TODO: Should we make is_singular check in here also?
	$edd_members_check_singular = false;
	if ( !edd_members_is_membership_valid() && !empty( $edd_members_check_as_private ) && 'private' == $edd_members_check_as_private && !current_user_can( 'edd_members_show_all_content' ) && is_main_query() ) {
		$edd_members_check_singular = true;
	}
	
	// Check private post types from settings
	$edd_members_check_non_singular = false;
	if ( !edd_members_is_membership_valid() && !current_user_can( 'edd_members_show_all_content' ) && !empty( $edd_members_private_post_type ) && is_singular( array_keys( $edd_members_private_post_type ) ) && is_main_query() ) {
		$edd_members_check_non_singular = true;
	}
	
	// Check if some of the conditions are true
	$edd_members_check = $edd_members_check_singular || $edd_members_check_non_singular;
	
	return apply_filters( 'edd_members_is_private_content', $edd_members_check );

}

/**
 * Add or update expire date to custom user meta when purchasing something on the site.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_add_expire_date( $download_id = 0, $payment_id = 0, $type = 'default', $cart_item = array() ) {

	if( !is_user_logged_in() ) {
		return;
	}
	
	$user_id = get_current_user_id();
	
	if( 'bundle' == $type ) {
		$downloads = edd_get_bundled_products( $download_id );
	} else {
		$downloads = array();
		$downloads[] = $download_id;
	}

	if( ! is_array( $downloads ) ) {
		return;
	}
	
	// Current date
	$current_date = date( 'Y-m-d' );

	foreach ( $downloads as $d_id ) {
		
		// Skip if members length is not enabled
		if ( ! get_post_meta( $d_id, '_edd_members_length_enabled', true ) ) {
			continue;
		}
		
		// Get price id
		$price_id = isset( $cart_item['item_number']['options']['price_id'] ) ? (int) $cart_item['item_number']['options']['price_id'] : false;
		
		// Get membership lengths in arrays
		$edd_members_membership_lengths = array();
		$edd_members_membership_lengths[] = strtotime( edd_members_get_membership_length( $price_id, $payment_id, $d_id ), strtotime( $current_date ) );
		
	}
	
	// Pick the max value from membership lengths
	$edd_members_membership_length = max( $edd_members_membership_lengths );
	
	// Get current expire date
	$expire_date = get_user_meta( $user_id, '_edd_members_expiration_date', true );
	
	/* If expire_date is not set (this means new user), add membership length = current_date + support_time.
	 * Else there is expire date already.
	 */
	if ( !isset( $expire_date ) || empty( $expire_date ) ) {
		$expire_date = $edd_members_membership_length;
	}
	else {
		
		// if expire_date < current_date, add current_date + support_time
		if ( $expire_date < strtotime( $current_date ) ) {
			$expire_date = $edd_members_membership_length;
		}
		else {
			
			// If future_date < expire_date, don't add anything
			if ( $edd_members_membership_length < $expire_date ) {
				$expire_date = $expire_date;
			}
			else {
				$expire_date = $edd_members_membership_length;
			}
			
		}
		
	}
	
	// Set membership expiration date
	edd_members_set_membership_expiration( $user_id, $expire_date );

}
add_action( 'edd_complete_download_purchase', 'edd_members_add_expire_date', 10, 4 );

/**
 * Get membership length from download.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_get_membership_length( $price_id = 0, $payment_id = 0, $download_id = 0 ) {
	
	// Get expire unit and length
	if ( edd_has_variable_prices( $download_id ) ) {
		$edd_members_exp_length = edd_members_get_variable_price_length( $download_id, $price_id, 'length' );
		$edd_members_exp_unit   = edd_members_get_variable_price_length( $download_id, $price_id, 'unit' );
		
	} else {
		$edd_members_exp_length = absint( get_post_meta( $download_id, '_edd_members_exp_length', true ) );
		$edd_members_exp_unit   = esc_attr( get_post_meta( $download_id, '_edd_members_exp_unit', true ) );
	}
	
	// Set default
	if( empty( $edd_members_exp_unit ) ) {
		$edd_members_exp_unit = 'years';
	}

	if( empty( $edd_members_exp_length ) ) {
		$edd_members_exp_length = '1';
	}
	
	// Set expiration
	$expiration = '+' . $edd_members_exp_length . ' ' . $edd_members_exp_unit;

	$edd_members_membership_length = apply_filters( 'edd_members_membership_length', $expiration, $payment_id, $download_id, $license_id );

	return $edd_members_membership_length;
}

/**
 * Set membership length to user meta.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_set_membership_expiration( $user_id, $expiration ) {

	// $expiration should be a valid time stamp
	do_action( 'edd_members_pre_set_expiration', $user_id, $expiration );
	update_user_meta( $user_id, '_edd_members_expiration_date', $expiration );
	do_action( 'edd_members_post_set_expiration', $user_id, $expiration );

}

/**
 * Check if current user membership have expired or not.
 *
 * @since  1.0.0
 * @return boolean
 */
function edd_members_is_membership_valid() {

	$check_membership = false;

	if( !is_user_logged_in() ) {
		return $check_membership;
	}
	
	// Get current user id
	$user_id = get_current_user_id();
	
	// Current date
	$current_date = date( 'Y-m-d' );

	// Get expire date
	$expire_date = get_user_meta( $user_id, '_edd_members_expiration_date', true );
	
	// Check if user expire date > current date
	if ( !empty( $expire_date ) && $expire_date > strtotime( $current_date ) ) {
		$check_membership = true;
	}
	
	return apply_filters( 'edd_members_is_membership_valid', $check_membership );

}

/**
 * Get membership length and unit for variable prices.
 *
 * @since  1.0.0
 * @return string
 */
function edd_members_get_variable_price_length( $download_id = 0, $price_id = null, $type = null ) {

	$prices = edd_get_variable_prices( $download_id );

	if ( isset( $prices[ $price_id ][ 'edd_members_length' ] ) && 'length' == $type ) {
		return absint( $prices[ $price_id ][ 'edd_members_length' ] );
	}
	elseif ( isset( $prices[ $price_id ][ 'edd_members_exp_unit' ] ) && 'unit' == $type ) {
		return esc_attr( $prices[ $price_id ][ 'edd_members_exp_unit' ] );
	}

	return false;
}

/**
 * Returns a list of all public post types.
 *
 * @since 1.0.0
 * @return array $edd_members_public_post_types_array All the public post types
 */
function edd_members_get_public_post_types() {

	// Get public post types in array
	$edd_members_public_post_types = array_values( get_post_types( array( 'public' => true ) ) );
	
	$edd_members_public_post_types_array = array();
	
	foreach ( $edd_members_public_post_types as $edd_members_public_post_type ) {
		$post_type_object = get_post_type_object( $edd_members_public_post_type );
		$slug = $post_type_object->name;
		$label = $post_type_object->labels->name;
		$edd_members_public_post_types_array[$slug] = $label;
	}
	
	return apply_filters( 'edd_members_public_post_types', $edd_members_public_post_types_array );
}