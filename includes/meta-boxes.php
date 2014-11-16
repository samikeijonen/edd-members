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
	
	// Add metabox for checking singular public post type private
	$edd_members_when_to_show_metaboxes = apply_filters( 'edd_members_when_to_show_metaboxes', array_keys( edd_members_get_public_post_types() ) );
	
	foreach ( $edd_members_when_to_show_metaboxes as $edd_members_when_to_show_metabox ) {
		add_meta_box( 'edd_members_check_as_private', esc_html__( 'Set as private', 'edd-members' ), 'edd_members_render_check_as_private_meta_box', $edd_members_when_to_show_metabox, 'side', 'core' );
	}
	
}
add_action( 'add_meta_boxes', 'edd_members_create_meta_boxes' );

/**
 * Displays the metabox for membership length.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @param  array   $metabox
 * @return void
 */
function edd_members_render_check_as_private_meta_box( $post, $metabox ) {

	wp_nonce_field( basename( __FILE__ ), 'edd-members-check-private-nonce' );
	
	// Retrieve metadata values if they already exist
	$edd_members_check_as_private = get_post_meta( $post->ID, '_edd_members_check_as_private', true );

	// Get private post types from settings
	$edd_members_private_post_type = edd_get_option( 'edd_members_private_post_type' );
	
	// Get current screen
	$screen = get_current_screen();
	
	// Do not show metabox if all post type have been set private in settings
	if ( !empty( $edd_members_private_post_type ) && in_array( $screen->post_type , array_keys( $edd_members_private_post_type ) ) ) {
		echo sprintf( __( 'All content have been marked as private in global %ssettings%s page.', 'edd-members' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) ) . '">', '</a>' );
	} else { 
	?>
		<p>
			<input type="checkbox" name="edd_members_check_as_private" id="edd_members_check_private" value="private" <?php checked( $edd_members_check_as_private, 'private' ); ?> />
			<label for="edd_members_check_private"><?php _e( 'Check this content as private.', 'edd-members' ); ?></label>
		</p>
		<?php
	}
	
}

/**
 * Membership length for not variable price option
 *
 * @since       1.0.0
 * @param       int $post_id The ID of this download
 * @return      void
 */
function edd_members_metabox_membership_length( $post_id = 0 ) {

	$edd_members_length_enabled = get_post_meta( $post_id, '_edd_members_length_enabled', true ) ? true : false;
	$edd_members_exp_unit       = esc_attr( get_post_meta( $post_id, '_edd_members_exp_unit', true ) );
	$edd_members_exp_length     = absint( get_post_meta( $post_id, '_edd_members_exp_length', true ) );
	$edd_members_display   	    = $edd_members_length_enabled ? '' : ' style="display:none;"';
	
	?>
	
	<script type="text/javascript">jQuery( document ).ready( function($) {$( "#edd_members_check_length_enabled" ).on( "click",function() {$( ".edd-members-toggled-hide" ).toggle();} )} );</script>
	
	<p>
		<input type="checkbox" name="_edd_members_length_enabled" id="edd_members_check_length_enabled" value="1" <?php echo checked( true, $edd_members_length_enabled, false ); ?> />
		<label for="edd_members_check_length_enabled"><?php _e( 'Check to enable membership length creation', 'edd-members' ); ?></label>
	</p>
	
	<div <?php echo $edd_members_display; ?> class="edd-members-toggled-hide">
		<input type="number" name="_edd_members_exp_length" class="small-text" value="<?php echo $edd_members_exp_length; ?>" />
	
		<select name="_edd_members_exp_unit" id="edd_members_exp_unit">
			<option value="days"   <?php echo selected( 'days',   $edd_members_exp_unit, false ); ?>><?php _e( 'Days', 'edd-members' );   ?></option>
			<option value="weeks"  <?php echo selected( 'weeks',  $edd_members_exp_unit, false ); ?>><?php _e( 'Weeks', 'edd-members' );  ?></option>
			<option value="months" <?php echo selected( 'months', $edd_members_exp_unit, false ); ?>><?php _e( 'Months', 'edd-members' ); ?></option>
			<option value="years"  <?php echo selected( 'years',  $edd_members_exp_unit, false ); ?>><?php _e( 'Years', 'edd-members' );  ?></option>
		</select>
		<label for="edd_members_exp_unit"><?php _e( 'How long are membership valid for?', 'edd-members' ); ?></label>
	</div>

	<?php
}
add_action( 'edd_meta_box_fields', 'edd_members_metabox_membership_length' );

