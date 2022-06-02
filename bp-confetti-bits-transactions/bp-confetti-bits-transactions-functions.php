<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* * * Confetti Bits Transactions Functions. Hope this works. Good luck! * * */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

defined('ABSPATH') || exit;

function cb_activity_bits( $content, $user_id, $activity_id ) {

	$user_id = bp_loggedin_user_id();
	$user_name = bp_get_loggedin_user_fullname();

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$activity_transactions = $transaction->get_activity_bits_transactions_for_today( $user_id );

	if ( ! empty ( $activity_transactions ) ) {

		foreach ( $activity_transactions as $activity_transaction ) {

			$total_count = $activity_transaction['total_count'];

		}

		if ( ! cb_is_user_site_admin() && $total_count >= 1 ) {
			return;
		}

	}

	$activity_post = cb_send_bits(
		array(
			'item_id'			=> 1,
			'secondary_item_id'	=> $user_id,
			'user_id'			=> $user_id,
			'sender_id'			=> $user_id,
			'sender_name'		=> $user_name,
			'recipient_id' 		=> $user_id,
			'recipient_name'	=> $user_name,
			'identifier'		=> $user_id,
			'date_sent'			=> bp_core_current_time( false ),
			'log_entry'    		=> 'Posted a new update',
			'component_name'    => 'confetti_bits',
			'component_action'  => 'cb_activity_bits',
			'amount'    		=> 1,
			'error_type' 		=> 'wp_error',
		)
	);


}
add_action( 'bp_activity_posted_update', 'cb_activity_bits', 10, 3 );

function cb_send_bits($args = '') {

	$r = bp_parse_args($args, array(
		'item_id'           => 0,
		'secondary_item_id' => 0,
		'user_id'			=> 0,
		'sender_id'         => 0,
		'sender_name'		=> '',
		'recipient_id'		=> 0,
		'recipient_name'	=> '',
		'identifier'		=> 0,
		'date_sent'			=> '',
		'log_entry'			=> '',
		'component_name'    => '',
		'component_action'  => '',
		'date_sent'     	=> bp_core_current_time( false ),
		'amount'			=> 0,
		'error_type'		=> 'bool',
	), 'transactions_new_transaction');

	if ( empty($r['sender_id'] ) || empty( $r['log_entry'] ) ) {

		if ( 'wp_error' === $r['error_type'] ) {

			if ( empty( $r['sender_id'] ) ) {

				$error_code = 'transactions_empty_sender_id';
				$feedback   = __('Your transaction was not sent. We couldn\'t find a sender.', 'confetti-bits');

			} else {

				$error_code = 'transactions_empty_log_entry';
				$feedback   = __('Your transaction was not sent. Please add a log entry.', 'confetti-bits');

			}

			return new WP_Error( $error_code, $feedback );

		} else {

			return false;

		}
	}

	if ( empty( $r['recipient_id'] ) || empty( $r['recipient_name'] ) ) {

		if ( 'wp_error' === $r['error_type'] ) {

			if ( empty( $r['recipient_name'] ) ) {

				$error_code = 'transactions_empty_recipient_name';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipient.', 'confetti-bits');

			} else {

				$error_code = 'transactions_empty_recipient_id';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipient.', 'confetti-bits');

			}

			return new WP_Error( $error_code, $feedback );

		} else {

			return false;

		}
	}

	if ( empty( $r['amount'] ) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_empty_amount';
			$feedback   = __('Your bits were not sent. Please enter a valid amount.', 'confetti-bits');

			return new WP_Error($error_code, $feedback);

		} else {

			return false;

		}
	}

	if ( abs( $r['amount'] ) > cb_get_total_bits( $r['sender_id'] ) && ( $r['amount'] < 0 ) ) {

		if ('wp_error' === $r['error_type']) {

			$error_code = 'transactions_not_enough_bits';
			$feedback   = __('Sorry, it looks like you don\'t have enough bits for that.', 'confetti-bits');

			return new WP_Error($error_code, $feedback);

		} else {

			return false;

		}

	}

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$transaction->item_id 				= $r['item_id'];
	$transaction->secondary_item_id		= $r['secondary_item_id'];
	$transaction->user_id				= $r['user_id'];
	$transaction->sender_id				= $r['sender_id'];
	$transaction->sender_name			= $r['sender_name'];
	$transaction->recipient_id			= $r['recipient_id'];
	$transaction->recipient_name		= $r['recipient_name'];
	$transaction->identifier			= $r['identifier'];
	$transaction->date_sent				= $r['date_sent'];
	$transaction->log_entry				= $r['log_entry'];
	$transaction->component_name		= $r['component_name'];
	$transaction->component_action		= $r['component_action'];
	$transaction->amount				= $r['amount'];

	$send = $transaction->send_bits();

	if ( false === is_int( $send ) ) {

		if ( 'wp_error' === $r['error_type'] ) {

			if ( is_wp_error( $send ) ) {

				return $send;

			} else {

				return new WP_Error(
					'transaction_generic_error',
					__(
						'Bits were not sent. Please try again.',
						'confetti-bits'
					)
				);

			}
		}

		return false;
	}

	do_action( 'cb_send_bits', $r );

	return $transaction->id;

}

