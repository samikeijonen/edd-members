<?php
/**
 * Add metaboxes
 *
 * @package     EDDMembers\Metaboxes
 * @since       1.0.0
 */
 

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom meta box for 'download' metabox.
 *
 * @since 1.0.0
 * @return void
 */

 function edd_members_create_meta_boxes() {
 
	//Add metabox for membership length
	if( 'bundle' != edd_get_download_type( get_the_ID() ) ) {
		add_meta_box( 'edd_members_membership_exp_length', esc_html__( 'Membership length', 'edd-members' ), 'edd_members_render_exp_length_meta_box', 'download', 'side', 'core' );
	}
	
}
add_action( 'add_meta_boxes', 'edd_members_create_meta_boxes' );

/**
 * Displays the extra meta box in pages.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @param  array   $metabox
 * @return void
 */
function edd_members_render_exp_length_meta_box( $post, $metabox ) {

	wp_nonce_field( basename( __FILE__ ), 'edd-members-metabox-nonce' ); 
	
	$edd_members_exp_unit   = esc_attr( get_post_meta( $post->ID, '_edd_members_exp_unit', true ) );
	$edd_members_exp_length = absint( get_post_meta( $post->ID, '_edd_members_exp_length', true ) );
	
	?>
	
	<input type="number" name="edd_members_exp_length" class="small-text" value="<?php echo $edd_members_exp_length; ?>"/>&nbsp;';
	
	<select name="edd_members_exp_unit" id="edd_members_exp_unit">
		<option value="days"   <?php echo selected( 'days', $edd_members_exp_unit, false );   ?>><?php _e( 'Days', 'edd-members' );   ?></option>
		<option value="weeks"  <?php echo selected( 'weeks', $edd_members_exp_unit, false );  ?>><?php _e( 'Weeks', 'edd-members' );  ?></option>
		<option value="months" <?php echo selected( 'months', $edd_members_exp_unit, false ); ?>><?php _e( 'Months', 'edd-members' ); ?></option>
		<option value="years"  <?php echo selected( 'years', $edd_members_exp_unit, false );  ?>><?php _e( 'Years', 'edd-members' );  ?></option>
	</select>&nbsp;
	<label for="edd_members_exp_unit"><?php _e( 'How long are membership valid for?', 'edd-members' ); ?></label>
	
	<?php
}

/**
 * Saves the metadata for meta box.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  object  $post
 * @return void
 */
function edd_members_save_meta_boxes( $post_id, $post ) {

	// Check nonce
	if ( !isset( $_POST['edd-members-metabox-nonce'] ) || !wp_verify_nonce( $_POST['edd-members-metabox-nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	
	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}
	
	// Check for 'download' post type
	if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
		return;
	}

	$meta = array(
		//'_metsahvp_featured_location' => strip_tags( $_POST['metsahvp_featured_location'] ),
		'_edd_members_exp_length' => absint( $_POST['edd_members_exp_length'] ),
		'_edd_members_exp_unit'    => esc_attr( $_POST['edd_members_exp_unit'] )
	);

        foreach ( $meta as $meta_key => $new_meta_value ) {
			
			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If there is no new meta value but an old value exists, delete it. */
			if ( current_user_can( 'edit_post', $post_id ) && '' == $new_meta_value && $meta_value ) {
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}	

			/* If a new meta value was added and there was no previous value, add it. */
			elseif ( current_user_can( 'edit_post', $post_id ) && $new_meta_value && '' == $meta_value ) {
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
			}

			/* If the new meta value does not match the old value, update it. */
			elseif ( current_user_can( 'edit_post', $post_id ) && $new_meta_value && $new_meta_value != $meta_value ) {
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			}
				
        }
}
add_action( 'save_post', 'edd_members_save_meta_boxes', 10, 2 );
