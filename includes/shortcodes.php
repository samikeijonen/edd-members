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
 * @since  1.0.0
 * @return void
 */
function edd_members_expire_date_shortcode() {
	
	if ( false != edd_members_get_expire_date() ) {
		return edd_members_get_expire_date();
	}
	
	return '';

}
add_shortcode( 'edd_members_expire_date', 'edd_members_expire_date_shortcode' );

/**
 * Add edd_members_drip shortcode for delayed content.
 *
 * @since  1.0.0
 * @param  array $atts The attributes to pass to the shortcode
 * @param  string $content The content of the shortcode
 * @return string $content The data to return for the shortcode
 */
function edd_members_drip_shortcode( $atts, $content = null ) {
	
	$atts = shortcode_atts( array(
		'delay'   => null,
		'message' => null
	), $atts );
	
	// Current date
	$current_date = current_time( 'timestamp' );
	
	// Get expire date
	$expire_date = edd_members_get_unix_expire_date();
	
	// Set delay
	$delay = '+' . absint( $atts['delay'] ) . ' ' . 'days';
	
	// Delay time
	$delay_time = strtotime( $delay, $expire_date );
	
	// Calculate when to show content
	$when_to_show_time = $current_date - $delay_time;
	
	if( $when_to_show_time >= 0 ) {
		$content = do_shortcode( $content );
	} elseif( ! is_null( $atts['message'] ) ) {
		$content = wpautop( esc_attr( $atts['message'] ) );
	} else {
		$content = '';
	}
	
	return $content;
}
add_shortcode( 'edd_members_drip', 'edd_members_drip_shortcode' );