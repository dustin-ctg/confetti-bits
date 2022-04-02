<?php
/**
 * Confetti Bits Notifications Loader.
 *
 * Establishes the Confetti Bits Notifications component.
 *
 * @since BuddyPress 1.9.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Extends the component class to set up the Notifications component.
 */
class Confetti_Bits_Notifications_Component extends BP_Component {
//	$bp = buddypress();
	/**
	 * Start the notifications component creation process.
	 *
	 * @since BuddyPress 1.9.0
	 */
	public function __construct() {
		parent::start(
			'confetti-bits-notifications',
			__( 'Confetti Bits Notifications', 'buddyboss' ),
			CONFETTI_BITS_ADDON_PLUGIN_PATH . 'bp-confetti-bits-notifications',
			);
		
		$this->includes();
		
	}
	
	public function includes( $includes = array() ) {
		$includes = array(
			'functions',
			'loader',
			'notifications',
		);
		parent::includes( $includes );
	}

	
	/**
	 * Late includes method.
	 *
	 * Only load up certain code when on specific pages.
	 *
	 * @since BuddyPress 3.0.0
	 */
	public function late_includes() {
		// Bail if PHPUnit is running.
//		if ( defined( 'BP_TESTS_DIR' ) ) {
//			return;
//		}

		// Bail if not on a notifications page or logged in.
//		if ( ! bp_is_user_notifications() || ! is_user_logged_in() ) {
//			return;
//		}

		// Actions.
//		if ( bp_is_post_request() ) {
//			require $this->path . 'bp-notifications/actions/bulk-manage.php';
//		} elseif ( bp_is_get_request() ) {
//			require $this->path . 'bp-notifications/actions/delete.php';
//		}

		// Screens.
//		require $this->path . 'bp-notifications/screens/unread.php';
//		if ( bp_is_current_action( 'read' ) ) {
//			require $this->path . 'bp-notifications/screens/read.php';
//		}
	}

	/**
	 * Set up component global data.
	 *
	 * @since BuddyPress 1.9.0
	 *
	 * @see BP_Component::setup_globals() for a description of arguments.
	 *
	 * @param array $args See BP_Component::setup_globals() for a description.
	 */
	
	public function setup_globals( $args = array() ) {


		// Define a slug, if necessary.
		if ( ! defined( 'CONFETTI_BITS_NOTIFICATIONS_SLUG' ) ) {
			define( 'CONFETTI_BITS_NOTIFICATIONS_SLUG', $this->id );
		}

		// Global tables for the notifications component.
		$global_tables = array(
			'table_name'      => $bp->table_prefix . 'confetti_bits_notifications',
			'table_name_meta' => $bp->table_prefix . 'confetti_bits_notifications_meta',
		);

		// Metadata tables for notifications component.
		$meta_tables = array(
			'confetti_bits_notification' => $bp->table_prefix . 'bp_notifications_meta',
		);

		// All globals for the notifications component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'          => CONFETTI_BITS_NOTIFICATIONS_SLUG,
			'has_directory' => false,
			'search_string' => __( 'Search Notifications...', 'buddyboss' ),
			'notification_callback' => 'confetti_bits_format_notifications',
			'global_tables' => $global_tables,
			'meta_tables'   => $meta_tables,
		);

		parent::setup_globals( $args );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since BuddyPress 1.9.0
	 */
	public function setup_title() {

		// Adjust title.
		if ( get_current_user_id() ) {
			$bp = buddypress();

			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'Confetti Bits Notifications', 'buddyboss' );
			} else {
				$bp->bp_options_avatar = bp_core_fetch_avatar(
					array(
						'item_id' => bp_displayed_user_id(),
						'type'    => 'thumb',
						'alt'     => sprintf( __( 'Profile photo of %s', 'buddyboss' ), bp_get_displayed_user_fullname() ),
					)
				);
				$bp->bp_options_title  = bp_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}



	
	/**
	 * Setup cache groups.
	 *
	 * @since BuddyPress 2.2.0
	 */
	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups(
			array(
				'confetti_bits_notifications',
				'confetti_bits_notification_meta',
				'confetti_bits_notifications_unread_count',
				'confetti_bits_notifications_grouped_notifications',
			)
		);

		parent::setup_cache_groups();
	}
}