function cb_requests() {

	if ( ! bp_is_post_request() || 
		! cb_is_confetti_bits_component() || 
		! isset( $_POST['send_bits_request'] ) ) {
		return false;
	}

	$redirect_to = '';
	$feedback    = '';
	$success     = false;

	if ( empty( $_POST['cb_request_option'] ) || empty( $_POST['cb_request_amount'] ) ) {

		$success = false;
		$feedback = __('The request was not sent. Please select a request option.', 'confetti-bits');

	} else if ( abs( $_POST['cb_request_amount'] ) > cb_get_total_bits( bp_current_user_id() ) ) {

		$success     = false;
		$feedback = __('Sorry, but you don\'t have enough Confetti Bits for that.', 'confetti-bits');

	} else {

		$member_transactions = trailingslashit( bp_loggedin_user_domain() . cb_get_transactions_slug() );

		$lauren = get_user_by( 'email', 'dustin@celebrationtitlegroup.com');
		$lauren_id = $lauren->ID;

		$subtract = cb_send_request(
			array(
				'item_id'			=> $lauren_id,
				'secondary_item_id'	=> $_POST['cb_request_amount'],
				'user_id'			=> bp_current_user_id(),
				'sender_id'			=> bp_current_user_id(),
				'sender_name'		=> bp_get_loggedin_user_fullname(),
				'recipient_id' 		=> bp_current_user_id(),
				'recipient_name'	=> bp_get_loggedin_user_fullname(),
				'identifier'		=> bp_current_user_id(),
				'date_sent'			=> bp_core_current_time( false ),
				'log_entry'    		=> str_replace( "\\", '', $_POST['cb_request_option'] ),
				'component_name'    => 'confetti_bits',
				'component_action'  => 'cb_bits_request',
				'amount'    		=> -$_POST['cb_request_amount'],
				'error_type' 		=> 'wp_error',
			)
		);

		if ( true === is_int( $subtract ) ) {
			$success     = true;
			$feedback    = __(
				'Request received! Your request should be fulfilled within 1-2 weeks.',
				'confetti-bits'
			);

			$view        = trailingslashit( $member_transactions );
			$redirect_to = trailingslashit( $view );
		} else {

			$success  = false;
			$feedback = 'Something went wonky. Call Dustin!';

		}
	}

	if ( ! empty( $feedback ) ) {

		$type = (true === $success)
			? 'success'
			: 'error';

		bp_core_add_message($feedback, $type);
	}

	if ( !empty( $redirect_to ) ) {
		bp_core_redirect( $redirect_to );
	}

}
add_action('bp_actions', 'cb_requests');

function cb_send_request($args = '') {

	$r = bp_parse_args($args, array(
		'item_id'           => 0,
		'secondary_item_id' => 0,
		'user_id'			=> 0,
		'sender_id'         => 0,
		'sender_name'		=> '',
		'recipient_id'		=> 0,
		'recipient_name'	=> '',
		'identifier'		=> 0,
		'date_sent'			=> '',
		'log_entry'			=> '',
		'component_name'    => '',
		'component_action'  => '',
		'date_sent'     	=> bp_core_current_time( false ),
		'amount'			=> 0,
		'error_type'		=> 'bool',
	), 'transactions_new_request');

	if ( empty($r['sender_id'] ) || empty( $r['log_entry'] ) ) {
		if ('wp_error' === $r['error_type']) {
			if ( empty($r['sender_id'] ) ) {
				$error_code = 'transactions_empty_sender_id';
				$feedback   = __('Your transaction was not sent. We couldn\'t find a sender.', 'confetti-bits');
			} else {
				$error_code = 'transactions_empty_log_entry';
				$feedback   = __('Your transaction was not sent??? Please add a log entry.', 'confetti-bits');
			}

			return new WP_Error($error_code, $feedback);
		} else {

			return false;
		}
	}

	if ( empty($r['recipient_id'] ) || empty( $r['recipient_name'] ) ) {
		if ('wp_error' === $r['error_type']) {
			if (empty($r['recipient_name'])) {
				$error_code = 'transactions_empty_recipient_name';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipient.', 'confetti-bits');
			} else {
				$error_code = 'transactions_empty_recipient_id';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipient.', 'confetti-bits');
			}

			return new WP_Error($error_code, $feedback);
		} else {
			return false;
		}
	}

	if ( empty($r['amount'] ) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_empty_amount';
			$feedback   = __('Your bits were not sent. Please enter a valid amount.', 'confetti-bits');

			return new WP_Error( $error_code, $feedback );
		} else {
			return false;
		}
	}

	if ( abs( $r['amount'] ) > cb_get_total_bits( $r['sender_id'] ) && ( $r['amount'] < 0) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_not_enough_bits';
			$feedback   = __('Sorry, it looks like you don\'t have enough bits for that.', 'confetti-bits');

			return new WP_Error( $error_code, $feedback );
		} else {
			return false;
		}
	}

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$transaction->item_id 				= $r['item_id'];
	$transaction->secondary_item_id		= $r['secondary_item_id'];
	$transaction->user_id				= $r['user_id'];
	$transaction->sender_id				= $r['sender_id'];
	$transaction->sender_name			= $r['sender_name'];
	$transaction->recipient_id			= $r['recipient_id'];
	$transaction->recipient_name		= $r['recipient_name'];
	$transaction->identifier			= $r['identifier'];
	$transaction->date_sent				= $r['date_sent'];
	$transaction->log_entry				= $r['log_entry'];
	$transaction->component_name		= $r['component_name'];
	$transaction->component_action		= $r['component_action'];
	$transaction->amount				= $r['amount'];

	$send = $transaction->send_bits();


	if ( false === is_int($send) ) {
		if ( 'wp_error' === $r['error_type'] ) {
			if ( is_wp_error($send) ) {
				return $send;
			} else {
				return new WP_Error(
					'transaction_generic_error',
					__(
						'Bits were not sent. Please try again.',
						'confetti-bits'
					)
				);
			}
		}

		return false;
	}

	do_action('cb_request_bits', $r);

	return $transaction->id;
}

function cb_bits_request_email_notification( $args = array() ) {

	$r = bp_parse_args( $args, array(
		'recipient_id'	=> 0,
		'sender_id'		=> 0,
		'request_item'	=> '',
	), 'cb_transactions_new_request_email');

	$request_recipient_name	= bp_core_get_user_displayname( $r['recipient_id'] );
	$request_sender_name	= bp_core_get_user_displayname( $r['sender_id'] );
	$request_item			= $r['request_item'];
	$cb_page_link			= trailingslashit( bp_loggedin_user_domain() . cb_get_transactions_slug() );

	if ( 'no' != bp_get_user_meta( $r['recipient_id'], 'cb_bits_request', true ) ) {

		$unsubscribe_args = array(
			'user_id'           => $r['recipient_id'],
			'notification_type' => 'cb-bits-request-email',
		);

		$email_args = array(
			'tokens' => array(
				'request_sender.id'		=> $r['sender_id'],
				'request_sender.name'	=> $request_sender_name,
				'request_sender.item'	=> $r['request_item'],
				'unsubscribe'			=> esc_url( bp_email_get_unsubscribe_link( $unsubscribe_args ) ),
			),
		);

		bp_send_email( 'cb-bits-request-email', $r['recipient_id'], $email_args );
	}

	do_action( 'cb_transactions_sent_request_email_notification', $args );
}

