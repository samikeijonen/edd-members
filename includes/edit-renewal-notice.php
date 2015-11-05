<?php
/**
 * Edit Renewal Notice
 *
 * Edit renewal functions are from Pippin Williamson and his Software License Plugin.
 *
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDMembers\Renewals
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

if ( ! isset( $_GET['notice'] ) || ! is_numeric( $_GET['notice'] ) ) {
	//wp_die( __( 'Something went wrong.', 'edd-members' ), __( 'Error', 'edd-members' ) );
}

$notice_id = absint( $_GET['notice'] );
$notice    = edd_members_get_renewal_notice( $notice_id );
?>
<h2><?php esc_html_e( 'Edit Renewal Notice', 'edd-members' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ); ?>" class="add-new-h2"><?php _e( 'Go Back', 'edd-members' ); ?></a></h2>
<form id="edd-edit-renewal-notice" action="" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-subject"><?php esc_html_e( 'Email Subject', 'edd-members' ); ?></label>
				</th>
				<td>
					<input name="subject" id="edd-notice-subject" type="text" value="<?php echo esc_attr( stripslashes( $notice['subject'] ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php esc_html_e( 'The subject line of the renewal notice email', 'edd-members' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-period"><?php esc_html_e( 'Email Subject', 'edd-members' ); ?></label>
				</th>
				<td>
					<select name="period" id="edd-notice-period">
						<?php foreach( edd_members_get_renewal_notice_periods() as $period => $label ) : ?>
							<option value="<?php echo esc_attr( $period ); ?>"<?php selected( $period, $notice['send_period'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'When should this email be sent?', 'edd-members' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-message"><?php esc_html_e( 'Email Message', 'edd-members' ); ?></label>
				</th>
				<td>
					<?php wp_editor( wpautop( wp_kses_post( wptexturize( $notice['message'] ) ) ), 'message', array( 'textarea_name' => 'message' ) ); ?>
					<p class="description"><?php esc_html_e( 'The email message to be sent with the renewal notice. The following template tags can be used in the message:', 'edd-members' ); ?></p>
					<ul>
						<li>{name} <?php esc_html_e( 'The customer\'s name', 'edd-members' ); ?></li>
						<li>{edd_members_expiration} <?php esc_html_e( 'User expiration date', 'edd-members' ); ?></li>
					</ul>
				</td>
			</tr>
			
		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="edd-action" value="members_edit_renewal_notice"/>
		<input type="hidden" name="notice-id" value="<?php echo esc_attr( $notice_id ); ?>"/>
		<input type="hidden" name="edd-members-renewal-notice-nonce" value="<?php echo wp_create_nonce( 'edd_members_renewal_nonce' ); ?>"/>
		<input type="submit" value="<?php esc_html_e( 'Update Renewal Notice', 'edd-members' ); ?>" class="button-primary"/>
	</p>
</form>
