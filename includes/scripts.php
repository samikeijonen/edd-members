<?php
/**
 * Scripts
 *
 * @package     EDDMembers\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      array $edd_settings_page The slug for the EDD settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function edd_members_admin_scripts( $hook ) {

	global $edd_settings_page, $post_type;

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	
	// Enqueue date picker in user profile page
	if( 'user-edit.php' == $hook || 'profile.php' == $hook ) {
	
		// Add datepicker
		wp_enqueue_script( 'edd-members-datepicker-settings', EDD_MEMBERS_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery-ui-datepicker' ) );
		
		// Localize dateformat
		wp_localize_script( 'edd-members-datepicker-settings', 'datepicker_settings_vars', array(
			'dateformat' => apply_filters( 'edd_members_datepicker_date', 'mm/dd/yy' )
			)
		);
		
		// Add styles from EDD plugin
		$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
		wp_enqueue_style( 'jquery-ui-css', trailingslashit( EDD_PLUGIN_URL ) . 'assets/css/jquery-ui-' . $ui_style . '.css' );
	}
	
}
add_action( 'admin_enqueue_scripts', 'edd_members_admin_scripts', 100 );