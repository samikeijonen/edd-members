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
 * Load members only template for the content.
 *
 * Load templates/content-private.php file when content is private. 
 * This file can be overwitten in themes 'edd-members-templates' folder.
 *
 * @since  1.0.0
 * @param  string $content The Content template
 * @return string $content
 */
function edd_members_private_content( $content ) {
	
	// Check for private content
	$edd_members_is_private_content = edd_members_is_private_content();
	
	// Load private template if the content is private
	if ( $edd_members_is_private_content ) {
		
		ob_start();
		$content = edd_get_template_part( 'content', 'private' );
		return ob_get_clean();
		
	}
	
	return $content;
	
}
add_filter( 'the_content', 'edd_members_private_content', 99 );
add_filter( 'the_content_feed', 'edd_members_private_content', 99 );
add_filter( 'comment_text_rss', 'edd_members_private_content', 99 );
add_filter( 'bbp_get_topic_content', 'edd_members_private_content', 99 ); // Also support for bbPress topics
add_filter( 'bbp_get_reply_content', 'edd_members_private_content', 99 ); // Also support for bbPress replies
add_filter( 'mb_get_forum_content', 'edd_members_private_content', 99 );  // Also support for Message Board Plugin
add_filter( 'mb_get_topic_content', 'edd_members_private_content', 99 );
add_filter( 'mb_get_reply_content', 'edd_members_private_content', 99 ); 


/**
 * Load members only template for comments.
 *
 * Load templates/comments-private.php file when comments are private. 
 * This file can be overwitten in themes 'edd-members-templates' folder.
 *
 * @since  1.0.0
 * @param  string $template The Comments template
 * @return string $template
 */
function edd_members_private_comments( $template ) {

	// Get comments setting
	$edd_members_private_comments = edd_get_option( 'edd_members_private_comments' );
	
	// Bail if not checked
	if( ! $edd_members_private_comments ) {
		return $template;
	}
	
	// Check for private content
	$edd_members_is_private_content = edd_members_is_private_content();
	
	// Load private template if the content is private
	if ( $edd_members_is_private_content ) {

		// Look for a 'edd-members-templates/comments-private.php' template in the parent and child theme
		$has_template = locate_template( array( 'edd_templates/comments-private.php' ) );

		// If the template was found, use it. Otherwise, load 'templates/comments-private.php' template
		$template = ( !empty( $has_template ) ? $has_template : EDD_MEMBERS_DIR . 'templates/comments-private.php' );

		// Allow developers to overwrite the comments template
		$template = apply_filters( 'edd_members_comments_template', $template );
		
	}

	// Return the comments template filename
	return $template;
	
}
add_filter( 'comments_template', 'edd_members_private_comments', 4 );
	
/**
 * Check for private content.
 *
 * User with cap 'edd_members_show_all_content' can always see content.
 * Admins have this cap by default.
 *
 * @since  1.0.0
 * @param  int $user_id The ID of the user to check
 * @param  int $post_id The ID of the post to check
 * @return boolean
 */
