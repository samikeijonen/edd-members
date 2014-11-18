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
	
	if ( false != edd_members_get_expire_date() ) {
		return edd_members_get_expire_date();
	}
	
	return '';

}
add_shortcode( 'edd_members_expire_date', 'edd_members_expire_date_shortcode' );