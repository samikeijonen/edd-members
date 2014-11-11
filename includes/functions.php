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
	 
	$edd_members_private_post_type = edd_get_option( 'edd_members_private_post_type' );
	
	if ( !empty( $edd_members_private_post_type ) && is_singular( array_keys( $edd_members_private_post_type ) ) && is_main_query() ) {
		
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