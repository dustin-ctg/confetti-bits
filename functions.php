<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'confetti_bits_admin_enqueue_script' ) ) {
	function confetti_bits_admin_enqueue_script() {
		wp_enqueue_style( 'confetti-bits-admin-css', plugin_dir_url( __FILE__ ) . 'style.css' );
	}

	add_action( 'admin_enqueue_scripts', 'confetti_bits_admin_enqueue_script' );
}

add_action(
   'bp_init',
   function () {
      if ( class_exists( 'Confetti_Bits_Notifications_Component' ) ) {
         Confetti_Bits_Notifications_Component::instance();
      }
   }
);

if ( ! function_exists( 'confetti_bits_register_integration' ) ) {
	function confetti_bits_register_integration() {
		require_once dirname( __FILE__ ) . '/integration/buddyboss-integration.php';
		buddypress()->integrations['addon'] = new Confetti_Bits_BuddyBoss_Integration();
	}
}
add_action( 'bp_setup_integrations', 'confetti_bits_register_integration' );

if ( ! function_exists( 'confetti_bits_get_settings_sections' ) ) {
	function confetti_bits_get_settings_sections() {

		$settings = array(
			'confetti_bits_settings_section' => array(
				'page'  => 'addon',
				'title' => __( 'Confetti Bits Settings', 'confetti-bits' ),
			),
		);

		return (array) apply_filters( 'confetti_bits_get_settings_sections', $settings );
	}
}



if ( ! function_exists( 'confetti_bits_get_settings_fields_for_section' ) ) {
	function confetti_bits_get_settings_fields_for_section( $section_id = '' ) {

		// Bail if section is empty
		if ( empty( $section_id ) ) {
			return false;
		}

		$fields = confetti_bits_get_settings_fields();
		$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

		return (array) apply_filters( 'confetti_bits_get_settings_fields_for_section', $retval, $section_id );
	}
}

if ( ! function_exists( 'confetti_bits_get_settings_fields' ) ) {
	function confetti_bits_get_settings_fields() {

		$fields = array();

		$fields['confetti_bits_settings_section'] = array(

			'confetti_bits_field' => array(
				'title'             => __( 'Confetti Bits Field', 'confetti-bits' ),
				'callback'          => 'confetti_bits_settings_callback_field',
				'sanitize_callback' => 'absint',
				'args'              => array(),
			),

		);

		return (array) apply_filters( 'confetti_bits_get_settings_fields', $fields );
	}
}

if ( ! function_exists( 'confetti_bits_settings_callback_field' ) ) {
	function confetti_bits_settings_callback_field() {
		?>
        <input name="confetti_bits_field"
               id="confetti_bits_field"
               type="checkbox"
               value="1"
			<?php checked( confetti_bits_is_addon_field_enabled() ); ?>
        />
        <label for="confetti_bits_field">
			<?php _e( 'Enable this option', 'confetti-bits' ); ?>
        </label>
		<?php
	}
}

if ( ! function_exists( 'confetti_bits_is_enabled' ) ) {
	function confetti_bits_is_enabled( $default = 1 ) {
		return (bool) apply_filters( 'confetti_bits_is_enabled', (bool) get_option( 'confetti_bits_field', $default ) );
	}
}