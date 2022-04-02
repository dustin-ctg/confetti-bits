<?php
/**
 * BuddyBoss Activity Notifications.
 *
 * @package BuddyBoss\Activity
 * @since BuddyPress 1.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



/**
 * Format notifications related to confetti bits.
 *
 * @since BuddyPress 1.5.0
 *
 * @param string $action            The type of activity item. Just 'new_confetti_bits' for now.
 * @param int    $item_id           The item ID. We'll just auto-increment this in the database.
 * @param int    $secondary_item_id In the case of confetti_bits, this is the sender's ID.
 * @param string $format            'string' to get a BuddyBar-compatible notification, 'array' otherwise.
 * @param int    $id                Optional. The notification ID.
 * @return string $return Formatted @mention notification.
 */
function confetti_bits_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string') {

	switch ( $action ) {
		case 'new_confetti_bits':
			$item_id 		= $id;
			$action_filter	= 'confetti_bits';
			$text			= sprintf( __( '%s got Confetti Bits', 'buddyboss' ), bp_get_loggedin_user_username() );
			$link			= 'https://teamctg.com/confetti-bits-test';
			$title			= sprintf( __( '%s got Confetti Bits', 'buddyboss' ), bp_get_loggedin_user_username() );

			/**
			 * Filters the confetti_bits notification permalink.
			 * Don't know if we need this in particular. 
			 * We can test this to see if it works and echo it into a page.
			 * 
			 * The two possible hooks are bp_activity_new_at_mention_permalink
			 * or activity_get_notification_permalink.
			 *
			 * @since BuddyBoss 1.2.5
			 *
			 * @param string $link          HTML anchor tag for the interaction.
			 * @param int    $item_id            The permalink for the interaction.
			 * @param int    $secondary_item_id     How many items being notified about.
			 * @param int    $total_items     ID of the activity item being formatted.
			 */

			if ( (int) $total_items > 1 ) {
				$text   = sprintf( __( 'You have %1$d new confetti bits', 'buddyboss' ), (int) $total_items );
				$amount = 'multiple';
			} else {
				$text = sprintf( __( '%1$s sent you confetti bits', 'buddyboss' ), $user_fullname );
			}
			break;
	}

	if ( 'string' == $format ) {

		/**
		 * Filters the activity notification for the string format.
		 *
		 * This is a variable filter that is dependent on how many items
		 * need notified about. The two possible hooks are bp_activity_single_at_mentions_notification
		 * or bp_activity_multiple_at_mentions_notification.
		 *
		 * @since BuddyPress 1.5.0
		 * @since BuddyPress 2.6.0 use the $action_filter as a new dynamic portion of the filter name.
		 *
		 * @param string $string          HTML anchor tag for the interaction.
		 * @param string $link            The permalink for the interaction.
		 * @param int    $total_items     How many items being notified about.
		 * @param int    $activity_id     ID of the activity item being formatted.
		 * @param int    $user_id         ID of the user who inited the interaction.
		 */
		$return = apply_filters( 'confetti_bits_' . $amount . '_' . $action_filter . '_notification', '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>', $link, (int) $total_items, $activity_id, $user_id );
	} else {

		/**
		 * Filters the activity notification for any non-string format.
		 *
		 * This is a variable filter that is dependent on how many items need notified about.
		 * The two possible hooks are bp_activity_single_at_mentions_notification
		 * or bp_activity_multiple_at_mentions_notification.
		 *
		 * @since BuddyPress 1.5.0
		 * @since BuddyPress 2.6.0 use the $action_filter as a new dynamic portion of the filter name.
		 *
		 * @param array  $array           Array holding the content and permalink for the interaction notification.
		 * @param string $link            The permalink for the interaction.
		 * @param int    $total_items     How many items being notified about.
		 * @param int    $activity_id     ID of the activity item being formatted.
		 * @param int    $user_id         ID of the user who inited the interaction.
		 */
		$return = apply_filters(
			'confetti_bits_' . $amount . '_' . $action_filter . '_notification',
			array(
				'text' => $text,
				'link' => $link,
			),
			$link,
			(int) $total_items,
			$activity_id,
			$user_id
		);
	}
	add_filter( 'bp_notifications_get_notifications_for_user', 'confetti_bits_format_notifications', 10, 5 );

	/**
	 * Fires right before returning the formatted activity notifications.
	 *
	 * @since BuddyPress 1.2.0
	 *
	 * @param string $action            The type of activity item.
	 * @param int    $item_id           The activity ID.
	 * @param int    $secondary_item_id The user ID who inited the interaction.
	 * @param int    $total_items       Total amount of items to format.
	 */
	do_action( 'confetti_bits_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Notify a member when their nicename is mentioned in an activity feed item.
 *
 * Hooked to the 'bp_activity_sent_mention_email' action, we piggy back off the
 * existing email code for now, since it does the heavy lifting for us. In the
 * future when we separate emails from Notifications, this will need its own
 * 'bp_activity_at_name_send_emails' equivalent helper function.
 *
 * @since BuddyPress 1.9.0
 *
 * @param object $activity           Activity object.
 * @param string $subject (not used) Notification subject.
 * @param string $message (not used) Notification message.
 * @param string $content (not used) Notification content.
 * @param int    $receiver_user_id   ID of user receiving notification.
 */
function confetti_bits_add_notification( $user_id, $sender_id ) {
	bp_notifications_add_notification(
		array(
			'user_id'           => $user_id,
			'item_id'           => $wpdb->insert_id,
			'secondary_item_id' => $sender_id,
			'component_name'    => 'confetti_bits',
			'component_action'  => 'new_confetti_bits',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		)
	);
}
add_action( 'send_confetti_bits', 'confetti_bits_add_notification', 10, 5 );



/**
 * Add activity notifications settings to the notifications settings page.
 *
 * @since BuddyPress 1.2.0
 */
/*
function bp_activity_screen_notification_settings() {
	if ( bp_activity_do_mentions() ) {
		if ( ! $mention = bp_get_user_meta( bp_displayed_user_id(), 'notification_activity_new_mention', true ) ) {
			$mention = 'yes';
		}
	}

	if ( ! $reply = bp_get_user_meta( bp_displayed_user_id(), 'notification_activity_new_reply', true ) ) {
		$reply = 'yes';
	}

	?>

	<table class="notification-settings" id="activity-notification-settings">
		<thead>
			<tr>
				<th class="icon">&nbsp;</th>
				<th class="title"><?php _e( 'Activity Feed', 'buddyboss' ); ?></th>
				<th class="yes"><?php _e( 'Yes', 'buddyboss' ); ?></th>
				<th class="no"><?php _e( 'No', 'buddyboss' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if ( bp_activity_do_mentions() ) : ?>
				<?php $current_user = wp_get_current_user(); ?>
				<tr id="activity-notification-settings-mentions">
					<td>&nbsp;</td>
					<td><?php printf( __( 'A member mentions you in an update using "@%s"', 'buddyboss' ), bp_activity_get_user_mentionname( $current_user->ID ) ); ?></td>
					<td class="yes">
						<div class="bp-radio-wrap">
							<input type="radio" name="notifications[notification_activity_new_mention]" id="notification-activity-new-mention-yes" class="bs-styled-radio" value="yes" <?php checked( $mention, 'yes', true ); ?> />
							<label for="notification-activity-new-mention-yes"><span class="bp-screen-reader-text"><?php  _e( 'Yes, send email', 'buddyboss' ); ?></span></label>
						</div>
					</td>
					<td class="no">
						<div class="bp-radio-wrap">
							<input type="radio" name="notifications[notification_activity_new_mention]" id="notification-activity-new-mention-no" class="bs-styled-radio" value="no" <?php checked( $mention, 'no', true ); ?> />
							<label for="notification-activity-new-mention-no"><span class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'buddyboss' ); ?></span></label>
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<tr id="activity-notification-settings-replies">
				<td>&nbsp;</td>
				<td><?php _e( "A member replies to an update or comment you've posted", 'buddyboss' ); ?></td>
				<td class="yes">
					<div class="bp-radio-wrap">
						<input type="radio" name="notifications[notification_activity_new_reply]" id="notification-activity-new-reply-yes" class="bs-styled-radio" value="yes" <?php checked( $reply, 'yes', true ); ?> />
						<label for="notification-activity-new-reply-yes"><span class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'buddyboss' ); ?></span></label>
					</div>
				</td>
				<td class="no">
					<div class="bp-radio-wrap">
						<input type="radio" name="notifications[notification_activity_new_reply]" id="notification-activity-new-reply-no" class="bs-styled-radio" value="no" <?php checked( $reply, 'no', true ); ?> />
						<label for="notification-activity-new-reply-no"><span class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'buddyboss' ); ?></span></label>
					</div>
				</td>
			</tr>

			<?php

			/**
			 * Fires inside the closing </tbody> tag for activity screen notification settings.
			 *
			 * @since BuddyPress 1.2.0
			 */
/*/
			do_action( 'bp_activity_screen_notification_settings' )
			?>
		</tbody>
	</table>

	<?php
}
add_action( 'bp_notification_settings', 'bp_activity_screen_notification_settings', 1 );