function cb_import_bits($args = '') {

	$r = bp_parse_args($args, array(
		'item_id'           => 0,
		'secondary_item_id' => 0,
		'user_id'			=> 0,
		'sender_id'         => 0,
		'sender_name'		=> '',
		'recipient_id'		=> 0,
		'recipient_name'	=> '',
		'identifier'		=> 0,
		'date_sent'			=> '',
		'log_entry'			=> '',
		'component_name'    => '',
		'component_action'  => '',
		'date_sent'     	=> bp_core_current_time( false ),
		'amount'			=> 0,
		'error_type'		=> 'bool',
	), 'transactions_new_import');

	if ( empty($r['sender_id'] ) || empty( $r['log_entry'] ) ) {
		if ('wp_error' === $r['error_type']) {
			if ( empty($r['sender_id'] ) ) {
				$error_code = 'transactions_empty_sender_id';
				$feedback   = __('Your transaction was not sent. We couldn\'t find a sender.', 'confetti-bits');
			} else {
				$error_code = 'transactions_empty_log_entry';
				$feedback   = __('Your transaction was not sent. Please add log entries.', 'confetti-bits');
			}

			return new WP_Error($error_code, $feedback);
		} else {

			return false;
		}
	}

	if ( empty($r['recipient_id'] ) || empty( $r['recipient_name'] ) ) {
		if ('wp_error' === $r['error_type']) {
			if (empty($r['recipient_name'])) {
				$error_code = 'transactions_empty_recipient_name';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipients.', 'confetti-bits');
			} else {
				$error_code = 'transactions_empty_recipient_id';
				$feedback   = __('Your bits were not sent. We couldn\'t find the recipients.', 'confetti-bits');
			}

			return new WP_Error($error_code, $feedback);
		} else {
			return false;
		}
	}

	if ( empty($r['amount'] ) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_empty_amount';
			$feedback   = __('Your bits were not sent. Please enter a valid amount.', 'confetti-bits');

			return new WP_Error( $error_code, $feedback );
		} else {
			return false;
		}
	}

	if ( abs( $r['amount'] ) > cb_get_total_bits( $r['recipient_id'] ) && ( $r['amount'] < 0) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_not_enough_bits';
			$feedback   = __('Sorry, it looks like you don\'t have enough bits for that.', 'confetti-bits');

			return new WP_Error( $error_code, $feedback );
		} else {
			return false;
		}
	}

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$transaction->item_id 				= $r['item_id'];
	$transaction->secondary_item_id		= $r['secondary_item_id'];
	$transaction->user_id				= $r['user_id'];
	$transaction->sender_id				= $r['sender_id'];
	$transaction->sender_name			= $r['sender_name'];
	$transaction->recipient_id			= $r['recipient_id'];
	$transaction->recipient_name		= $r['recipient_name'];
	$transaction->identifier			= $r['identifier'];
	$transaction->date_sent				= $r['date_sent'];
	$transaction->log_entry				= $r['log_entry'];
	$transaction->component_name		= $r['component_name'];
	$transaction->component_action		= $r['component_action'];
	$transaction->amount				= $r['amount'];

	$send = $transaction->send_bits();


	if ( false === is_int($send) ) {
		if ( 'wp_error' === $r['error_type'] ) {
			if ( is_wp_error($send) ) {
				return $send;
			} else {
				return new WP_Error(
					'transaction_generic_error',
					__(
						'Bits were not sent. Please try again.',
						'confetti-bits'
					)
				);
			}
		}

		return false;
	}

	do_action('cb_import_bits', $r);

	return $transaction->id;
}

function cb_delete_all() {

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$transaction->wipe_it_down();

	return 'It is done.';

}

