<?php
/**
 * Plugin Name:     EDD Members
 * Plugin URI:      https://foxland.fi/downloads/edd-members
 * Description:     Create membership site with EDD Members. 
 * Version:         1.0.0
 * Author:          Sami Keijonen
 * Author URI:      https://foxland.fi
 * Text Domain:     edd-members
 *
 * @package         EDD\EDDMembers
 * @author          Sami Keijonen <sami.keijonen@foxnet.fi>
 * @copyright       Copyright (c) Sami Keijonen
 *
 * IMPORTANT! Ensure that you make the following adjustments
 * before releasing your extension:
 *
 * - Replace all instances of plugin-name with the name of your plugin.
 *   By WordPress coding standards, the folder name, plugin file name,
 *   and text domain should all match. For the purposes of standardization,
 *   the folder name, plugin file name, and text domain are all the
 *   lowercase form of the actual plugin name, replacing spaces with
 *   hyphens.
 *
 * - Replace all instances of Plugin_Name with the name of your plugin.
 *   For the purposes of standardization, the camel case form of the plugin
 *   name, replacing spaces with underscores, is used to define classes
 *   in your extension.
 *
 * - Replace all instances of PLUGINNAME with the name of your plugin.
 *   For the purposes of standardization, the uppercase form of the plugin
 *   name, removing spaces, is used to define plugin constants.
 *
 * - Replace all instances of Plugin Name with the actual name of your
 *   plugin. This really doesn't need to be anywhere other than in the
 *   EDD Licensing call in the hooks method.
 *
 * - Find all instances of @todo in the plugin and update the relevant
 *   areas as necessary.
 *
 * - All functions that are not class methods MUST be prefixed with the
 *   plugin name, replacing spaces with underscores. NOT PREFIXING YOUR
 *   FUNCTIONS CAN CAUSE PLUGIN CONFLICTS!
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
				self::$instance->setup_actions();
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
			define( 'EDD_MEMBERS_VER', '1.0.0' );

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
			
			// Include files and scripts
			require_once EDD_MEMBERS_DIR . 'includes/scripts.php';
			require_once EDD_MEMBERS_DIR . 'includes/functions.php';
			require_once EDD_MEMBERS_DIR . 'includes/class-gamajo-template-loader.php';
			require_once EDD_MEMBERS_DIR . 'includes/class-svamuli-template-loader.php';
			require_once EDD_MEMBERS_DIR . 'includes/meta-boxes.php';
			
		}
		
		
		/**
		 * Setup plugin actions
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_actions() {
		
			// Internationalize the text strings used
			add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );
		
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
         */
		private function hooks() {
            
			// Register settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );

			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'EDD Members', EDD_MEMBERS_VER, 'Sami Keijonen' );
			}
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function i18n() {
		
			// Load the default language files
			load_plugin_textdomain( 'edd-members', false, 'edd-members/languages' );
			
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
					'id'   => 'edd_members_settings',
					'name' => '<strong>' . __( 'EDD Members Settings', 'edd-members' ) . '</strong>',
					'desc' => __( 'Configure EDD Members Settings', 'edd-members' ),
					'type' => 'header',
				),
				array(
					'id'      => 'edd_members_settings_private_label_logged_out',
					'name'    => __( 'Private Label logged out', 'edd-members' ),
					'desc'    => __( 'Enter the text you for private content when user is logged out', 'edd-members' ),
					'type'    => 'text',
					'size'    => 'regular',
					'std'     => __( 'This content is for members only.', 'edd-members' )
				),
				array(
					'id'      => 'edd_members_settings_private_label_logged_in',
					'name'    => __( 'Private Label logged in', 'edd-members' ),
					'desc'    => __( 'Enter the text you for private content when user is logged in', 'edd-members' ),
					'type'    => 'text',
					'size'    => 'regular',
					'std'     => __( 'This content is for members only. Your membership have probably expired.', 'edd-members' )
				),
				array(
					'id'      => 'edd_members_show_login_form',
					'name'    => __( 'Login form', 'edd-members' ),
					'desc'    => __( 'Show login form for logged out users', 'edd-members' ),
					'type'    => 'checkbox'
				),
				array(
					'id'      => 'edd_members_private_post_type',
					'name'    => __( 'Private content', 'edd-members' ),
					'desc'    => __( 'Select which post type content you want to have private. Note! Only singular views will be private', 'edd-members' ),
					'type'    => 'multicheck',
					'options' => edd_members_get_public_post_types()
				)
			);

			return array_merge( $settings, $edd_members_settings );
		}
        
        
		/*
		 * Activation function fires when the plugin is activated.
		 *
		 * This function is fired when the activation hook is called by WordPress,
		 * 
		 */
		public static function activation() {
		
		// Activation functions here

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
			return EDD_Members::instance();
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