/**
 * Price rows header for membership length
 *
 * @since       1.0.0
 * @return      void
 */
	
function edd_members_prices_header( $download_id ) {

	if( 'bundle' == edd_get_download_type( $download_id ) ) {
		return;
	}
	
	// Get membership length enabled for deciding when to show membership length
	$edd_members_length_enabled = get_post_meta( $download_id, '_edd_members_length_enabled', true ) ? true : false;
	$edd_members_display   	    = $edd_members_length_enabled ? '' : ' style="display:none;"';

?>
	<th <?php echo $edd_members_display; ?> class="edd-members-toggled-hide"><?php _e( 'Membership length', 'edd-members' ); ?></th>
<?php
}
add_action( 'edd_download_price_table_head', 'edd_members_prices_header', 800 );

/**
 * Membership length for variable price options
 *
 * @since       1.0.0
 * @return      void
 */
function edd_members_price_option_membership_length( $download_id, $price_id, $args ) {

	if( 'bundle' == edd_get_download_type( $download_id ) ) {
		return;
	}
	
	// Get membership length and unit for variable prices
	$edd_members_length   = edd_members_get_variable_price_length( $download_id, $price_id, 'length' );
	$edd_members_exp_unit = edd_members_get_variable_price_length( $download_id, $price_id, 'unit' );
	
	// Get membership length enabled for deciding when to show membership length option
	$edd_members_length_enabled = get_post_meta( $download_id, '_edd_members_length_enabled', true ) ? true : false;
	$edd_members_display   	    = $edd_members_length_enabled ? '' : ' style="display:none;"';

	?>
	<td <?php echo $edd_members_display; ?> class="edd-members-length edd-members-toggled-hide">
		<input type="number" min="0" step="1" name="edd_variable_prices[<?php echo $price_id; ?>][edd_members_length]" id="edd_variable_prices[<?php echo $price_id; ?>][edd_members_length]" size="4" style="width: 70px" value="<?php echo absint( $edd_members_length ); ?>" />
	
		<select name="edd_variable_prices[<?php echo $price_id; ?>][edd_members_exp_unit]" id="edd_variable_prices[<?php echo $price_id; ?>][edd_members_exp_unit]">
			<option value="days"   <?php echo selected( 'days',   esc_attr( $edd_members_exp_unit ), false ); ?>><?php _e( 'Days', 'edd-members' );   ?></option>
			<option value="weeks"  <?php echo selected( 'weeks',  esc_attr( $edd_members_exp_unit ), false ); ?>><?php _e( 'Weeks', 'edd-members' );  ?></option>
			<option value="months" <?php echo selected( 'months', esc_attr( $edd_members_exp_unit ), false ); ?>><?php _e( 'Months', 'edd-members' ); ?></option>
			<option value="years"  <?php echo selected( 'years',  esc_attr( $edd_members_exp_unit ), false ); ?>><?php _e( 'Years', 'edd-members' );  ?></option>
		</select>
	</td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_members_price_option_membership_length', 800, 3 );

/**
 * Saves the metadata for set as private meta box.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  object  $post
 * @return void
 */
function edd_members_save_set_as_private_meta_box( $post_id, $post ) {

	// Check nonce
	if ( !isset( $_POST['edd-members-check-private-nonce'] ) || !wp_verify_nonce( $_POST['edd-members-check-private-nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	
	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	$meta = array(
		'_edd_members_check_as_private' => strip_tags( $_POST['edd_members_check_as_private'] )
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
add_action( 'save_post', 'edd_members_save_set_as_private_meta_box', 10, 2 );

/**
 * Save the membership length and unit metabox when EDD saves other fields.
 *
 * @param  array $fields Existing fields to save
 * @since  1.0.0
 * @return array $fields Modified fields
 */
function edd_members_save_membership_lenght_unit_metabox( $fields ) {

	$fields[] = '_edd_members_length_enabled';
	$fields[] = '_edd_members_exp_unit';
	$fields[] = '_edd_members_exp_length';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_members_save_membership_lenght_unit_metabox' );