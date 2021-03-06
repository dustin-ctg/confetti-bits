<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
class Confetti_Bits_Core extends BP_Component {
	
	public function __construct() {
		parent::start(
			'core',
			__('Confetti Bits Core', 'confetti-bits'),
			CONFETTI_BITS_PLUGIN_PATH
		);

		$this->bootstrap();
	}

	private function bootstrap() {
		
		$this->includes();
		$this->load_components();

	}

	public function load_components() {
		
		$cb = Confetti_Bits();
		
		$cb->optional_components = apply_filters( 'cb_optional_components', array_keys( cb_core_get_components( 'optional' ) ) );

		$cb->required_components = apply_filters( 'cb_required_components', array( 'transactions' ) );

		// Get a list of activated components.
		if ( $active_components = bp_get_option( 'cb_active_components' ) ) {

			$cb->active_components = apply_filters( 'cb_active_components', $active_components );

			$cb->deactivated_components = apply_filters( 'cb_deactivated_components', array_values( array_diff( array_values( array_merge( $cb->optional_components, $cb->required_components ) ), array_keys( $cb->active_components ) ) ) );

			// Pre 1.5 Backwards compatibility.
		} elseif ( $deactivated_components = bp_get_option( 'cb-deactivated-components' ) ) {

			// Trim off namespace and filename.
			foreach ( array_keys( (array) $deactivated_components ) as $component ) {
				$trimmed[] = str_replace( '.php', '', str_replace( 'bp-confetti-bits-', '', $component ) );
			}


			$cb->deactivated_components = apply_filters( 'cb_deactivated_components', $trimmed );

			// Setup the active components.
			$active_components = array_fill_keys( array_diff( array_values( array_merge( $cb->optional_components, $cb->required_components ) ), array_values( $cb->deactivated_components ) ), '1' );


			$cb->active_components = apply_filters( 'cb_active_components', $cb->active_components );

			// Default to all components active.
		} else {

			// Set globals.
			$cb->deactivated_components = array();

			// Setup the active components.
			$active_components = array_fill_keys( array_values( array_merge( $cb->optional_components, $cb->required_components ) ), '1' );


			$cb->active_components = apply_filters( 'cb_active_components', $cb->active_components );
		}

		// Loop through optional components.
		foreach ( $cb->optional_components as $component ) {
			if ( cb_is_active( $component ) && file_exists( $cb->plugin_dir . 'bp-confetti-bits-' . $component . '/bp-confetti-bits-' . $component . '-loader.php' ) ) {
				include $cb->plugin_dir . 'bp-confetti-bits-' . $component . '/bp-confetti-bits-' . $component . '-loader.php';
			}
		}

		// Loop through required components.
		foreach ( $cb->required_components as $component ) {
			if ( file_exists( $cb->plugin_dir . 'bp-confetti-bits-' . $component . '/bp-confetti-bits-' . $component . '-loader.php' ) ) {
				include $cb->plugin_dir . 'bp-confetti-bits-' . $component . '/bp-confetti-bits-' . $component . '-loader.php';
			}
		}

		// Add Core to required components.
		$cb->required_components[] = 'core';

		do_action( 'cb_core_components_included' );
		
	}

	private function load_integrations() {}

	public function includes( $includes = array() ) {

		// Files to include.
		$includes = array(
		
			'admin',
			'components',
			
		);
		
		// Bail if no files to include.
		if ( ! empty( $includes ) ) {
			$slashed_path = trailingslashit( $this->path );

			// Loop through files to be included.
			foreach ( (array) $includes as $file ) {

				$paths = array(

					// Passed with no extension.
					'bp-confetti-bits-' . $this->id . '/bp-confetti-bits-' . $this->id . '-' . $file . '.php',
					'bp-confetti-bits-' . $this->id . '-' . $file . '.php',
					'bp-confetti-bits-' . $this->id . '/' . $file . '.php',

					// Passed with extension.
					$file,
					'bp-confetti-bits-' . $this->id . '-' . $file,
					'bp-confetti-bits-' . $this->id . '/' . $file,
				);

				foreach ( $paths as $path ) {
					if ( @is_file( $slashed_path . $path ) ) {
						require $slashed_path . $path;
						break;
					}
				}
			}
		}
	}

	public function setup_globals( $args = array() ) {}

	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups(
			array(
				'confetti_bits_core',
				'confetti_bits_core_pages',
				'confetti_bits_core_transactions',
			)
		);

		parent::setup_cache_groups();
	}

	public function register_post_types() {
		
	}

}