function cb_importer() {

	if ( ! bp_is_post_request() || ! cb_is_confetti_bits_component() || ! isset( $_POST['cb_bits_imported'] ) ) {
		return;
	}

	global $wpdb;

	require( ABSPATH . 'wp-admin/includes/import.php');

	if ( ! class_exists( 'WP_Importer' ) ) {
		$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		if ( file_exists($class_wp_importer) )
			require $class_wp_importer;
	}

	if ( ! function_exists('wp_handle_upload') ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}


	// redirect variables
	$member_transactions = trailingslashit( bp_loggedin_user_domain() . cb_get_transactions_slug() );
	$view        = trailingslashit( $member_transactions );
	$redirect_to = trailingslashit( $view );

	// loop variables
	$row_list = array();
	$imported = 0;
	$skipped = 0;
	$row_number = 2;
	$skip_list = array();
	$skipped_users = '';


	// display & setup variables
	$max_upload_size = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
	$max_upload_display_text = size_format( $max_upload_size );
	$upload_dir = wp_upload_dir();

	// start the actual business
	$file = wp_import_handle_upload();

	// pretty much everything hinges on there not being a fundamental problem with the file upload
	if ( ! empty( $file['error'] ) || empty( $file['id'] ) ) {

		$success  = false;
		$feedback = $file['error'];

	} if ( ! cb_is_user_site_admin() ) {

		$success  = false;
		$feedback = __('Sorry, you don\'t have permission to import Confetti Bits. Call Dustin!', 'confetti-bits');

	} else {

		$attached_file = get_attached_file($file['id']);

		if ( ! is_file( $attached_file ) ) {

			$success  = false;
			$feedback = __('The file does not exist or could not be read.', 'confetti-bits');

		}

		$file_stream = fopen( $attached_file, "r" );
		$each_row = fgetcsv( $file_stream, 0, "," );
		$no_of_columns = sizeof( $each_row );

		if ( $no_of_columns != 4 ) {

			$success  = false;
			$feedback = __('Invalid CSV file. Make sure there are only 4 columns containing first name, last name, amount of bits, and a log entry', 'confetti-bits');
		}

		$ran = false;
		$row_loop = 0;

		while ( ( $each_row = fgetcsv( $file_stream, 0, "," ) ) !== false ) {

			$fname = '';
			$lname = '';
			$amount = 0;
			$log_entry = '';
			$row_error = false;

			list( $fname, $lname, $amount, $log_entry ) = $each_row;

			$r = array(
				'type' => 'alphabetical',
				'search_terms' => trim( $fname . ' ' . $lname ),
				'search_wildcard' => 'both',
				'per_page' => 2,
				'error_type'	=> 'wp_error',
			);

			if ( empty ( $r ['search_terms'] ) ) {
				$skip_list[] = 'No first or last name in row ' . $row_number . '.';
				$row_error = true;
				$skipped++;
				$row_number++;
				continue;
			}

			$new_user_query	= new BP_User_Query( $r );
			$new_user_query->__construct();
			$query_results	= $new_user_query->results;

			$recipient_id = 0;

			if ( empty( $query_results ) || ! $new_user_query ) {

				$skip_list[] = 'The name "' . trim( $fname . ' ' . $lname ) . '"' . 
					' in row ' . $row_number . 
					' didn\'t show up in the member search.';
				$skipped++;
				$row_number++;
				$row_error = true;
				continue;

			}

			if ( count( $query_results ) > 1 || count( $query_results ) === 2 ) {
				$skip_list[] = '"' . $fname . ' ' . $lname . '"' . ' in row ' . $row_number . ' returned multiple members.';
				$skipped++;
				$row_number++;
				$row_error = true;
				continue;
			}

			if ( count( $query_results ) === 1 ) {
				foreach ( $query_results as $query_result ) {
					$recipient_id = $query_result->ID;
				}
			}

			if ( $recipient_id == false || $recipient_id == 0 ) {

				$skip_list[] = 'The name "' . trim( $fname . ' ' . $lname ) . '"' . 
					' in row ' . $row_number . 
					' didn\'t show up in the member search.';
				$skipped++;
				$row_number++;
				$row_error = true;
				continue;
			}

			if ( $amount < 0 && abs( $amount ) > cb_get_total_bits( $recipient_id ) ) {

				$skip_list[] = $fname . ' ' . $lname . 
					' in row ' . $row_number . 
					' didn\'t have enough Confetti Bits to buy something.';
				$skipped++;
				$row_number++;
				$row_error = true;
				continue;
			}

			if ( ! is_numeric( $amount ) || empty( $amount ) ) {

				if ( ! is_numeric( $amount ) ) {
					$skip_list[] = '"' . $amount . '" is not a number in row ' . $row_number . '.';
				} else if ( empty( $amount ) ) {
					$skip_list[] = 'Amount is empty in row ' . $row_number . '.';
				} else {
					$skip_list[] = 'Invalid amount entered in row ' . $row_number . '.';
				}

				$row_error = true;
				$skipped++;
				$row_number++;
				continue;
			}

			if ( empty( $log_entry ) ) {

				$skip_list[] = 'No log entry in row ' . $row_number . '.';
				$skipped++;
				$row_number++;
				$row_error = true;
				continue;
			}

			$sender_id = get_current_user_id();
			$sender_name = bp_get_loggedin_user_fullname();
			$row_error = false;

			if ( ! $row_error && ! empty( $log_entry ) && ! empty( $recipient_id ) && ! empty( $amount ) ) {

				$send = cb_import_bits(
					$args = array(
						'item_id'           => $recipient_id,
						'secondary_item_id' => $amount,
						'user_id'			=> $sender_id,
						'sender_id'         => $sender_id,
						'sender_name'		=> $sender_name,
						'recipient_id'		=> $recipient_id,
						'recipient_name'	=> bp_xprofile_get_member_display_name( $recipient_id ),
						'identifier'		=> $recipient_id,
						'date_sent'			=> bp_core_current_time( false ),
						'log_entry'			=> $log_entry,
						'component_name'    => 'confetti_bits',
						'component_action'  => 'cb_import_bits',
						'amount'			=> $amount,
						'error_type'		=> 'wp_error',
					)
				);

			}

			$row_loop++;
			$imported++;
			$row_number++;

		}


		$ran = true;

		fclose( $file_stream );
		$file = '';

		$success  = true;

		if ( $imported === 1 ) {

			$feedback = __('Not a problem in sight, we successfully imported ' . $imported . ' row!.', 'confetti-bits');	

		} else {

			$feedback = __('Not a problem in sight, we successfully imported ' . $imported . ' rows!.', 'confetti-bits');	

		}


	}

	if ( ! empty( $skip_list ) && $ran = true ) {

		$type = 'success';
		$feedback = '
		<span>Successfully imported: ' . $imported .'. But these oopsies came up: </span>
				<strong>' . implode(
			' ',
			$skip_list
		) . '</strong>';

	}

	if ( ! empty( $feedback ) ) {

		$type = ( true === $success )
			? 'success'
			: 'error';

		bp_core_add_message($feedback, $type);
	}

	if ( ! empty( $redirect_to ) ) {
		bp_core_redirect( add_query_arg( array(
			'results' => $type,
		), $redirect_to ) );
	}
}
add_action('bp_actions', 'cb_importer');

