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


if ( ! class_exists( 'BP_Core_Notification_Abstract' ) ) {
    return;
}

/**
 * Extends the component class to set up the Notifications component.
 */
class Confetti_Bits_Notifications_Component extends BP_Core_Notification_Abstract {

	
	/**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;
 
    /**
     * Get the instance of this class.
     *
     * @return null|BP_Custom_Notification|Controller|object
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
	
	
	/**
	 * Start the notifications component creation process.
	 *
	 * @since BuddyPress 1.9.0
	 */
	public function __construct() {
		$this::start();
	}
	
	public function load() {
		
		/**
		 * Register Notification Group.
		 *
		 * @param string $group_key         Group key.
		 * @param string $group_label       Group label.
		 * @param string $group_admin_label Group admin label.
		 * @param int    $priority          Priority of the group.
		 */
		$this->register_notification_group(
			'confetti_bits',
<<<<<<< HEAD
			esc_html__( 'Confetti Bits Notifications Frontend', 'buddyboss' ), // For the frontend.
=======
			esc_html__( 'Confetti Bits Notifications', 'buddyboss' ), // For the frontend.
>>>>>>> d35ff97 (Whoopsies)
			esc_html__( 'Confetti Bits Notifications Admin', 'buddyboss' ) // For the backend.
		);

		$this->register_confetti_bits_notifications();

	}
	
	
	public function register_confetti_bits_notifications() {
		
		/**
		 * Register Notification Type.
		 *
		 * @param string $notification_type        Notification Type key.
		 * @param string $notification_label       Notification label.
		 * @param string $notification_admin_label Notification admin label.
		 * @param string $notification_group       Notification group.
		 * @param bool   $default                  Default status for enabled/disabled.
		 */
		$this->register_notification_type(
			'leadership_confetti_bits',
			esc_html__( 'Leadership Confetti Bits', 'buddyboss' ),
			esc_html__( 'Leadership Confetti Bits', 'buddyboss' ),
			'confetti_bits'
		);

		/**
		 * Add email schema.
		 *
		 * @param string $email_type        Type of email being sent.
		 * @param array  $args              Email arguments.
		 * @param string $notification_type Notification Type key.
		 */
		$this->register_email_type(
			'confetti-bits-email',
			array(
				'email_title'         => __( 'Confetti Bits!', 'buddyboss' ),
				'email_content'       => __( 'Someone just sent you Confetti Bits!', 'buddyboss' ),
				'email_plain_content' => __( 'Someone just sent you Confetti Bits!', 'buddyboss' ),
				'situation_label'     => __( 'Someone sent you bits!', 'buddyboss' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when someone sends you Confetti Bits.', 'buddyboss' ),
			),
			'leadership_confetti_bits'
		);

		/**
		 * Register notification.
		 *
		 * @param string $component         Component name.
		 * @param string $component_action  Component action.
		 * @param string $notification_type Notification Type key.
		 * @param string $icon_class        Notification Small Icon.
		 */
		$this->register_notification(
			'confetti_bits',
			'confetti_bits_send_bits',
			'leadership_confetti_bits'
		);

		/**
		 * Register Notification Filter.
		 *
		 * @param string $notification_label    Notification label.
		 * @param array  $notification_types    Notification types.
		 * @param int    $notification_position Notification position.
		 */
		$this->register_notification_filter(
			__( 'Confetti Bits Notifications', 'buddyboss' ),
			array( 'leadership_confetti_bits' ),
			5
		);	
			
	}
	
	
	public function format_notification( $content, $item_id, $secondary_item_id, $action_item_count, $component_action_name, $component_name, $notification_id, $screen ) {

		if ( 'confetti_bits' === $component_name && 'confetti_bits_send_bits' === $component_action_name ) {
 
            $text = esc_html__( bp_core_get_user_displayname( $secondary_item_id ) . ' sent you bits!', 'buddyboss' );
            $link = get_permalink( $item_id );
 
            return array(
                'title' => "New Confetti Bits!", // (optional) only for push notification & if not provided no title will be used.
                'text' => $text,
                'link' => $link,
            );
        }
			
		return $content;
	}
	
}