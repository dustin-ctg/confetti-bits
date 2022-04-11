<?php
/**
 * Plugin Name: Confetti Bits
 * Plugin URI:  https://dustindelgross.com/
 * Description: This is the TeamCTG platform add-on for the Confetti Bits program.
 * Author:      Dustin Delgross
 * Author URI:  https://dustindelgross.com/
 * Version:     2.0.0
 * Text Domain: confetti-bits
 * Domain Path: /languages/
 * License:     GPLv3 or later (license.txt)
 */

/**
 * This file should always remain compatible with the minimum version of
 * PHP supported by WordPress.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'Confetti_Bits_Platform_Addon' ) ) {

	/**
	 * Main Confetti Bits Custom Emails Class
	 *
	 * @class Confetti_Bits_Platform_Addon
	 * @version	1.0.0
	 */
	final class Confetti_Bits_Platform_Addon {

		/**
		 * @var Confetti_Bits_Platform_Addon The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main Confetti_Bits_Platform_Addon Instance
		 *
		 * Ensures only one instance of Confetti_Bits_Platform_Addon is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see Confetti_Bits_Platform_Addon()
		 * @return Confetti_Bits_Platform_Addon - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'buddyboss-platform-addon' ), '1.0.0' );
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'buddyboss-platform-addon' ), '1.0.0' );
		}

		/**
		 * Confetti_Bits_Platform_Addon Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->load_plugin_textdomain();
		}

		/**
		 * Define WCE Constants
		 */
		private function define_constants() {
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_FILE', __FILE__ );
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_IS_INSTALLED', 1);
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_VERSION', '2.0.0');
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_DB_VERSION', '2.0.0');
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'CONFETTI_BITS_ADDON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			spl_autoload_register( array( $this, 'load_components' ) );
			require CONFETTI_BITS_ADDON_PLUGIN_PATH . 'functions.php';
		}

		public function load_components( $class ) {
			
			$class_parts = explode( '_', strtolower( $class ) );
			
			if ( 'confetti' !== $class_parts[0] ) {
				return;
			}
			
			$components = array ('notifications');

			if ( in_array( $class_parts[2], $components, true ) ) {
				$component = $class_parts[2];
			}
			
			$class = strtolower( str_replace( '_', '-', $class ) );
			
			$path = dirname( __FILE__ ) . "/bp-confetti-bits-{$component}/classes/class-{$class}.php";
			
			if ( ! file_exists( $path ) ) {
				return;
			}
			
			require $path;			
			
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'buddyboss-platform-addon' );

			unload_textdomain( 'buddyboss-platform-addon' );
			load_textdomain( 'buddyboss-platform-addon', WP_LANG_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' . plugin_basename( dirname( __FILE__ ) ) . '-' . $locale . '.mo' );
			load_plugin_textdomain( 'buddyboss-platform-addon', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}
	}

	/**
	 * Returns the main instance of Confetti_Bits_Platform_Addon to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return Confetti_Bits_Platform_Addon
	 */
	function Confetti_Bits() {
		return Confetti_Bits_Platform_Addon::instance();
	}

	function Confetti_Bits_Platform_install_bb_platform_notice() {
		echo '<div class="error fade"><p>';
		_e('<strong>Confetti Bits</strong></a> requires the BuddyBoss Platform plugin to work. Please <a href="https://buddyboss.com/platform/" target="_blank">install BuddyBoss Platform</a> first.', 'buddyboss-platform-addon');
		echo '</p></div>';
	}

	function Confetti_Bits_Platform_update_bb_platform_notice() {
		echo '<div class="error fade"><p>';
		_e('<strong>Confetti Bits</strong></a> requires BuddyBoss Platform plugin version 1.2.6 or higher to work. Please update BuddyBoss Platform.', 'buddyboss-platform-addon');
		echo '</p></div>';
	}

	function Confetti_Bits_Platform_is_active() {
		if ( defined( 'BP_PLATFORM_VERSION' ) && version_compare( BP_PLATFORM_VERSION,'1.2.6', '>=' ) ) {
			return true;
		}
		return false;
	}

	function Confetti_Bits_Platform_init() {
		if ( ! defined( 'BP_PLATFORM_VERSION' ) ) {
			add_action( 'admin_notices', 'Confetti_Bits_Platform_install_bb_platform_notice' );
			add_action( 'network_admin_notices', 'Confetti_Bits_Platform_install_bb_platform_notice' );
			return;
		}

		if ( version_compare( BP_PLATFORM_VERSION,'1.2.6', '<' ) ) {
			add_action( 'admin_notices', 'Confetti_Bits_Platform_update_bb_platform_notice' );
			add_action( 'network_admin_notices', 'Confetti_Bits_Platform_update_bb_platform_notice' );
			return;
		}

		Confetti_Bits();
	}

	add_action( 'plugins_loaded', 'Confetti_Bits_Platform_init', 9 );
}