function cb_send_bits_form() {

	if ( ! bp_is_post_request() || 
		! cb_is_confetti_bits_component() ||
		! isset( $_POST['cb_send_bits'] ) ) {
		return false;
	}

	$redirect_to = '';
	$feedback    = '';
	$success     = false;

	if ( empty( $_POST['log_entry'] ) || empty( $_POST['amount'] ) ) {
		
		$success = false;

		if ( empty( $_POST['log_entry'] ) ) {
			
			$feedback = __('Your transaction was not sent. Please add a log entry.', 'confetti-bits');
			
		} else {
			
			$feedback = __('Your transaction was not sent. Please enter an amount.', 'confetti-bits');
			
		}
		
	} else if ( ( abs( $_POST['amount'] ) > cb_get_total_bits( $_POST['recipient_id'] ) ) && ( $_POST['amount'] < 0 ) ) {
		$success     = false;
		$feedback = __( bp_xprofile_get_member_display_name( $_POST['recipient_id'] ) . ' doesn\'t have enough Confetti Bits for that.', 'confetti-bits' );
		
	} else if ( $_POST['amount'] + cb_get_total_for_current_day() > 20 && ! cb_is_user_site_admin() ) {

		$success     = false;
		$feedback = __('You\'ve already sent 20 Confetti Bits today. Your counter will reset tomorrow!', 'confetti-bits');
		
	} else if ( $_POST['amount'] > cb_get_total_bits( $_POST['sender_id'] ) && ! cb_is_user_admin() ) {

		$success     = false;
		$feedback = __('Sorry, it looks like you don\'t have enough bits to send.', 'confetti-bits');

	} else {

		$member_transactions = trailingslashit( bp_loggedin_user_domain() . cb_get_transactions_slug() );

		if ( cb_is_user_admin() ) {

			$send = cb_send_bits(
				array(
					'item_id'			=> $_POST['recipient_id'],
					'secondary_item_id'	=> $_POST['amount'],
					'user_id'			=> bp_current_user_id(),
					'sender_id'			=> bp_current_user_id(),
					'sender_name'		=> bp_get_loggedin_user_fullname(),
					'recipient_id' 		=> $_POST['recipient_id'],
					'recipient_name'	=> bp_xprofile_get_member_display_name( $_POST['recipient_id'] ),
					'identifier'		=> $_POST['recipient_id'],
					'date_sent'			=> bp_core_current_time( false ),
					'log_entry'    		=> str_replace("\\", '', $_POST['log_entry']) . ' – from ' .
					bp_core_get_user_displayname($_POST['sender_id']),
					'component_name'    => 'confetti_bits',
					'component_action'  => 'cb_send_bits',
					'amount'    		=> $_POST['amount'],
					'error_type' 		=> 'wp_error',
				)
			);

			if ( true === is_int( $send ) ) {
				$success     = true;
				$feedback    = __(
					'We successfully sent bits to ' .
					bp_core_get_user_displayname( $_POST['recipient_id'] ) .
					'!',
					'confetti-bits'
				);

				$view        = trailingslashit($member_transactions);
				$redirect_to = trailingslashit($view);
				
			} else {
				
				$success  = false;
				$feedback = 'Something\'s broken, call Dustin.';
				
			}
		} else {

			$send = cb_send_bits(
				array(
					'item_id'			=> $_POST['recipient_id'],
					'secondary_item_id'	=> $_POST['amount'],
					'user_id'			=> bp_current_user_id(),
					'sender_id'			=> bp_current_user_id(),
					'sender_name'		=> bp_get_loggedin_user_fullname(),
					'recipient_id' 		=> $_POST['recipient_id'],
					'recipient_name'	=> bp_core_get_user_displayname($_POST['recipient_id']),
					'identifier'		=> $_POST['recipient_id'],
					'date_sent'			=> bp_core_current_time( false ),
					'log_entry'			=> $_POST['log_entry'] . ' – from ' .
					bp_core_get_user_displayname($_POST['sender_id']),
					'component_name'    => 'confetti_bits',
					'component_action'  => 'cb_send_bits',
					'amount'    		=> $_POST['amount'],
					'error_type' 		=> 'wp_error',
				)
			);

			$subtract = cb_send_bits(
				array(
					'item_id'			=> $_POST['sender_id'],
					'secondary_item_id'	=> $_POST['amount'],
					'user_id'			=> bp_current_user_id(),
					'sender_id'			=> bp_current_user_id(),
					'sender_name'		=> bp_get_loggedin_user_fullname(),
					'recipient_id' 		=> $_POST['sender_id'],
					'recipient_name'	=> bp_core_get_user_displayname($_POST['sender_id']),
					'identifier'		=> $_POST['sender_id'],
					'date_sent'			=> bp_core_current_time( false ),
					'log_entry'    		=> 'Sent bits to ' . bp_core_get_user_displayname($_POST['recipient_id']),
					'component_name'    => 'confetti_bits',
					'component_action'  => 'cb_send_bits',
					'amount'    		=> -$_POST['amount'],
					'error_type' 		=> 'wp_error',
				)
			);

			if (true === is_int($send) && true === is_int($subtract)) {
				$success     = true;
				$feedback    = __(
					'We successfully sent bits to ' .
					bp_core_get_user_displayname($_POST['recipient_id']) .
					'!',
					'confetti-bits'
				);

				$view        = trailingslashit($member_transactions);
				$redirect_to = trailingslashit($view);
			} else {
				$success  = false;
				$feedback = 'Something\'s broken. Call Dustin.';
			}
		}
	}

	if ( ! empty( $feedback ) ) {

		$type = (true === $success)
			? 'success'
			: 'error';

		bp_core_add_message($feedback, $type);
	}

	if (!empty($redirect_to)) {
		bp_core_redirect($redirect_to);
	}
}
add_action('bp_actions', 'cb_send_bits_form');

function cb_member_search( $args = '' ) {


	$r = bp_parse_args( $args, array(
		'type'				=> 'alphabetical',
		'search_terms'		=> '',
		'exclude'			=> '',
		'search_wildcard'	=> 'both',
		'count_total'		=> 'sql_count_found_rows',
		'per_page'			=> 8,
		'error_type' 		=> 'bool',
	), 'transactions_new_member_query');


	if ( empty( $r['search_terms'] ) ) {
		if ( 'wp_error' === $r['error_type'] ) {

			$error_code = 'transactions_search_terms';
			$feedback   = __('We need something to search for! Try looking someone up by their first and/or last name.', 'confetti-bits');

			return new WP_Error( $error_code, $feedback );
		} else {
			return false;
		}
	}

	$member_search = new BP_User_Query( $r );

	$member_search->__construct();

	return $member_search->results;
}

