<?php
/**
 * Add filters
 *
 * @package     EDDMembers\Filters
 * @since       1.0.0
 */
 

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate 'the_content' filters.
 *
 * @since  1.0.0
 * @return void
 */
add_filter( 'edd_members_the_content', 'wptexturize'        );
add_filter( 'edd_members_the_content', 'convert_smilies'    );
add_filter( 'edd_members_the_content', 'convert_chars'      );
add_filter( 'edd_members_the_content', 'wpautop'            );
add_filter( 'edd_members_the_content', 'shortcode_unautop'  );
add_filter( 'edd_members_the_content', 'do_shortcode'       );
add_filter( 'edd_members_the_content', 'prepend_attachment' );
add_filter( 'edd_members_the_content', 'capital_P_dangit'   );

// Add filters for oEmbed.
global $wp_embed;
add_filter( 'edd_members_the_content', array( $wp_embed, 'autoembed' ), 8 );