function edd_members_is_private_content( $user_id = false, $post_id = '' ) {

	// If no user is given, use the current user
	if( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// If no post ID is given, assume we're in The Loop and get the current post's ID
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	
	// Get the post object.
	$post = get_post( $post_id );
	
	// Get post meta for singular posts that have been checked private
	$edd_members_check_as_private = get_post_meta( $post_id, '_edd_members_check_as_private', true );
	
	// Get private post types from settings
	$edd_members_private_post_type = edd_get_option( 'edd_members_private_post_type' );
	
	// Get feed setting
	$edd_members_private_feed = edd_get_option( 'edd_members_private_feed' );
	
	// By default content is not private
	$edd_members_check = false;
	
	// The post author can always see content, or users who can edit the post or have cap 'edd_members_show_all_content'
	if( $post_id && ( $post->post_author == $user_id || current_user_can( 'edit_post', $post_id ) || current_user_can( 'edd_members_show_all_content' ) ) ) {
		$edd_members_check = false;
	}
	
	// Check for feed
	elseif ( !edd_members_is_membership_valid() && is_feed() && $edd_members_private_feed ) {
		$edd_members_check = true;
	}
	
	// Check for singular post that have been checked private. @TODO: Should we make is_singular check in here also?
	elseif ( !edd_members_is_membership_valid() && !empty( $edd_members_check_as_private ) && 'private' == $edd_members_check_as_private && is_main_query() ) {
		$edd_members_check = true;
	}
	
	// Check private post types from settings
	elseif ( !edd_members_is_membership_valid() && !empty( $edd_members_private_post_type ) && is_singular( array_keys( $edd_members_private_post_type ) ) && is_main_query() ) {
		$edd_members_check = true;
	}
	
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
	$current_date = current_time( 'timestamp' );

	foreach ( $downloads as $d_id ) {
		
		// Skip if members length is not enabled
		if ( ! get_post_meta( $d_id, '_edd_members_length_enabled', true ) ) {
			continue;
		}
		
		// Get price id
		$price_id = isset( $cart_item['item_number']['options']['price_id'] ) ? (int) $cart_item['item_number']['options']['price_id'] : false;
		
		// Get membership lengths in arrays because user might purchase more than one item
		$edd_members_membership_lengths = array();
		$edd_members_membership_lengths[] = strtotime( edd_members_get_membership_length( $price_id, $payment_id, $d_id ), $current_date );
		
	}
	
	// Pick the max value from membership lengths
	$edd_members_membership_length = max( $edd_members_membership_lengths );
	
	// Get current expire date
	$expire_date = get_user_meta( $user_id, '_edd_members_expiration_date', true );
	
	/**
	 * If expire_date is not set (this means new user), add membership length = current_date + support_time.
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
	
	// Set defaults
	if( empty( $edd_members_exp_unit ) ) {
		$edd_members_exp_unit = apply_filters( 'edd_members_exp_unit_default', 'days' );
	}

	if( empty( $edd_members_exp_length ) ) {
		$edd_members_exp_length = apply_filters( 'edd_members_exp_length_default', '0' );
	}
	
	// Set expiration
	$expiration = '+' . $edd_members_exp_length . ' ' . $edd_members_exp_unit;

	return apply_filters( 'edd_members_membership_length', $expiration, $payment_id, $download_id, $price_id );
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
 * Get user expire date in Unix time format.
 *
 * @since 1.0.0
 * @return void
 */
function edd_members_get_unix_expire_date( $user_id = 0 ) {
	
	// Get current user id if it's not set from function call
	if ( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}
		
	// Get expire date
	$expire_date = get_the_author_meta( '_edd_members_expiration_date', $user_id );
		
	return $expire_date;

}

/**
 * Get user expire date in readable format.
 *
 * @since 1.0.0
 * @return void
 */
function edd_members_get_expire_date( $user_id = 0, $show_time = true ) {
		
	// Get expire date
	$expire_date = edd_members_get_unix_expire_date( $user_id );
		
	// Return expire_date if there is one
	if ( !empty( $expire_date ) ) {
		$edd_members_expire_date  = date_i18n( get_option( 'date_format' ), $expire_date );
		if( $show_time ) {
			$edd_members_expire_date .= ' ' . _x( 'at', 'word between date and time', 'edd-members' ) . ' ';
			$edd_members_expire_date .= date_i18n( get_option( 'time_format' ), $expire_date );
		}
	} else {
		$edd_members_expire_date = __( 'Unknown', 'edd-members' );
	}
		
	return $edd_members_expire_date;

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
	$current_date = current_time( 'timestamp' );

	// Get expire date
	$expire_date = get_user_meta( $user_id, '_edd_members_expiration_date', true );
	
	// Check if user expire date >= current date
	if ( !empty( $expire_date ) && $expire_date >= $current_date ) {
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
 * Get user info from user email.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_get_user_info_by_email( $user_email = null, $type = null ) {

	if( is_null( $type ) || empty( $type ) ) {
		return false;
	}

	// User info by email
	$user = get_user_by( 'email', $user_email );
		
	// User info
	$user_info = $user->$type;
	
	return $user_info;
}

/**
 * Returns a list of all public post types.
 *
 * @since  1.0.0
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

/**
 * Adds our templates dir to the EDD template stack.
 *
 * @since  1.0.0
 * @return array $paths Directories of EDD template stack
 */
function edd_members_add_template_stack( $paths ) {

	$paths[ 66 ] = EDD_MEMBERS_DIR . 'templates/';

	return $paths;

}
add_filter( 'edd_template_paths', 'edd_members_add_template_stack' );