function cb_transactions_new_member_search() {

	if ( !bp_is_post_request() || !cb_is_confetti_bits_component() || ! isset( $_POST['cb_member_search_submit'] ) ) {
		return false;
	}

	$redirect_to = '';
	$feedback    = '';
	$success     = false;

	if ( empty( trim( $_POST['cb_member_search_terms'] ) ) ) {

		$feedback   = __('Your search didn\'t go through – we can\'t seem to locate any vibes associated with "(abject nonexistence)." :/', 'confetti-bits');

	} else {

		$member_transactions = trailingslashit( bp_loggedin_user_domain() ) . cb_get_transactions_slug();

		$exclude_user = ( cb_is_user_site_admin() ? '' : get_current_user_id() );

		$search = cb_member_search(
			array(
				'type' 				=> 'alphabetical',
				'search_terms'		=> trim($_POST['cb_member_search_terms']),
				'exclude'			=> $exclude_user,
				'search_wildcard'	=> 'both',
				'count_total' 		=> 'sql_count_found_rows',
				'per_page'			=> 8,
				'error_type'		=> 'wp_error',
			)
		);

		if ( true === is_array( $search ) ) {

			$search_count = count( $search );

			if ( $search_count === 0 ) {

				$success     = false;
				$feedback    = __(
					'I\'m sorry, I couldn\'t find a gosh darn thing from searching "' .
					$_POST['cb_member_search_terms'] .
					'" :/',
					'confetti-bits'
				);
			}

			if ( $search_count === 1 ) {

				$success     = true;
				$feedback    = __(
					'I found ' . $search_count .
					' lone ranger from looking up "' .
					$_POST['cb_member_search_terms'] .
					'". I hope they fare well in their travels.',
					'confetti-bits'
				);
			}

			if ( $search_count > 1 && $search_count < 8 ) {

				$success     = true;
				$feedback    = __(
					'I found ' .
					$search_count .
					' awesome folks from searching "' .
					$_POST['cb_member_search_terms'] .
					'":',
					'confetti-bits'
				);
			}

			if ( $search_count >= 8 ) {

				$success     = true;
				$feedback    = __(
					'I found a whole bunch of people (more than ' .
					$search_count . ') from searching "' .
					$_POST['cb_member_search_terms'] .
					'". If you can\'t find who you\'re looking for, 
		try typing in a first and last name!',
					'confetti-bits'
				);
			}

			$view        = trailingslashit( $member_transactions );
			$redirect_to = trailingslashit( $view );
		} else {
			$success  = false;
			$feedback = __( 'Something\'s wonky. Call Dustin.', 'confetti-bits' );
		}
	}

	if ( !empty( $feedback ) ) {

		$type = ( true === $success )
			? 'success'
			: 'error';

		bp_core_add_message( $feedback, $type );
	}

	if ( ! empty( $redirect_to ) ) {
		bp_core_redirect(add_query_arg(array(
			'results' => $type,
			'search_terms' => trim( $_POST['cb_member_search_terms'] ),
		), $redirect_to ) );
	}
}
add_action('bp_actions', 'cb_transactions_new_member_search');


function cb_get_member_search_results( $search_results = array() ) {

	if (isset($_GET['results']) && 'success' === $_GET['results'] && isset($_GET['search_terms'])) {

		$exclude_user = (cb_is_user_site_admin() ? '' : get_current_user_id());

		$search_results = cb_member_search(
			array(
				'type' => 'alphabetical',
				'search_terms' => $_GET['search_terms'],
				'exclude' => $exclude_user,
				'search_wildcard' => 'both',
				'count_total' => 'sql_count_found_rows',
				'per_page' => 8,
				'error_type' 		=> 'wp_error',
			)
		);

		if ( empty( $search_results ) ) {

			return;
		} else {


			foreach ($search_results as $member) {

				$member_id = $member->ID;
				$member_display_name = bp_xprofile_get_member_display_name($member->ID);
				$member_avatar = bp_core_fetch_avatar(
					array(
						'item_id' => $member->ID,
						'object'  => 'user',
						'type'    => 'thumb',
						'width'   => BP_AVATAR_THUMB_WIDTH,
						'height'  => BP_AVATAR_THUMB_HEIGHT,
						'html'    => true,
					)
				);

				echo sprintf(
					'<div class="memberSelect send-bits member-data" 
		data-member-id="%d"
		data-member-display-name="%s"
		id="member-data-%d">
		<div class="cb-search-results-avatar">%s</div>
		<p class="memberName">%s</p>
		</div>',
					$member_id,
					$member_display_name,
					$member_id,
					$member_avatar,
					$member_display_name
				);
			}
		}
	}
}

function cb_search_results() {

	echo cb_get_member_search_results();
}

function cb_get_total_for_current_day() {

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$fetched_transactions = $transaction->get_send_bits_transactions_for_today(get_current_user_id());
	$total = 0;

	foreach ( $fetched_transactions as $fetched_transaction ) {

		if ( $fetched_transaction->amount > 0 ) {
			$total += $fetched_transaction->amount;
		}
	}

	if ( 0 !== $total ) {

		return $total;
	} else {

		return 0;
	}

	return $total;
}

function cb_get_total_for_current_day_notice() {

	$amount = cb_get_total_for_current_day();

	if ( empty( $amount ) || $amount === 0 ) {

		$notice = 'You\'ve sent 0 Confetti Bits so far today. You can send up to 20.';

	} else {

		if ( $amount > 1 && $amount < 20) {
			$notice = 'You\'ve sent ' . $amount .
				' Confetti Bits so far today. You can send up to ' .
				(20 - $amount) . ' more.';
		}

		if ($amount === 1) {
			$notice = 'You\'ve sent ' . $amount .
				' Confetti Bit so far today. You can send up to 19 more.';
		}

		if ($amount >= 20) {
			$notice = 'You\'ve already sent ' . $amount .
				' Confetti Bits today. Your counter should reset tomorrow!';
		}
	}

	return $notice;
}

add_action('bp_actions', 'cb_get_total_for_current_day_notice');

function cb_total_for_current_day_notice() {

	echo cb_get_total_for_current_day_notice();
}


function cb_update_total_bits( $user_id = 0, $meta_key = 'cb_total_bits', $previous_total = '' ) {

	if ( $user_id == 0 ) {
		$user_id = get_current_user_id();
	}

	$transaction_logs = new Confetti_Bits_Transactions_Transaction();
	$transaction_query = $transaction_logs->get_users_balance( $user_id );

	foreach ( $transaction_query as $query_result ) {

		$total = $query_result['amount'];

	}

	return update_user_meta( $user_id, $meta_key, $total, $previous_total );

}
add_action('bp_actions', 'cb_update_total_bits');

