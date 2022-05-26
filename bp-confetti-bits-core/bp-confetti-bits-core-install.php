<?php 
function cb_core_prepare_install() {
	
	global $wpdb;
 
    $raw_db_version = (int) bp_get_db_version_raw();
    $bp_prefix      = bp_core_get_table_prefix();
 
    // 2.3.0: Change index lengths to account for utf8mb4.
    if ( $raw_db_version < 9695 ) {
        // Map table_name => columns.
        $tables = array(
            $bp_prefix . 'confetti_bits_transactions'       => array( 'meta_key' ),
        );
 
        foreach ( $tables as $table_name => $indexes ) {
            foreach ( $indexes as $index ) {
                if ( $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", bp_esc_like( $table_name ) ) ) ) {
                    $wpdb->query( "ALTER TABLE {$table_name} DROP INDEX {$index}" );
                }
            }
        }
    }
}


function cb_core_install_transactions() {
	
	$sql = array();
	
	$bp_prefix      = bp_core_get_table_prefix();
	$charset_collate = $GLOBALS['wpdb']->get_charset_collate();



	$sql[] = "CREATE TABLE {$bp_prefix}confetti_bits_transactions (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				item_id bigint(20) NOT NULL,
				secondary_item_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				sender_id bigint(20) NOT NULL,
				sender_name varchar(75) NOT NULL,
				recipient_id bigint(20) NOT NULL,
				recipient_name varchar(75) NOT NULL,
				identifier varchar(75) NOT NULL,
				date_sent datetime NOT NULL,
				log_entry longtext NOT NULL,
				component_name varchar(75) NOT NULL,
				component_action varchar(75) NOT NULL,
				amount bigint(20) NOT NULL,
				KEY item_id (item_id),
				KEY secondary_item_id (secondary_item_id),
				KEY user_id (user_id),
				KEY sender_id (sender_id),
				KEY sender_name (sender_name),
				KEY recipient_id (recipient_id),
				KEY recipient_name (recipient_name),
				KEY identifier (identifier),
				KEY date_sent (date_sent),
				KEY component_name (component_name),
				KEY component_action (component_action),
				KEY amount (amount)
			) {$charset_collate};";
	
	
	$sql[] = "CREATE TABLE {$bp_prefix}confetti_bits_transactions_recipients (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				item_id bigint(20) NOT NULL,
				secondary_item_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				sender_id bigint(20) NOT NULL,
				sender_name varchar(75) NOT NULL,
				recipient_id bigint(20) NOT NULL,
				recipient_name varchar(75) NOT NULL,
				identifier varchar(75) NOT NULL,
				date_sent datetime NOT NULL,
				log_entry longtext NOT NULL,
				component_name varchar(75) NOT NULL,
				component_action varchar(75) NOT NULL,
				amount bigint(20) NOT NULL,
				KEY item_id (item_id),
				KEY secondary_item_id (secondary_item_id),
				KEY user_id (user_id),
				KEY sender_id (sender_id),
				KEY sender_name (sender_name),
				KEY recipient_id (recipient_id),
				KEY recipient_name (recipient_name),
				KEY identifier (identifier),
				KEY date_sent (date_sent),
				KEY component_name (component_name),
				KEY component_action (component_action),
				KEY amount (amount)
			) {$charset_collate};";

	dbDelta( $sql );
	
	
}

function cb_core_install( $active_components = false ) {
	
	cb_core_prepare_install();
	
	if ( empty( $active_components ) ) {
 
        $active_components = apply_filters( 'cb_active_components', bp_get_option( 'cb_active_components' ) );
		
    }
	
	
	if ( ! empty ( $active_components['transactions'] ) ) {
		cb_core_install_transactions();		
	}
	
	do_action('cb_core_install');
	
	// Needs to flush all cache when component activate/deactivate.
	wp_cache_flush();

	// Reset the permalink to fix the 404 on some pages.
	flush_rewrite_rules();

}