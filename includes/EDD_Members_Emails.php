<?php

class EDD_Members_Emails {

	function __construct() {

		add_action( 'edd_add_email_tags', array( $this, 'add_email_tag' ), 100 );

	}

	public function add_email_tag() {

		edd_add_email_tag( 'edd_members_expiration', __( 'Show user expire date', 'edd-members' ), array( $this, 'edd_members_expiration_render' ) );

	}

	public function edd_members_expiration_render( $payment_id = 0 ) {
		
		// Get user id
		$user_id = edd_get_payment_user_id( $payment_id );
		
		// Get expire date
		$exprire_date = edd_members_get_expire_date( $user_id  );

		return $exprire_date;

	}

	public function send_renewal_reminder( $user_email = null, $notice_id = 0 ) {

		global $edd_options;

		if( empty( $user_email ) ) {
			return;
		}

		$send = true;

		$send = apply_filters( 'edd_members_send_renewal_reminder', $send, $user_email, $notice_id );

		if( ! $send ) {
			return;
		}

		// Email to
		$email_to   = $user_email;

		$notice     = edd_members_get_renewal_notice( $notice_id );
		$message    = ! empty( $notice['message'] ) ? $notice['message'] : __( "Hello {name},\n\nYour membership is about to expire.\n\nYour membership expires on: {edd_members_expiration}.\n\nRenew now: {renewal_link}.", "edd-members" );
		$message    = $this->filter_reminder_template_tags( $message, $user_email );

		$subject    = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Your membership is about to expire', 'edd-members' );
		$subject    = $this->filter_reminder_template_tags( $subject, $user_email );

		if( class_exists( 'EDD_Emails' ) ) {

			EDD()->emails->send( $email_to, $subject, $message );

		} else {

			$from_name  = get_bloginfo( 'name' );
			$from_email = get_bloginfo( 'admin_email' );
			$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
			$headers   .= "Reply-To: ". $from_email . "\r\n";

			wp_mail( $email_to, $subject, $message, $headers );

		}
		
		// User id
		$user_id = edd_members_get_user_info_by_email( $user_email, 'ID' );

		add_user_meta( $user_id, sanitize_key( '_edd_members_renewal_sent_' . $notice['send_period'] ), time() ); // Prevent renewal notices from being sent more than once

	}

	public function filter_reminder_template_tags( $text = '', $user_email = null ) {
		
		// User display name
		$user_display_name = edd_members_get_user_info_by_email( $user_email, 'display_name' );
		
		// Get expire date
		$exprire_date = edd_members_get_expire_date( $user_id  );

		// Retrieve the customer name
		if ( $user_display_name ) {
			$customer_name = $user_display_name;
		} else {
			$customer_name = $user_email;
		}
		
		// Get renewal page
		$page_id = edd_get_option( 'edd_members_renew_page' );
		
		if( !empty( $page_id ) ) {
			$edd_renewal_page = '<a href="' . esc_url( get_permalink( $page_id ) ) . '">' . bloginfo( 'name' ) . '</a>';
		} else {
			$edd_renewal_page = '<a href="' . esc_url( home_url( '/' ) ) . '">' . bloginfo( 'name' ) . '</a>';
		}
		
		$text = str_replace( '{name}', $customer_name, $text );
		$text = str_replace( '{edd_members_expiration}', $exprire_date, $text );
		$text = str_replace( '{edd_members_page}', $edd_renewal_page, $text );

		return $text;
	}


}
$edd_members_emails = new EDD_Members_Emails;
