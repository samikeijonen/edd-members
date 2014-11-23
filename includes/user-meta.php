<?php
/**
 * Add user meta related functions
 *
 * @package     EDDMembers\Usermeta
 * @since       1.0.0
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add new user column: Expire Date.
 *
 * @since  1.0.0
 * @return array $columns
 */
function edd_members_expire_date_column( $columns ) {
	$columns['expire_date'] = __( 'Expire Date', 'edd-members' );
	return $columns;
}
add_filter( 'manage_users_columns', 'edd_members_expire_date_column' );

/**
 * Adds expire Date to column.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_expire_date_data( $value, $column_name, $user_id ) {
	
	if( 'expire_date' == $column_name ) {
		
		// Get expire date
		$expire_date = edd_members_get_expire_date( $user_id );
		
		return $expire_date;
	
	}
	
}
add_action( 'manage_users_custom_column', 'edd_members_expire_date_data', 10, 3 );

/**
 * Adds expire date in user profile.
 *
 * Only user with 'edd_members_edit_user' can edit expire date.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_expire_date_profile_field( $user ) { ?>

	<h3><?php _e( 'Membership expire date', 'edd-members' ); ?></h3>

	<table class="form-table">

		<tr>
			<th><label for="edd_members_exprire_date"><?php _e( 'Expire date', 'edd-members' ); ?></label></th>

			<td>
				<?php if ( current_user_can( 'edd_members_edit_user' ) || current_user_can( 'manage_shop_settings' ) ) { // Only users with 'edd_members_edit_user' or 'manage_shop_settings' cap can edit expire date ?>
					<input class="edd_members_datepicker" type="text" name="_edd_members_expiration_date" id="edd_members_exprire_date" value="<?php echo edd_members_get_expire_date( $user->ID ); ?>" class="regular-text" />
					<span class="description"><?php _e( 'Set expire date for membership.', 'edd-members' ); ?></span>
				<?php } else {
					echo edd_members_get_expire_date( $user->ID );
				} ?>
			</td>
		</tr>

	</table>
<?php }
add_action( 'show_user_profile', 'edd_members_expire_date_profile_field' );
add_action( 'edit_user_profile', 'edd_members_expire_date_profile_field' );

/**
 * Save expire date in user profile.
 *
 * Only user with 'edd_members_edit_user' can save expire date.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_save_expire_date_profile_field( $user_id ) {
	
	// Bail if current user doesn't have 'edd_members_edit_user' cap to update expire date
	if ( !current_user_can( 'edd_members_edit_user', $user_id ) ) {
		return false;
	}

	// Update user meta
	update_usermeta( $user_id, '_edd_members_expiration_date', strtotime( esc_attr( $_POST['_edd_members_expiration_date'] ) ) );
	
}
add_action( 'personal_options_update', 'edd_members_save_expire_date_profile_field' );
add_action( 'edit_user_profile_update', 'edd_members_save_expire_date_profile_field' );