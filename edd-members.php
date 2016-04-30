<?php
/**
 * Plugin Name:     EDD Members
 * Plugin URI:      https://foxland.fi/downloads/edd-members/
 * Description:     Create membership site with EDD Members. 
 * Version:         1.2.1
 * Author:          Sami Keijonen
 * Author URI:      https://foxland.fi/
 * Text Domain:     edd-members
 * Domain Path:     /languages
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package         EDD\EDDMembers
 * @author          Sami Keijonen <sami.keijonen@foxnet.fi>
 * @copyright       Copyright (c) Sami Keijonen
 * @license         http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'EDD_Members' ) ) {

	/**
	 * Main EDD_Members class
	 *
	 * @since       1.0.0
	 */
	class EDD_Members {

		/**
		* @var         EDD_Members $instance The one true EDD_Members
		* @since       1.0.0
		*/
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true EDD_Members
		 */
		public static function instance() {
			if( !self::$instance ) {
				self::$instance = new EDD_Members();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			
			// Plugin version
			define( 'EDD_MEMBERS_VER', '1.2.1' );

			// Plugin path
			define( 'EDD_MEMBERS_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_MEMBERS_URL', plugin_dir_url( __FILE__ ) );

		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
		
			// Get out if EDD is not active
			if( ! function_exists( 'EDD' ) ) {
				return;
			}
			
			// Include files and scripts
			if ( is_admin() ) {
				require_once EDD_MEMBERS_DIR . 'includes/scripts.php';
				require_once EDD_MEMBERS_DIR . 'includes/meta-boxes.php';
				require_once EDD_MEMBERS_DIR . 'includes/settings.php';
				require_once EDD_MEMBERS_DIR . 'includes/user-meta.php';
			}
			
			require_once EDD_MEMBERS_DIR . 'includes/functions.php';
			require_once EDD_MEMBERS_DIR . 'includes/functions-filters.php';
			require_once EDD_MEMBERS_DIR . 'includes/shortcodes.php';
			require_once EDD_MEMBERS_DIR . 'includes/renewals.php';
			require_once EDD_MEMBERS_DIR . 'includes/EDD_Members_Emails.php';
		}
		
		
		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
		
			// Load the default language files
			load_plugin_textdomain( 'edd-members', false, 'edd-members/languages' );
			
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
         */
		private function hooks() {
			
			// Register section for settings
			add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_sections' ) );
            
			// Register settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );

			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'EDD Members', EDD_MEMBERS_VER, 'Sami Keijonen', null, 'http://foxland.fi/' );
			}
		}
		
		/**
		 * Add section for settings
		 *
		 * @access      public
		 * @since       1.1.5
		 * @param       array $sections The existing EDD sections array
		 * @return      array The modified EDD sections array
		 */
		public function settings_sections( $sections ) {
				
			$sections['edd-members-settings-section'] = esc_html__( 'EDD Members', 'edd-members' );
			return $sections;
				
		}


		/**
		 * Add settings
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $settings The existing EDD settings array
		 * @return      array The modified EDD settings array
		 */
		public function settings( $settings ) {
            
			$edd_members_settings = array(
				array(
					'id'          => 'edd_members_settings',
					'name'        => '<strong>' . esc_html__( 'EDD Members Settings', 'edd-members' ) . '</strong>',
					'desc'        => esc_html__( 'Configure EDD Members Settings', 'edd-members' ),
					'type'        => 'header',
				),
				array(
					'id'          => 'edd_members_private_post_type',
					'name'        => esc_html__( 'Private content', 'edd-members' ),
					'desc'        => esc_html__( 'Select which post type content you want to have private. Note! Only singular views will be private.', 'edd-members' ),
					'type'        => 'multicheck',
					'options'     => edd_members_get_public_post_types()
				),
				array(
					'id'          => 'edd_members_private_comments',
					'name'        => esc_html__( 'Private comments', 'edd-members' ),
					'desc'        => esc_html__( 'Check this if you want to set comments private for above selected content.', 'edd-members' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'edd_members_private_feed',
					'name'        => esc_html__( 'Private feed', 'edd-members' ),
					'desc'        => esc_html__( 'Check this if you want to set all feeds private.', 'edd-members' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'edd_members_settings_private_label_logged_out',
					'name'        => esc_html__( 'Private Label logged out', 'edd-members' ),
					'desc'        => esc_html__( 'Enter the text for private content when user is logged out.', 'edd-members' ),
					'type'        => 'rich_editor',
					'size'        => 15,
					'std'         => esc_html__( 'This content is for members only.', 'edd-members' )
				),
				array(
					'id'          => 'edd_members_settings_private_label_logged_in',
					'name'        => esc_html__( 'Private Label logged in', 'edd-members' ),
					'desc'        => esc_html__( 'Enter the text for private content when user is logged in.', 'edd-members' ),
					'type'        => 'rich_editor',
					'size'        => 15,
					'std'         => esc_html__( 'This content is for members only. Your membership have probably expired.', 'edd-members' )
				),
				array(
					'id'          => 'edd_members_show_login_form',
					'name'        => esc_html__( 'Login form', 'edd-members' ),
					'desc'        => esc_html__( 'Check this box if you want to show login form for logged out users.', 'edd-members' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'edd_members_send_renewal_reminders',
					'name'        => esc_html__( 'Send Renewal Reminders', 'edd-members' ),
					'desc'        => esc_html__( 'Check this box if you want customers to receive a renewal reminder when their membership is about to expire.', 'edd-members' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'members_renewal_notices', // EDD adds prefix 'edd_' in hook type
					'name'        => esc_html__( 'Renewal Notices', 'edd-members' ),
					'desc'        => esc_html__( 'Configure the renewal notice emails.', 'edd-members' ),
					'type'        => 'hook'
				)
			);
			
			// Support for Recurring Payments Plugin
			if( class_exists( 'EDD_Recurring' ) ) {
				$edd_members_settings[2] = array(
					'id'      => 'edd_members_rc_all_active_only',
					'name'    => esc_html__( 'Active Subscribers Only?', 'edd-members' ),
					'desc'    => esc_html__( 'Check this if you want to show content only for active subscribers which are set in EDD Recurring Payments Plugin.', 'edd-members' ),
					'type'    => 'checkbox',
				);
			}
			
			// If EDD is at version 2.5 or later use section for settings.
			if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
				// Use the previously noted array key as an array key again and next your settings
				$edd_members_settings = array( 'edd-members-settings-section' => $edd_members_settings );
			}

			return array_merge( $settings, $edd_members_settings );
		}
        
        
		/*
		 * Activation function fires when the plugin is activated.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public static function activation() {
		
			// Get the administrator role
			$role = get_role( 'administrator' );
			
			// If the administrator role exists, add required capabilities for the plugin
			if ( !empty( $role ) ) {
				$role->add_cap( 'edd_members_show_all_content' );
				$role->add_cap( 'edd_members_edit_user' );
			}
		}
          
	}


	/**
	 * The main function responsible for returning the one true EDD_Members
	 * instance to functions everywhere
	 *
	 * @since       1.0.0
	 * @return      \EDD_Members The one true EDD_Members
	 */
	function EDD_Members_load() {
	
		if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			if( ! class_exists( 'EDD_Extension_Activation' ) ) {
				require_once 'includes/class.extension-activation.php';
			}

			$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$activation = $activation->run();
		} else {
			return EDD_Members::instance();
		}

	}

	/**
	 * The activation hook is called outside of the singleton because WordPress doesn't
	 * register the call from within the class hence, needs to be called outside and the
	 * function also needs to be static.
	 */
	register_activation_hook( __FILE__, array( 'EDD_Members', 'activation' ) );

	add_action( 'plugins_loaded', 'EDD_Members_load' );

} // End if class_exists check