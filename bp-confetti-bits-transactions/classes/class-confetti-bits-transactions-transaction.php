<?php
/**
 * Confetti Bits Transaction Loader.
 * A component that allows leaders to send bits to users and for users to send bits to each other.
 * @package Confetti_Bits 
 * @since Confetti Bits 2.0.0  */

defined( 'ABSPATH' ) || exit;

class Confetti_Bits_Transactions_Transaction { 
	public static $last_inserted_id; public $id; public $item_id;
	public $secondary_item_id;
	public $user_id;
	public $sender_id;
	public $sender_name;
	public $recipient_id;
	public $recipients;
	public $recipient_name;
	public $identifier;
	public $date_sent;
	public $log_entry;
	public $component_name;
	public $component_action;
	public $amount;
	public $total_count;
	public $total_pages;
	public $error;
	public $error_type = 'bool';
	public static $columns = array(
		'id',
		'item_id',
		'secondary_item_id',
		'user_id',
		'sender_id',
		'sender_name',
		'recipient_id',
		'recipient_name',
		'identifier',
		'date_sent',
		'log_entry',
		'component_name',
		'component_action',
		'amount',
	);
	public function __construct( $id = 0 ) {
		$this->errors = new WP_Error();
		if ( ! empty ( $id ) ) {
			$this->id = (int) $id;
			$this->populate( $id );
		}
	}
	public function send_bits() {
		$retval = false;
		do_action_ref_array( 'cb_transactions_before_send', array( &$this ) );
		$data = array (
			'item_id' => $this->item_id,
			'secondary_item_id' => $this->secondary_item_id,
			'user_id' => $this->user_id,
			'sender_id' => $this->sender_id,
			'sender_name' => $this->sender_name,
			'recipient_id' => $this->recipient_id,
			'recipient_name' => $this->recipient_name,
			'identifier' => $this->identifier,
			'date_sent' => $this->date_sent,
			'log_entry' => $this->log_entry,
			'component_name' => $this->component_name,
			'component_action' => $this->component_action,
			'amount' => $this->amount,
		);
		$data_format = array( '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', );
		$result = self::_insert( $data, $data_format );
		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			global $wpdb;

			if ( empty( $this->id ) ) {
				$this->id = $wpdb->insert_id;
			}

			do_action( 'cb_transactions_after_send', $data );


			$retval = $this->id;
		}
		return $retval;
	}

	public function populate( $id ) {
		$transaction = self::get(
			array(
				'include' 	=> array( $id ),
			)
		);
		global $wpdb;
		$bp = buddypress();
		$cb = Confetti_Bits();
		$fetched_transaction = ( ! empty( $transaction['log_entry'] ) ? current( $transaction['log_entry'] ) : array() );
		if ( ! empty( $fetched_transaction ) && ! is_wp_error( $fetched_transaction ) ) {
			$this->item_id           = (int) $fetched_transaction->item_id;
			$this->secondary_item_id = (int) $fetched_transaction->secondary_item_id;
			$this->user_id           = (int) $fetched_transaction->user_id;
			$this->sender_id		 = (int) $fetched_transaction->sender_id;
			$this->sender_name		 = $fetched_transaction->sender_name;
			$this->recipient_id		 = (int) $fetched_transaction->recipient_id;
			$this->recipient_name	 = $fetched_transaction->recipient_name;
			$this->identifier		 = (int) $fetched_transaction->identifier;
			$this->date_sent		 = $fetched_transaction->date_sent;
			$this->log_entry		 = $fetched_transaction->log_entry;
			$this->component_name    = $fetched_transaction->component_name;
			$this->component_action  = $fetched_transaction->component_action;
			$this->amount			 = (int) $fetched_transaction->amount;
		}
	}
	protected static function _insert( $data = array(), $data_format = array() ) {
		global $wpdb;
		return $wpdb->insert( Confetti_Bits()->transactions->table_name, $data, $data_format );
	}
	public static function get( $args = array() ) {
		global $wpdb;
		$bp = buddypress();
		$cb = Confetti_Bits();
		$defaults = array(
			'orderby'           => 'date_sent',
			'order'             => 'DESC',
			'per_page'          => 20,
			'page'              => 1,
			'user_id'           => 0,
			'date_query'        => false,
			'transactions'		=> array(),
			'include'           => false,
			'exclude'           => false,
			'fields'            => 'all',
			'group_by'          => '',
			'log_entry'         => '',
			'count_total'       => false,
		);
		$r = bp_parse_args( $args, $defaults, 'confetti_bits_transactions_transaction_get' );
		$sql = array(
			'select'     => 'SELECT DISTINCT m.id',
			'from'       => "{$cb->transactions->table_name} m",
			'where'      => '',
			'orderby'    => '',
			'pagination' => '',
			'date_query' => '',
		);
		if ( 'sender_ids' === $r['fields'] ) {
			$sql['select'] = 'SELECT DISTINCT m.sender_id';
		}
		if ( 'recipient_ids' === $r['fields'] ) {
			$sql['select'] = 'SELECT DISTINCT m.recipient_id';
		}
		$where_conditions = array();
		$date_query_sql = self::get_date_query_sql( $r['date_query'] );
		if ( ! empty( $date_query_sql ) ) {
			$where_conditions['date'] = $date_query_sql;
		}
		if ( ! empty( $r['user_id'] ) ) {
			$where_conditions['user'] = $wpdb->prepare( 'm.sender_id = %d', $r['user_id'] );
		}
		if ( ! empty( $r['include'] ) ) {
			$include                     = implode( ',', wp_parse_id_list( $r['include'] ) );
			$where_conditions['include'] = "m.id IN ({$include})";
		}
		if ( ! empty( $r['exclude'] ) ) {
			$exclude                     = implode( ',', wp_parse_id_list( $r['exclude'] ) );
			$where_conditions['exclude'] = "m.id NOT IN ({$exclude})";
		}
		if ( ! empty( $r['log_entry'] ) ) {
			$where_conditions['log_entry'] = $wpdb->prepare( 'm.log_entry != %s', $r['log_entry'] );
		}
		$order   = $r['order'];
		$orderby = $r['orderby'];

		$order = bp_esc_sql_order( $order );

		$orderby = apply_filters( 
			'confetti_bits_transactions_transaction_get_orderby', 
			self::convert_orderby_to_order_by_term( $orderby ), $orderby );

		$sql['orderby'] = "ORDER BY {$orderby} {$order}";

		if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && - 1 !== $r['per_page'] ) {
			$sql['pagination'] = $wpdb->prepare( 
				'LIMIT %d, %d', 
				intval( ( $r['page'] - 1 ) * $r['per_page'] ), 
				intval( $r['per_page'] ) 
			);
		}
		$where_conditions = apply_filters( 
			'confetti_bits_transactions_transaction_get_where_conditions', 
			$where_conditions, 
			$r 
		);

		$where = '';

		if ( ! empty( $where_conditions ) ) {
			$sql['where'] = implode( ' AND ', $where_conditions );

			$where        = "WHERE {$sql['where']}";
		}

		$sql['from'] = apply_filters( 
			'confetti_bits_transactions_transaction_get_join_sql', 
			$sql['from'], $r 
		);

		$paged_transactions_sql = "{$sql['select']} FROM {$sql['from']} {$where} {$sql['orderby']} {$sql['pagination']}";
		$paged_transactions_sql = apply_filters( 
			'confetti_bits_transactions_transaction_get_paged_sql', 
			$paged_transactions_sql, 
			$sql, $r 
		);
		$cached = bp_core_get_incremented_cache( $paged_transactions_sql, 'confetti_bits_transactions' );

		if ( false === $cached ) {
			$paged_transaction_ids = $wpdb->get_col( $paged_transactions_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			bp_core_set_incremented_cache( $paged_transactions_sql, 'confetti_bits_transactions', $paged_transaction_ids );
		} else {
			$paged_transaction_ids = $cached;
		}

		$paged_transactions = array();

		if ( 'ids' === $r['fields'] || 'sender_ids' === $r['fields'] || 'recipients' === $r['fields'] ) {
			// We only want the IDs.
			$paged_transactions = array_map( 'intval', $paged_transaction_ids );
		} elseif ( ! empty( $paged_transaction_ids ) ) {
			$transaction_ids_sql             = implode( ',', array_map( 'intval', $paged_transaction_ids ) );
			$transaction_data_objects_sql    = "SELECT m.* FROM {$cb->transactions->table_name} m WHERE m.id IN ({$transaction_ids_sql})";
			$transaction_data_objects_cached = bp_core_get_incremented_cache( $transaction_data_objects_sql, 'confetti_bits_transactions' );

			if ( false === $transaction_data_objects_cached ) {
				$transaction_data_objects = $wpdb->get_results( $transaction_data_objects_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				bp_core_set_incremented_cache( $transaction_data_objects_sql, 'confetti_bits_transactions', $transaction_data_objects );
			} else {
				$transaction_data_objects = $transaction_data_objects_cached;
			}

			foreach ( (array) $transaction_data_objects as $tdata ) {
				$transaction_data_objects[ $tdata->id ] = $tdata;
			}
			foreach ( $paged_transaction_ids as $paged_transaction_id ) {
				$paged_transactions[] = $transaction_data_objects[ $paged_transaction_id ];
			}
		}

		$retval = array(
			'transactions' => $paged_transactions,
			'total'    => 0,
		);

		if ( ! empty( $r['count_total'] ) ) {

			$total_transactions_sql = "SELECT COUNT(DISTINCT m.id) FROM {$sql['from']} $where";

			$total_transactions_sql = apply_filters( 'confetti_bits_transactions_transaction_get_total_sql', $total_transactions_sql, $sql, $r );

			$total_transactions_sql_cached = bp_core_get_incremented_cache( $total_transactions_sql, 'confetti_bits_transactions' );

			if ( false === $total_transactions_sql_cached ) {
				$total_transactions  = (int) $wpdb->get_var( $total_transactions_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				bp_core_set_incremented_cache( $total_transactions_sql, 'confetti_bits_transactions', $total_transactions );
			} else {
				$total_transactions = $total_transactions_sql_cached;
			}

			$retval['total'] = $total_transactions;
		}

		return $retval;
	}

	public function get_recipients( $item_id = 0 ) {

		if ( empty( $item_id ) ) {
			$item_id = $this->item_id;
		}

		$item_id = (int) $item_id;

		$recipients = wp_cache_get( 'transaction_recipients_' . $item_id, 'confetti_bits_transactions_recipients' );

		if ( false === $recipients ) {

			$recipients = array();

			$results = self::get(
				array(
					'per_page'		=> - 1,
					'transactions' 	=> array( $item_id ),
				)
			);

			if ( ! empty( $results['recipients'] ) ) {
				foreach ( (array) $results['recipients'] as $recipient ) {
					$recipients[ $recipient->user_id ] = $recipient;
				}

				wp_cache_set( 'transaction_recipients_' . $item_id, $recipients, 'cb_transactions' );
			}
		}

		// Cast all items from the messages DB table as integers.
		foreach ( (array) $recipients as $key => $data ) {
			$recipients[ $key ] = (object) array_map( 'intval', (array) $data );
		}
	}

	public static function get_date_query_sql( $date_query = array() ) {
		$sql = '';

		if ( ! empty( $date_query ) && is_array( $date_query ) ) {
			$date_query = new BP_Date_Query( $date_query, 'date_sent' );
			$sql        = preg_replace( '/^\sAND/', '', $date_query->get_sql() );
		}

		return $sql;
	}

	protected static function convert_orderby_to_order_by_term( $orderby ) {
		$order_by_term = '';

		switch ( $orderby ) {
			case 'id':
				$order_by_term = 'm.id';
				break;
			case 'sender_id':
			case 'user_id':
				$order_by_term = 'm.sender_id';
				break;
			case 'amount' :
				$order_by_term = 'SUM( m.amount ) AS amount';
				break;
			case 'date_sent':
			default:
				$order_by_term = 'm.date_sent';
				break;
		}

		return $order_by_term;
	}

	public static function get_users_balance( $user_id = 0 ) {

		if ( $user_id === 0 ) {
			return;
		}

		global $wpdb;
		$cb = Confetti_Bits();

		$select_sql = "SELECT identifier, SUM(amount) as amount";
		$from_sql = "FROM {$cb->transactions->table_name} n ";
		$where_sql = self::get_where_sql( array(
			'identifier'		=> $user_id,
			'recipient_id'		=> $user_id,
			'component_name'	=> 'confetti_bits',
		), $select_sql, $from_sql );

		$group_sql = "GROUP BY identifier";

		$pagination_sql = "LIMIT 0, 1";

		$sql = "{$select_sql} {$from_sql} {$where_sql} {$group_sql} {$pagination_sql}";

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public static function wipe_it_down() {

		if ( $user_id === 0 ) {
			return;
		}

		global $wpdb;
		$cb = Confetti_Bits();

		return $wpdb->delete( 
			$cb->transactions->table_name, 
			array(
				'component_name'	=> 'confetti_bits',
			)
		);

	}

	public static function get_totals_groupedby_identifier() {

		global $wpdb;

		$cb = Confetti_Bits();
		$select_sql = "SELECT identifier, SUM(amount) as amount";
		$from_sql = "FROM {$cb->transactions->table_name} n ";
		$where_sql = self::get_where_sql( array(
			'component_name'	=> 'confetti_bits',
		), $select_sql, $from_sql );
		$group_sql = "GROUP BY identifier";
		$order_sql = "ORDER BY amount DESC";
		$pagination_sql = "LIMIT 0, 15";
		$sql = "{$select_sql} {$from_sql} {$where_sql} {$group_sql} {$order_sql} {$pagination_sql}";

		return $wpdb->get_results( $sql, 'ARRAY_A' );

	}

	public static function get_totals_groupedby_recipient_name() {

		global $wpdb;

		$cb = Confetti_Bits();
		$select_sql = "SELECT identifier, recipient_name, SUM(amount) as amount";
		$from_sql = "FROM {$cb->transactions->table_name} n ";
		$where_sql = self::get_where_sql( array(
			'component_name'	=> 'confetti_bits',
		), $select_sql, $from_sql );
		$group_sql = "GROUP BY identifier";
		$order_sql = "ORDER BY recipient_name ASC";
		$sql = "{$select_sql} {$from_sql} {$where_sql} {$group_sql} {$order_sql}";

		return $wpdb->get_results( $sql, 'ARRAY_A' );

	}

	public static function get_activity_bits_transactions_for_today( $user_id ) {

		global $wpdb;

		$bp = buddypress();
		$cb = Confetti_Bits();

		$select_sql = "SELECT user_id, date_sent, component_name, component_action, COUNT(user_id) as total_count";

		$from_sql = "FROM {$cb->transactions->table_name} n ";

		$where_sql = self::get_where_sql( array(
			'user_id'			=> $user_id,
			'date_query'		=> array (
				'column'		=> 'date_sent',
				'compare'		=> 'IN',
				'relation'		=> 'AND',
				'day'			=> bp_core_current_time(false, 'd'),
			),
			'component_name'	=> 'confetti_bits',
			'component_action'	=> 'cb_activity_bits',
		), $select_sql, $from_sql );

		$order_sql = "ORDER BY date_sent desc";

		$sql = "{$select_sql} {$from_sql} {$where_sql} {$order_sql}";

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public static function get_send_bits_transactions_for_today( $user_id ) {

		global $wpdb;

		$bp = buddypress();
		$cb = Confetti_Bits();

		$select_sql = "SELECT id, item_id, secondary_item_id, user_id, sender_id, sender_name, recipient_id, recipient_name, identifier, date_sent, log_entry, component_name, component_action,  amount";

		$from_sql = "FROM {$cb->transactions->table_name} n ";

		$where_sql = self::get_where_sql( array(
			'user_id'			=> $user_id,
			'sender_id'			=> $user_id,
			'date_query'		=> array (
				'column'		=> 'date_sent',
				'compare'		=> 'IN',
				'relation'		=> 'AND',
				'day'			=> bp_core_current_time(false, 'd'),
			),
			'component_name'	=> 'confetti_bits',
			'component_action'	=> 'cb_send_bits',
		), $select_sql, $from_sql );

		$order_sql = "ORDER BY date_sent desc";

		$sql = "{$select_sql} {$from_sql} {$where_sql} {$order_sql}";

		return $wpdb->get_results( $sql );
	}

	public static function get_send_bits_transactions_for_recipient( $user_id ) {

		global $wpdb;

		$cb = Confetti_Bits();

		$select_sql = "SELECT id, item_id, secondary_item_id, user_id, sender_id, sender_name, recipient_id, recipient_name, identifier, date_sent, log_entry, component_name, component_action,  amount";

		$from_sql = "FROM {$cb->transactions->table_name} n ";

		$where_sql = self::get_where_sql( array(
			'recipient_id'		=> $user_id,
			'component_name'	=> 'confetti_bits',
			'component_action'	=> 'cb_send_bits',
		), $select_sql, $from_sql );

		$order_sql = "ORDER BY date_sent desc";

		$sql = "{$select_sql} {$from_sql} {$where_sql} {$order_sql}";

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public function get_paged_transactions_for_user( $user_id, $args = array() ) {
		global $wpdb;
		$bp = buddypress();
		$cb = Confetti_Bits();
		$defaults = array (
			'page'		=> 1,
			'per_page'	=> 5,
		);
		$r = bp_parse_args( $args, $defaults, 'cb_transactions_get_transactions_for_user' );
		$select_sql = "SELECT id, date_sent, log_entry, amount";
		$from_sql = "FROM {$cb->transactions->table_name} n ";
		$where_sql = self::get_where_sql( array(
			'recipient_id'		=> $user_id,
			'item_id'			=> $user_id,
			'component_name'	=> 'confetti_bits',
		), $select_sql, $from_sql );


		$prefetch_select_sql = "SELECT id, COUNT(id) AS total_rows";
		$prefetch_sql = "{$prefetch_select_sql} {$from_sql} {$where_sql}";
		$wpdb_prefetch_total = $wpdb->get_results( $prefetch_sql, 'ARRAY_A');
		//		$this->total_pages = $wpdb_prefetch_total/$r['per_page'];
		$this->total_pages = ceil($wpdb_prefetch_total[0]['total_rows']/$r['per_page']);
		$page_val = ( $r['page'] - 1 ) * $r['per_page'];
		/* * * * * * * * * * * v v v page v v v , v v # of rows v v * * */
		$pagination_sql 	= "LIMIT {$page_val}, {$r['per_page']}";
		$order_sql = "ORDER BY date_sent DESC";
		$sql = "{$select_sql} {$from_sql} {$where_sql} {$order_sql} {$pagination_sql}";
		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	protected static function get_where_sql( $args = array(), $select_sql = '', $from_sql = '', $join_sql = '', $meta_query_sql = '' ) {
		global $wpdb;
		$where_conditions = array();
		$where            = '';
		if ( ! empty( $args['id'] ) ) {
			$id_in                  = implode( ',', wp_parse_id_list( $args['id'] ) );
			$where_conditions['id'] = "id IN ({$id_in})";
		}
		if ( ! empty( $args['user_id'] ) ) {
			$user_id_in                  = implode( ',', wp_parse_id_list( $args['user_id'] ) );
			$where_conditions['user_id'] = "user_id IN ({$user_id_in})";
		}
		if ( ! empty( $args['sender_id'] ) ) {
			$sender_id_in                  = implode( ',', wp_parse_id_list( $args['sender_id'] ) );
			$where_conditions['sender_id'] = "sender_id IN ({$sender_id_in})";
		}
		if ( ! empty( $args['item_id'] ) ) {
			$item_id_in                  = implode( ',', wp_parse_id_list( $args['item_id'] ) );
			$where_conditions['item_id'] = "item_id IN ({$item_id_in})";
		}
		if ( ! empty( $args['secondary_item_id'] ) ) {
			$secondary_item_id_in                  = implode( ',', wp_parse_id_list( $args['secondary_item_id'] ) );
			$where_conditions['secondary_item_id'] = "secondary_item_id IN ({$secondary_item_id_in})";
		}

		if ( ! empty( $args['recipient_id'] ) ) {
			$recipient_id_in                  = implode( ',', wp_parse_id_list( $args['recipient_id'] ) );
			$where_conditions['recipient_id'] = "recipient_id IN ({$recipient_id_in})";
		}

		if ( ! empty( $args['identifier'] ) ) {
			$identifier_in                  = implode( ',', wp_parse_id_list( $args['identifier'] ) );
			$where_conditions['identifier'] = "identifier IN ({$identifier_in})";
		}

		if ( ! empty( $args['component_name'] ) ) {
			if ( ! is_array( $args['component_name'] ) ) {
				$component_names = explode( ',', $args['component_name'] );
			} else {
				$component_names = $args['component_name'];
			}
			$cn_clean = array();
			foreach ( $component_names as $cn ) {
				$cn_clean[] = $wpdb->prepare( '%s', $cn );
			}
			$cn_in                              = implode( ',', $cn_clean );
			$where_conditions['component_name'] = "component_name IN ({$cn_in})";
		}
		if ( ! empty( $args['component_action'] ) ) {
			if ( ! is_array( $args['component_action'] ) ) {
				$component_actions = explode( ',', $args['component_action'] );
			} else {
				$component_actions = $args['component_action'];
			}
			$ca_clean = array();
			foreach ( $component_actions as $ca ) {
				$ca_clean[] = $wpdb->prepare( '%s', $ca );
			}
			$ca_in                                = implode( ',', $ca_clean );
			$where_conditions['component_action'] = "component_action IN ({$ca_in})";
		}
		if ( ! empty( $args['excluded_action'] ) ) {
			if ( ! is_array( $args['excluded_action'] ) ) {
				$excluded_action = explode( ',', $args['excluded_action'] );
			} else {
				$excluded_action = $args['excluded_action'];
			}
			$ca_clean = array();
			foreach ( $excluded_action as $ca ) {
				$ca_clean[] = $wpdb->prepare( '%s', $ca );
			}
			$ca_not_in                           = implode( ',', $ca_clean );
			$where_conditions['excluded_action'] = "component_action NOT IN ({$ca_not_in})";
		}

		if ( ! empty( $args['search_terms'] ) ) {
			$search_terms_like                = '%' . bp_esc_like( $args['search_terms'] ) . '%';
			$where_conditions['search_terms'] = $wpdb->prepare( '( component_name LIKE %s OR component_action LIKE %s )', $search_terms_like, $search_terms_like );
		}

		if ( ! empty( $args['date_query'] ) ) {
			$where_conditions['date_query'] = self::get_date_query_sql( $args['date_query'] );
		}
		if ( ! empty( $meta_query_sql['where'] ) ) {
			$where_conditions['meta_query'] = $meta_query_sql['where'];
		}
		$where_conditions = apply_filters( 'cb_transactions_get_where_conditions', $where_conditions, $args, $select_sql, $from_sql, $join_sql, $meta_query_sql );
		if ( ! empty( $where_conditions ) ) {
			$where = 'WHERE ' . implode( ' AND ', $where_conditions );
		}
		return $where;
	}
	public static function get_recipient_ids( $recipient_usernames ) {
		$recipient_ids = false;
		if ( ! $recipient_usernames ) {
			return $recipient_ids;
		}
		if ( is_array( $recipient_usernames ) ) {
			$rec_un_count = count( $recipient_usernames );
			for ( $i = 0, $count = $rec_un_count; $i < $count; ++ $i ) {
				if ( $rid = bp_core_get_userid( trim( $recipient_usernames[ $i ] ) ) ) {
					$recipient_ids[] = $rid;
				}
			}
		}
		return apply_filters( 'transactions_transaction_get_recipient_ids', $recipient_ids, $recipient_usernames );
	}
	protected static function strip_leading_and( $s ) {
		return preg_replace( '/^\s*AND\s*/', '', $s );
	}
}