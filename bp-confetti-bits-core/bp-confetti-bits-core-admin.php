<?php 
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
 * 
 * We're going to have our own little box for these settings. 
 */

/*
 * Registers our settings field in the context of the BuddyBoss settings page environment.
 * Key part of this is the "bp_admin_setting_{ component }_register_fields"
 * 
 * add_section is a wordpress method to create the settings section. requires a setting object
 * add_field is a wordpress method that creates a field within the section we just made
 * 
 * bp_admin_setting_{ component }_register_fields handles the rest
 * 
 * */

if ( ! function_exists( 'confetti_bits_bp_admin_setting_general_register_fields' ) ) {
	function confetti_bits_bp_admin_setting_general_register_fields( $setting ) {
		// Main General Settings Section
		$setting->add_section( 'confetti_bits_addon', __( 'Confetti Bits Settings', 'confetti-bits' ) );
		$args          = array();
		$setting->add_field( 'cb_components', __( 'Enable Transactions', 'confetti-bits' ), 'cb_admin_components_settings', 'intval', $args );
	}
	add_action( 'bp_admin_setting_general_register_fields', 'confetti_bits_bp_admin_setting_general_register_fields' );
}

if ( ! function_exists( 'cb_admin_components_settings' ) ) {
function cb_admin_components_settings() { ?>
<form action="" method="post" id="cb-admin-component-form">

	<?php cb_admin_components_options(); ?>

</form><?php }
}

if ( ! function_exists( 'cb_admin_components_options' ) ) {

	function cb_admin_components_options() {
		$deactivated_components = array();


		$active_components = apply_filters( 'cb_active_components', bp_get_option('cb_active_components') );
		$default_components  = cb_core_admin_get_components( 'default' );
		$optional_components = cb_core_admin_get_components( 'optional' );
		$required_components = cb_core_admin_get_components( 'required' );

		$all_components = $required_components + $optional_components;

		if ( empty( $active_components ) ) {
			$active_components = $default_components;
		}


		$current_components = $all_components;

		$page      = bp_core_do_network_admin() ? 'admin.php' : 'admin.php';
		$action    = ! empty( $_GET['action'] ) ? $_GET['action'] : 'all';

		switch ( $action ) {
			case 'all':
				$current_components = $all_components;
				break;
			case 'active':
				foreach ( array_keys( $active_components ) as $component ) {
					if ( isset( $all_components[ $component ] ) ) {
						$current_components[ $component ] = $all_components[ $component ];
					}
				}
				break;
			case 'inactive':
				foreach ( $inactive_components as $component ) {
					if ( isset( $all_components[ $component ] ) ) {
						$current_components[ $component ] = $all_components[ $component ];
					}
				}
				break;
			case 'mustuse':
				$current_components = $required_components;
				break;
		}


		if ( ! empty( $current_components ) ) :

		foreach ( $current_components as $name => $labels ) :
?>
<input id="<?php echo esc_attr( "cb_components[$name]" ) ?>" 
	   name="<?php echo esc_attr( "cb_components[$name]" ) ?>" type="checkbox"
	   value="1" <?php checked( isset( $active_components[ esc_attr( $name ) ] ) ); ?> />
<label for="<?php echo esc_attr( "cb_components[$name]" ) ?>">
	<?php echo esc_html( $labels['title'], 'confetti-bits' ); ?>
</label>

<p><?php echo esc_html($labels['description']); ?></p>

<a href="<?php 
		echo 
			bp_get_admin_url(
				add_query_arg(
					array(
						'page' => 'bp-settings',
						'action' => $action,
						'cb_component' => $name,
						'cb_action' => 'activate',
					),
					$page
				)
		);
		 ?>">Activate</a><br>
<input type="submit" id="action" class="button action" name="cb-admin-component-submit" value="<?php esc_attr_e( 'Apply', 'buddyboss' ); ?>">

<?php endforeach ?>

<?php else : ?>

<tr class="no-items">
	<td class="colspanchange" colspan="3"><?php _e( 'No components found.', 'buddyboss' ); ?></td>
</tr>

<?php endif;
	}
}

if ( ! function_exists( 'cb_is_user_admin' ) )  {
	function cb_is_user_admin() {
		return bp_current_user_can('edit_users');
	}
}

if ( ! function_exists( 'cb_is_user_site_admin' ) ) {
	function cb_is_user_site_admin() {
		return current_user_can('edit_plugins');
	}
}

