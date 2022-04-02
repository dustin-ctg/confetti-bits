<?php
/**
 * BuddyBoss Activity Feeds Loader.
 *
 * An activity feed component, for users, groups, and site tracking.
 *
 * @package BuddyBoss\Activity
 * @since BuddyPress 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Set up the bp-confetti-bits-notifications component.
 *
 * @since BuddyPress 1.6.0
 */

function confetti_bits_setup_notifications() {
	Confetti_Bits()->notifications = new Confetti_Bits_Notifications_Component();
}
add_action( 'bp_setup_components', 'confetti_bits_setup_notifications', 8 );

