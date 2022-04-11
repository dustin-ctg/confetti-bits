<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'Confetti_Bits_admin_enqueue_script' ) ) {
	function Confetti_Bits_admin_enqueue_script() {
		wp_enqueue_style( 'buddyboss-addon-admin-css', plugin_dir_url( __FILE__ ) . 'style.css' );
	}

	add_action( 'admin_enqueue_scripts', 'Confetti_Bits_admin_enqueue_script' );
}

add_action(
   'bp_init',
   function () {
      // Register custom notification in preferences screen.
      if ( class_exists( 'Confetti_Bits_Notifications_Component' ) ) {
         Confetti_Bits_Notifications_Component::instance();
      }
   }
);

if ( ! function_exists( 'Confetti_Bits_get_settings_sections' ) ) {
	function Confetti_Bits_get_settings_sections() {

		$settings = array(
			'Confetti_Bits_settings_section' => array(
				'page'  => 'addon',
				'title' => __( 'Confetti Bits Settings', 'buddyboss-platform-addon' ),
			),
		);

		return (array) apply_filters( 'Confetti_Bits_get_settings_sections', $settings );
	}
}



if ( ! function_exists( 'Confetti_Bits_get_settings_fields_for_section' ) ) {
	function Confetti_Bits_get_settings_fields_for_section( $section_id = '' ) {

		// Bail if section is empty
		if ( empty( $section_id ) ) {
			return false;
		}

		$fields = Confetti_Bits_get_settings_fields();
		$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

		return (array) apply_filters( 'Confetti_Bits_get_settings_fields_for_section', $retval, $section_id );
	}
}

if ( ! function_exists( 'Confetti_Bits_get_settings_fields' ) ) {
	function Confetti_Bits_get_settings_fields() {

		$fields = array();

		$fields['Confetti_Bits_settings_section'] = array(

			'Confetti_Bits_field' => array(
				'title'             => __( 'Confetti Bits Field', 'buddyboss-platform-addon' ),
				'callback'          => 'Confetti_Bits_settings_callback_field',
				'sanitize_callback' => 'absint',
				'args'              => array(),
			),

		);

		return (array) apply_filters( 'Confetti_Bits_get_settings_fields', $fields );
	}
}

if ( ! function_exists( 'Confetti_Bits_settings_callback_field' ) ) {
	function Confetti_Bits_settings_callback_field() {
		?>
        <input name="Confetti_Bits_field"
               id="Confetti_Bits_field"
               type="checkbox"
               value="1"
			<?php checked( Confetti_Bits_is_addon_field_enabled() ); ?>
        />
        <label for="Confetti_Bits_field">
			<?php _e( 'Enable this option', 'buddyboss-platform-addon' ); ?>
        </label>
		<?php
	}
}

if ( ! function_exists( 'Confetti_Bits_is_addon_field_enabled' ) ) {
	function Confetti_Bits_is_addon_field_enabled( $default = 1 ) {
		return (bool) apply_filters( 'Confetti_Bits_is_addon_field_enabled', (bool) get_option( 'Confetti_Bits_field', $default ) );
	}
}

/***************************** Add section in current settings ***************************************/

/**
 * Register fields for settings hooks
 * bp_admin_setting_general_register_fields
 * bp_admin_setting_xprofile_register_fields
 * bp_admin_setting_groups_register_fields
 * bp_admin_setting_forums_register_fields
 * bp_admin_setting_activity_register_fields
 * bp_admin_setting_media_register_fields
 * bp_admin_setting_friends_register_fields
 * bp_admin_setting_invites_register_fields
 * bp_admin_setting_search_register_fields
 */
if ( ! function_exists( 'Confetti_Bits_bp_admin_setting_general_register_fields' ) ) {
    function Confetti_Bits_bp_admin_setting_general_register_fields( $setting ) {
	    // Main General Settings Section
	    $setting->add_section( 'Confetti_Bits_addon', __( 'Confetti Bits Settings', 'buddyboss-platform-addon' ) );

	    $args          = array();
	    $setting->add_field( 'bp-enable-my-addon', __( 'My Field', 'buddyboss-platform-addon' ), 'Confetti_Bits_admin_general_setting_callback_my_addon', 'intval', $args );
    }

	add_action( 'bp_admin_setting_general_register_fields', 'Confetti_Bits_bp_admin_setting_general_register_fields' );
}

if ( ! function_exists( 'Confetti_Bits_admin_general_setting_callback_my_addon' ) ) {
	function Confetti_Bits_admin_general_setting_callback_my_addon() {
		?>
        <input id="bp-enable-my-addon" name="bp-enable-my-addon" type="checkbox"
               value="1" <?php checked( Confetti_Bits_enable_my_addon() ); ?> />
        <label for="bp-enable-my-addon"><?php _e( 'Enable Confetti Bits?', 'buddyboss-platform-addon' ); ?></label>
		<?php
	}
}

if ( ! function_exists( 'Confetti_Bits_enable_my_addon' ) ) {
	function Confetti_Bits_enable_my_addon( $default = false ) {
		return (bool) apply_filters( 'Confetti_Bits_enable_my_addon', (bool) bp_get_option( 'bp-enable-my-addon', $default ) );
	}
}


/**************************************** MY PLUGIN INTEGRATION ************************************/

/**
 * Set up the my plugin integration.
 */
function Confetti_Bits_register_integration() {
	require_once dirname( __FILE__ ) . '/integration/buddyboss-integration.php';
	buddypress()->integrations['addon'] = new Confetti_Bits_BuddyBoss_Integration();
}
add_action( 'bp_setup_integrations', 'Confetti_Bits_register_integration' );