function cb_get_total_bits( $user_id, $meta_key = 'cb_total_bits', $unique = true ) {

	if ( $user_id === 0 ) {
		return;
	}

	$total = get_user_meta($user_id, $meta_key, $unique);

	return $total;

}

function cb_get_total_bits_notice( $user_id, $meta_key = 'cb_total_bits', $unique = true ) {

	if ( $user_id === 0 ) {
		return;
	}

	$notice = '';
	$total = get_user_meta($user_id, $meta_key, $unique);

	if ( $total == 1 ) {

		$notice = 'You currently have ' . $total . ' Confetti Bit.';

	}

	if ( $total < 1 || $total == 0 ) {

		$notice = 'You don\'t currently have any Confetti Bits.';

	}

	if ( $total > 1 ) {

		$notice = 'You currently have ' . $total . ' Confetti Bits.';

	}

	return $notice;

}


function cb_get_user_meta($user_id = 0, $meta_key, $unique = true) {

	if ( $user_id === 0 ) {
		return;
	}

	return get_user_meta($user_id, $meta_key, $unique);
}

function cb_update_user_meta($user_id = 0, $meta_key = '', $meta_value) {

	if ( $user_id === 0 ) {
		return;
	}

	return update_user_meta( $user_id, $meta_key, $meta_value );
}

function cb_leaderboard() {

	$transaction = new Confetti_Bits_Transactions_Transaction();
	$leaderboard_data = $transaction->get_totals_groupedby_identifier();
	$placement_digit = 0;
	$placement_suffix = '';
	$user_display_name = '';

	foreach ( $leaderboard_data as $leaderboard_entry ) {
		$placement_digit++;
		$user_display_name = bp_xprofile_get_member_display_name($leaderboard_entry['identifier']);
		$user_profile_url = bp_core_get_user_domain($leaderboard_entry['identifier']);
		switch ($placement_digit) {

			case ($placement_digit === 1):
				$placement_suffix = 'st';
				break;
			case ($placement_digit === 2):
				$placement_suffix = 'nd';
				break;
			case ($placement_digit === 3):
				$placement_suffix = 'rd';
				break;
			case ($placement_digit >= 4 && $placement_digit !== "/[2-9][1-3]/"):
				$placement_suffix = 'th';
		}
		echo sprintf(
			'<div class="cb-leaderboard-entry">
	<span class="cb-leaderboard-entry-item cb-placement">%d%s</span>
	<span class="cb-leaderboard-entry-item cb-user-link"><a href="%s">%s</a></span>
	<span class="cb-leaderboard-entry-item cb-user-leaderboard-bits">%d</span>
	</div>',
			$placement_digit,
			$placement_suffix,
			$user_profile_url,
			$user_display_name,
			$leaderboard_entry['amount'],
		);
	}
}

function cb_log() {

	if ( !cb_is_confetti_bits_component() || !cb_is_user_confetti_bits() ) {

		return false;

	}

	$cb_log_url			= trailingslashit(bp_loggedin_user_domain() . cb_get_transactions_slug());
	$current_log_page	= ( !empty($_GET['cb_log_page'] ) ? $_GET['cb_log_page'] : 1 );
	$transactions	 	= new Confetti_Bits_Transactions_Transaction();
	$paged_transactions = $transactions->get_paged_transactions_for_user(
		get_current_user_id(),
		array(
			'page'		=> $current_log_page,
			'per_page'	=> 5,
		)
	);
	$page_total_cap 	= $transactions->total_pages;

	cb_log_pagination( $current_log_page, $page_total_cap, $cb_log_url );

	cb_log_header();
	cb_log_entries( $paged_transactions );

}

function cb_log_pagination( $current_log_page, $page_total_cap, $cb_log_url ) {

	$pagination_links = cb_log_get_page_urls( $current_log_page, $page_total_cap, $cb_log_url, $page_range = 5 );
	$pagination_list_items = array();

	foreach ( $pagination_links as $pagination_link ) {

		if ( $pagination_link['enabled'] ) {

			$pagination_list_items[] = '<li><a href="' .
				$pagination_link['url'] . '">' .
				$pagination_link['text'] .
				'</a></li>';

		} else {

			$pagination_list_items[] = '<li class="cb-log-link-disabled">' .
				$pagination_link['text'] .
				'</li>';

		}


	}

	$args = $pagination_list_items;
	$string_tags_repeater = trim( str_repeat( "%s ", count( $args ) ) );
	echo '<ul class="cb-log-pagination">' . vsprintf( $string_tags_repeater, $args ) . '</ul>';

}

