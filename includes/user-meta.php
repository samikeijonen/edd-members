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
	$columns['expire_date']   = esc_html__( 'Expire Date', 'edd-members' );
	$columns['expire_status'] = esc_html__( 'Status', 'edd-members' );
	return $columns;
}
add_filter( 'manage_users_columns', 'edd_members_expire_date_column' );

/**
 * Adds Expire date and Status to column.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_add_custom_columns( $value, $column_name, $user_id ) {
	
	if( 'expire_date' == $column_name ) {
		
		// Get expire date
		$expire_date = edd_members_get_expire_date( $user_id );
		
		$value = $expire_date;
	
	}
	
	if( 'expire_status' == $column_name ) {
		
		// Get expire date
		$expire_date = get_user_option( '_edd_members_expiration_date', $user_id );
		
		// Set membership status
		if ( empty( $expire_date ) ) {
			$expire_status = esc_html__( 'Unknown', 'edd-members' );
		} elseif( edd_members_is_membership_valid( $user_id ) ) {
			$expire_status = esc_html__( 'Active', 'edd-members' );
		} else {
			$expire_status = esc_html__( 'Expired', 'edd-members' );
		}
		
		$value = $expire_status;
	
	}
	
	return $value;
	
}
add_action( 'manage_users_custom_column', 'edd_members_add_custom_columns', 10, 3 );

/**
 * Adds sortable columns.
 *
 * @since  1.0.3
 * @return void
 */
function edd_members_sortable_columns( $columns ) {
	
	$columns['expire_date'] = 'expire_date';

	return $columns;
	
}
add_filter( 'manage_users_sortable_columns', 'edd_members_sortable_columns' );

/**
 * Sort by expire date. Meta key is called '_edd_members_expiration_date'.
 *
 * @since  1.0.3
 * @return void
 */
function edd_members_sort_by_expiration_date( $query ) {
	
	// Bail if we are not in the admin.
	if( ! is_admin() ) {
		return;
	}
	
	// Set meta_key. In multisite it has blog prefix.
	if ( is_multisite() ) {
		global $wpdb;
		$expire_meta_key = $wpdb->get_blog_prefix() . '_edd_members_expiration_date';
	} else {
		$expire_meta_key = '_edd_members_expiration_date';
	}
	
	// Sort by expire date.
	if ( 'expire_date' == $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', $expire_meta_key );
	}

}
add_action( 'pre_get_users', 'edd_members_sort_by_expiration_date' );

/**
 * Adds expire date in user profile.
 *
 * Only user with 'edd_members_edit_user' can edit expire date.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_expire_date_profile_field( $user ) { 
	
	// Get expire date.
	$expire_date = edd_members_get_unix_expire_date( $user->ID );
	
	?>

	<h3><?php esc_html_e( 'Membership expire date', 'edd-members' ); ?></h3>

	<table class="form-table">

		<tr>
			<th><label for="edd_members_exprire_date"><?php esc_html_e( 'Expire date', 'edd-members' ); ?></label></th>

			<td>
				<?php if ( current_user_can( 'edd_members_edit_user' ) || current_user_can( 'manage_shop_settings' ) ) { // Only users with 'edd_members_edit_user' or 'manage_shop_settings' cap can edit expire date ?>
					<input type="text" name="edd_members_expiration_date" id="edd_members_exprire_date" value="<?php if ( ! empty( $expire_date ) ) esc_attr_e( date_i18n( get_option( 'date_format' ), $expire_date ) ); ?>" class="edd_members_datepicker medium-text edd-members-time-date" />
					<?php echo esc_html_x( 'at', 'word between date and time', 'edd-members' ); ?>
					<input type="number" step="1" max="24" name="edd_members_expiration_time_hour" value="<?php if ( ! empty( $expire_date ) ) esc_attr_e( date_i18n( 'H', $expire_date ) ); ?>" class="small-text edd-members-time-hour "/>&nbsp;:
					<input type="number" step="1" max="59" name="edd_members_expiration_time_min" value="<?php if ( ! empty( $expire_date ) ) esc_attr_e( date( 'i', $expire_date ) ); ?>" class="small-text edd-members-time-min "/>
					<p class="description"><?php _e( 'Set expire date and time for membership.', 'edd-members' ); ?></p>
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
 * Only user with 'edd_members_edit_user' or 'manage_shop_settings' can save expire date.
 *
 * @since  1.0.0
 * @return void
 */
function edd_members_save_expire_date_profile_field( $user_id ) {
	
	// Bail if current user doesn't have 'edd_members_edit_user' cap to update expire date
	if ( !current_user_can( 'edd_members_edit_user', $user_id ) || !current_user_can( 'manage_shop_settings', $user_id ) ) {
		return false;
	}
	
	// Get date and time values
	$date   = isset( $_POST['edd_members_expiration_date'] )      ? sanitize_text_field( $_POST['edd_members_expiration_date'] )      : null;
	$hour   = isset( $_POST['edd_members_expiration_time_hour'] ) ? sanitize_text_field( $_POST['edd_members_expiration_time_hour'] ) : null;
	$minute = isset( $_POST['edd_members_expiration_time_min'] )  ? sanitize_text_field( $_POST['edd_members_expiration_time_min'] )  : null;
	
	// Value saved in unix format
	$date_unix_save = strtotime( $date . ' ' . $hour . ':' . $minute . ':00' );

	// This will add blog prefix in multisite.
	if ( is_multisite() ) {
		update_user_option( $user_id, '_edd_members_expiration_date', $date_unix_save );
	}
	
	// Leave old method for non multisite, for filtering users by expire date and for email reminders.
	update_user_meta( $user_id, '_edd_members_expiration_date', $date_unix_save );
	
}
add_action( 'personal_options_update', 'edd_members_save_expire_date_profile_field' );
add_action( 'edit_user_profile_update', 'edd_members_save_expire_date_profile_field' );