if ( ! function_exists( 'cb_core_admin_component_activation_handler' ) ) {
	function cb_core_admin_component_activation_handler() {

		if ( ! isset( $_GET['cb_component'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'cb-admin-component-activation' ) ) {
			return;
		}

		// Settings form submitted, now save the settings. First, set active components.
		if ( isset( $_GET['cb_component'] ) ) {

			// Load up BuddyPress.
			$cb = Confetti_Bits();

			// Save settings and upgrade schema.
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$current_action = 'active';
			if ( isset( $_GET['cb_action'] ) && in_array( $_GET['cb_action'], array( 'activate', 'deactivate' ) ) ) {
				$current_action = $_GET['cb_action'];
			}

			$current_components = $cb->active_components;

			$submitted = stripslashes_deep( $_GET['cb_component'] );

			switch ( $current_action ) {
				case 'deactivate' :
					foreach( $current_components as $key => $component ) {
						if ( $submitted == $key ) {
							unset( $current_components[ $key ] );
						}
					}
					$cb->active_components = $current_components;
					break;

				case 'active' :
				default :
					$cb->active_components = array_merge( array( $submitted => $current_action == 'activate' ? '1' : '0' ), $current_components );
					break;
			}

			cb_core_install( $cb->active_components );

			bp_update_option( 'cb_active_components', $cb->active_components );

		}

		$current_action = 'all';
		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'active', 'inactive' ) ) ) {
			$current_action = $_GET['action'];
		}

		// Where are we redirecting to?
		$base_url = bp_get_admin_url(
			add_query_arg(
				array(
					'page'    => 'bp-settings',
					'action'  => $current_action,
					'updated' => 'true',
					'added'   => 'true',
				),
				'admin.php'
			)
		);

		// Redirect.
		wp_redirect( $base_url );
		die();

	}
}
add_action( 'bp_admin_init', 'cb_core_admin_component_activation_handler' );



if ( ! function_exists( 'cb_core_admin_component_settings_handler' ) ) {
	function cb_core_admin_component_settings_handler() {

		if ( ! isset( $_POST['cb-admin-component-submit'] ) ) {
			return;
		}

		$action = ( isset( $_POST['cb_action'] ) && '' !== $_POST['cb_action'] ) ? $_POST['cb_action'] : $_POST['action'];
		if ( '' === $action )
			return;
		// Settings form submitted, now save the settings. First, set active components.
		if ( isset( $_POST['cb_components'] ) ) {

			// Load up BuddyPress.
			$bp = buddypress();
			$cb = Confetti_Bits();


			// Save settings and upgrade schema.
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$current_components = $cb->active_components;
			$submitted = stripslashes_deep( $_POST['cb_components'] );
			$required = cb_core_admin_get_components('required');


			if ( empty( $submitted ) ) {
				foreach ( $required as $key => $req ) {
					$submitted[$key] = '1';
				}
			} else {
				foreach ( $required as $key => $req ) {
					$submitted[$key] = '1';
				}
			}

			$cb->active_components = $submitted;

			cb_core_install( $cb->active_components );
			bp_update_option( 'cb_active_components', $cb->active_components );

		}

		$current_action = 'all';
		if ( isset( $_GET['cb_action'] ) && in_array( $_GET['cb_action'], array( 'active', 'inactive' ) ) ) {
			$current_action = $_GET['cb_action'];
		}

		$base_url = bp_get_admin_url(
			add_query_arg(
				array(
					'page'    => 'bp-settings',
					'action'  => $current_action,
					'updated' => 'true',
					'added'   => 'true',
				),
				'admin.php'
			)
		);

		// Redirect.
		wp_safe_redirect( $base_url );
		die();

	}
}
add_action( 'bp_admin_init', 'cb_core_admin_component_settings_handler' );

if ( ! function_exists( 'cb_core_admin_get_active_components_from_submitted_settings' ) ){
	function cb_core_admin_get_active_components_from_submitted_settings( $submitted, $action = 'all' ) {
		$current_action = $action;

		if ( isset( $_GET['cb_action'] ) && in_array( $_GET['cb_action'], array( 'active', 'inactive' ) ) ) {
			$current_action = $_GET['cb_action'];
		}

		$current_components = Confetti_Bits()->active_components;

		switch ( $current_action ) {
			case 'inactive' :
				$components = array_merge( $submitted, $current_components );
				break;

			case 'all' :
			case 'active' :
			default :
				$components = $submitted;
				break;
		}

		return $components;
	}

}