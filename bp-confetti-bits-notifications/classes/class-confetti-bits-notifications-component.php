<?php
/**
 * Confetti Bits Notifications Loader.
 *
 * Establishes the Confetti Bits Notifications component.
 */

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BP_Core_Notification_Abstract' ) ) {
	return;
}

class Confetti_Bits_Notifications_Component extends BP_Core_Notification_Abstract {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->start();
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

			esc_html__( 'Confetti Bits Notifications', 'confetti-bits' ), 
			esc_html__( 'Confetti Bits Notifications Admin', 'confetti-bits' ),
		);

		$this->register_confetti_bits_send_notifications();

		$this->register_confetti_bits_import_notifications();
		
		$this->register_confetti_bits_request_notifications();
		
		$this->register_confetti_bits_activity_notifications();

		/**
		 * Register Notification Filter.
		 *
		 * @param string $notification_label    Notification label.
		 * @param array  $notification_types    Notification types.
		 * @param int    $notification_position Notification position.
		 */

	}

	public function register_confetti_bits_send_notifications() {

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
			'cb_transactions_send_bits',
			esc_html__( 'Someone sends you Confetti Bits', 'confetti-bits' ),
			esc_html__( 'Someone sends you Confetti Bits', 'confetti-bits' ),
			'confetti_bits',
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
			'cb_send_bits',
			'cb_transactions_send_bits'
		);
		
		/**
		 * Add email schema.
		 *
		 * @param string $email_type        Type of email being sent.
		 * @param array  $args              Email arguments.
		 * @param string $notification_type Notification Type key.
		 */
		$this->register_email_type(
			'cb-send-bits-email',
			array(
				'email_title'         => __( 'Someone Sent Confetti Bits!', 'confetti-bits' ),
				'email_content'       => __( 'Someone just sent you Confetti Bits!', 'confetti-bits' ),
				'email_plain_content' => __( 'Someone just sent you Confetti Bits!', 'confetti-bits' ),
				'situation_label'     => __( 'Someone sends Confetti Bits!', 'confetti-bits' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when someone sends you Confetti Bits.', 'confetti-bits' ),
			),
			'cb_transactions_send_bits'
		);

		$this->register_notification_filter(
			__( 'Confetti Bits Notifications', 'confetti-bitts' ),
			array( 'cb_transactions_send_bits' ),
			5
		);

		
//		add_filter( 'cb_transactions_send_bits', array( $this, 'format_notification' ), 10, 7 );	

	}
	
		public function register_confetti_bits_activity_notifications() {

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
			'cb_transactions_activity_bits',
			esc_html__( 'You get Confetti Bits for posting', 'confetti-bits' ),
			esc_html__( 'You get Confetti Bits for posting', 'confetti-bits' ),
			'confetti_bits',
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
			'cb_activity_bits',
			'cb_transactions_activity_bits'
		);
		
		/**
		 * Add email schema.
		 *
		 * @param string $email_type        Type of email being sent.
		 * @param array  $args              Email arguments.
		 * @param string $notification_type Notification Type key.
		 */
		$this->register_email_type(
			'cb-send-bits-email',
			array(
				'email_title'         => __( 'Nice!', 'confetti-bits' ),
				'email_content'       => __( 'You just got Confetti Bits for posting on TeamCTG!', 'confetti-bits' ),
				'email_plain_content' => __( 'You just got Confetti Bits for posting on TeamCTG!', 'confetti-bits' ),
				'situation_label'     => __( 'You get Confetti Bits for posting', 'confetti-bits' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when you receive Confetti Bits for posting.', 'confetti-bits' ),
			),
			'cb_transactions_activity_bits'
		);

		$this->register_notification_filter(
			__( 'Confetti Bits Notifications', 'confetti-bitts' ),
			array( 'cb_transactions_activity_bits' ),
			5
		);

		
//		add_filter( 'cb_transactions_send_bits', array( $this, 'format_notification' ), 10, 7 );	

	}

	public function register_confetti_bits_import_notifications() {
		
		$this->register_notification_type(
			'cb_transactions_import_bits',
			esc_html__( 'Someone performs a Confetti Bits import', 'confetti-bits' ),
			esc_html__( 'Someone performs a Confetti Bits import', 'confetti-bits' ),
			'confetti_bits'
		);

		$this->register_notification(
			'confetti_bits',
			'cb_import_bits',
			'cb_transactions_import_bits'
		);
		
		$this->register_email_type(
			'cb-import-bits-email',
			array(
				'email_title'         => __( 'Confetti Bits Imported', 'confetti-bits' ),
				'email_content'       => __( "Confetti Bits were just imported!", 'confetti-bits' ),
				'email_plain_content' => __( "Confetti Bits were just imported!", 'confetti-bits' ),
				'situation_label'     => __( "Confetti Bits are imported.", 'buddyboss' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when Confetti Bits are imported.', 'confetti-bits' ),
			),
			'cb_transactions_import_bits'
		);
		$this->register_notification_filter(
			__( 'Confetti Bits Notifications', 'confetti-bits' ),
			array( 'cb_transactions_import_bits' ),
			5
		);
//		add_filter( 'cb_transactions_import_bits', array( $this, 'format_notification' ), 10, 7 );

	}
	
	
	public function register_confetti_bits_request_notifications() {

		$this->register_notification_type(
			'cb_transactions_bits_request',
			esc_html__( 'Someone sends in a Confetti Bits request', 'confetti-bits' ),
			esc_html__( 'Someone sends in a Confetti Bits request', 'confetti-bits' ),
			'confetti_bits'
		);

		

		$this->register_notification(
			'confetti_bits',
			'cb_bits_request',
			'cb_transactions_bits_request'
		);
		
		$this->register_email_type(
			'cb-bits-request-email',
			array(
				'email_title'         => __( 'New Confetti Bits Request from {{request_sender.name}}', 'confetti-bits' ),
				'email_content'       => __( "A new Confetti Bits Request came in! {{request_sender.name}} requested {{request_sender.item}}.", 'confetti-bits' ),
				'email_plain_content' => __( "A new Confetti Bits Request came in! {{request_sender.name}} requested {{request_sender.item}}.", 'confetti-bits' ),
				'situation_label'     => __( "A new Confetti Bits Request comes in", 'confetti-bits' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when Confetti Bits requests are sent.', 'confetti-bits' ),
			),
			'cb_transactions_bits_request'
		);
		
		$this->register_notification_filter(
			__( 'Confetti Bits Notifications', 'confetti-bits' ),
			array( 'cb_transactions_bits_request' ),
			5
		);
		
//		add_filter( 'cb_transactions_request_bits', array( $this, 'format_notification' ), 10, 7 );

	}

	public function format_notification( $content, $item_id, $secondary_item_id, $action_item_count, $component_action_name, $component_name, $notification_id, $screen ) {

		$text = '';
		$link = bp_loggedin_user_domain() . cb_get_transactions_slug();
		
		if ( 'confetti_bits' === $component_name && 'cb_send_bits' === $component_action_name ) {

			$text = esc_html__( bp_core_get_user_displayname( $item_id ) . ' just sent you bits!', 'confetti-bits' );

			$content = array(
				'title' => "Someone just sent Confetti Bits!", 
				'text' => $text,
				'link' => $link,
			);
		}
		
		if ( 'confetti_bits' === $component_name && 'cb_activity_bits' === $component_action_name ) {

			if ( $item_id === 1 ) {
			
				$text = esc_html__( 'You just got ' . $item_id . ' Confetti Bit for posting!', 'confetti-bits' );
				
			} else {
				
				$text = esc_html__( 'You just got ' . $item_id . ' Confetti Bits for posting!', 'confetti-bits' );
				
			}

			$content = array(
				'title' => "You just got Confetti Bits!", 
				'text' => $text,
				'link' => $link,
			);
		}

		if ( 'confetti_bits' === $component_name && 'cb_import_bits' === $component_action_name ) {

			$text = esc_html__( bp_core_get_user_displayname( $item_id ) . ' just imported bits!', 'confetti-bits' );

			$content = array(
				'title' => "Someone just imported Confetti Bits!", 
				'text' => $text,
				'link' => $link,
			);
		}

		if ( 'confetti_bits' === $component_name && 'cb_bits_request' === $component_action_name ) {

			$text = esc_html__( bp_core_get_user_displayname( $item_id ) . ' just sent in a new Confetti Bits Request!', 'confetti-bits' );

			$content = array(
				'title' => "New Confetti Bits Request!", 
				'text' => $text,
				'link' => $link,
			);
		}


		return $content;

	}

}