function cb_log_get_page_urls( $current_log_page, $page_total_cap, $cb_log_url, $page_range = 5 ) {

	$pagination_links		= array();
	$link_text 				= array();
	$previous_page_number	= $current_log_page - 1;
	$next_page_number 		= $current_log_page + 1;
	$page_start 			= $current_log_page;



	if ( $page_total_cap == 0 ) {

		$page_range = 5;
		$page_range_cap = $page_start + $page_range - 1;

		$pagination_links[]	= array(
			'url'		=> '',
			'text'		=> '«',
			'enabled'	=> false,
		);

		$pagination_links[]	= array(
			'url'		=> '',
			'text'		=> '‹',
			'enabled'	=> false,
		);

		for ( $i = $page_start; $i <= $page_range_cap; $i++ ) {

			$pagination_links[] = array(
				'url'		=> '',
				'text'		=> $i,
				'enabled'	=> false,
			);
		}

		$pagination_links[] = array(
			'url'		=> '',
			'text'		=> '›',
			'enabled'	=> false,
		);

		$pagination_links[] = array(
			'url'		=> '',
			'text'		=> '»',
			'enabled'	=> false,
		);

	} else {

		$page_range 			= ( $page_range > $page_total_cap ? $page_total_cap : $page_range );
		$page_range_cap 		= $page_start + $page_range - 1;

		if ( $current_log_page >= ( $page_total_cap - $page_range + 1 ) ) {

			$page_start = $page_total_cap - $page_range + 1;
			$page_range_cap = $page_total_cap;

		}

		if ( $current_log_page <= 1 ) {

			$pagination_links[]	= array(
				'url'		=> '',
				'text'		=> '«',
				'enabled'	=> false,
			);

		} else {

			$pagination_links[]	= array(
				'url'		=> esc_url( add_query_arg( array( 'cb_log_page' => 1, ), $cb_log_url ) ),
				'text'		=>  '«',
				'enabled'	=> true,
			);
		}

		if ( $previous_page_number < 1 ) {

			$pagination_links[]	= array(
				'url'		=> '',
				'text'		=> '‹',
				'enabled'	=> false,
			);

		} else {

			$pagination_links[]		= array(
				'url'		=> esc_url(add_query_arg(array('cb_log_page' => $previous_page_number,), $cb_log_url)),
				'text'		=> '‹',
				'enabled'	=> true,
			);
		}

		for ( $i = $page_start; $i <= $page_range_cap; $i++ ) {

			$pagination_links[] = array(
				'url'		=> esc_url( add_query_arg( array( 'cb_log_page' => $i, ), $cb_log_url ) ),
				'text'		=> $i,
				'enabled'	=> true,
			);
		}

		if ( $next_page_number >= $page_total_cap ) {

			$pagination_links[] = array(
				'url'		=> '',
				'text'		=> '›',
				'enabled'	=> false,
			);
		} else {

			$pagination_links[] = array(
				'url'		=> add_query_arg(array('cb_log_page' => $next_page_number,), $cb_log_url),
				'text'		=> '›',
				'enabled'	=> true,
			);
		}

		if ( $current_log_page >= $page_total_cap ) {

			$pagination_links[] = array(
				'url'		=> '',
				'text'		=> '»',
				'enabled'	=> false,
			);
		} else {

			$pagination_links[] = array(
				'url'		=> add_query_arg(array('cb_log_page' => $page_total_cap,), $cb_log_url),
				'text'		=> '»',
				'enabled'	=> true,
			);
		}
	}

	return $pagination_links;
}

function cb_log_header() {

	echo '<div class="cb-log-header">
	<span class="cb-log-header-item">Transaction Date</span>
	<span class="cb-log-header-item">Amount Exchanged</span>
	<span class="cb-log-header-item">Log Entry</span>
	</div>';
}

function cb_log_entries( $paged_transactions ) {

	foreach ( $paged_transactions as $paged_transaction ) {

		$transaction_date = date("M d, Y | g:iA", strtotime( $paged_transaction['date_sent'] ) );
		$amount_entry = '';

		switch ( true ) {

			case ( intval( $paged_transaction['amount'] ) == -1):
				$amount_entry = 'spent ' . str_replace('-', '', $paged_transaction['amount']) . ' Confetti Bit';
				break;
			case ( intval( $paged_transaction['amount'] ) < -1):
				$amount_entry = 'spent ' . str_replace('-', '', $paged_transaction['amount']) . ' Confetti Bits';
				break;
			case ( intval( $paged_transaction['amount'] ) > 1 ):
				$amount_entry = 'received ' . $paged_transaction['amount'] . ' Confetti Bits';
				break;
			case ( intval( $paged_transaction['amount'] ) == 1 ):
				$amount_entry = 'received ' . $paged_transaction['amount'] . ' Confetti Bit';
				break;
		}

		echo sprintf(
			'<div class="cb-log-row">
	<span class="cb-log-row-item cb-log-date">%s</span>
	<span class="cb-log-row-item cb-log-bits-sent">%s</span>
	<span class="cb-log-row-item cb-log-entry">%s</span>
	</div>',
			$transaction_date,
			$amount_entry,
			$paged_transaction['log_entry'],
		);
	}
}

function cb_transactions_notifications( $data = array() ) {

	if ( empty( $data ) ) {
		return;	
	}

	$item_id			= $data['item_id'];
	$sender_id			= $data['sender_id'];
	$recipient_id		= $data['recipient_id'];
	$component_action	= $data['component_action'];
	$amount				= $data['amount'];
	$log_entry			= $data['log_entry'];

	switch ( $component_action ) {

		case ( 'cb_bits_request' ) :

			bp_notifications_add_notification(
				array(
					'user_id'           => $item_id,
					'item_id'           => $sender_id,
					'secondary_item_id' => $recipient_id,
					'component_name'    => 'confetti_bits',
					'component_action'  => $component_action,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
					'allow_duplicate'	=> true,
				)
			);

			cb_bits_request_email_notification( 
				array(
					'recipient_id'	=> $item_id,
					'sender_id'		=> $sender_id,
					'request_item'	=> $log_entry,
				) 
			);

			cb_bits_request_email_notification( 
				array(
					'recipient_id'	=> 5,
					'sender_id'		=> $sender_id,
					'request_item'	=> $log_entry,
				) 
			);
			break;

		case ( 'cb_send_bits' ) :

			bp_notifications_add_notification(
				array(
					'user_id'           => $recipient_id,
					'item_id'           => $sender_id,
					'secondary_item_id' => $sender_id,
					'component_name'    => 'confetti_bits',
					'component_action'  => $component_action,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
					'allow_duplicate'	=> true,
				)
			);
			break;

		case ( 'cb_activity_bits' ) :

			bp_notifications_add_notification(
				array(
					'user_id'           => $recipient_id,
					'item_id'           => $amount,		// this can be any number i think
					'secondary_item_id' => $sender_id,	// this is the profile pic
					'component_name'    => 'confetti_bits',
					'component_action'  => $component_action,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
				)
			);
			break;

		default :

			bp_notifications_add_notification(
				array(
					'user_id'           => $recipient_id,
					'item_id'           => $sender_id,
					'secondary_item_id' => $recipient_id,
					'component_name'    => 'confetti_bits',
					'component_action'  => $component_action,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
				)
			);

	}

	cb_update_total_bits( $recipient_id );

}
add_action( 'cb_transactions_after_send', 'cb_transactions_notifications' );
