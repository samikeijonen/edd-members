<?php
/**
 * Add shortcodes
 *
 * @package     EDDMembers\Shortcodes
 * @since       1.0.0
 */
 

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add shortcode for user expire date.
 *
 * @since 1.0.0
 * @return void
 */
function edd_members_expire_date_shortcode() {
	
	if( is_user_logged_in() ) {
	
		// Get current user id
		$user_id = get_current_user_id();
		
		// Get expire date
		$expire_date = get_the_author_meta( '_edd_members_expiration_date', $user_id );
		
		// Return expire_date if there is one
		if ( !empty( $expire_date ) ) {
			$edd_members_expire_date = date_i18n( get_option( 'date_format' ), $expire_date );
		} else {
			$edd_members_expire_date = __( 'Unknown', 'edd-members' );
		}
		
		return $edd_members_expire_date;
		
	}

}
add_shortcode( 'edd_members_expire_date', 'edd_members_expire_date_shortcode' );