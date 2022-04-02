<?php
/* 
 * 
 * Confetti Bits Import Module 
 * @since Version 1.0.0
 * 
 */ 

?>
<div class="confetti-bits-module">
<?php
	
	/*---------- Start the confetti bits import class ----------*/
	
	class Confetti_Bits_Import  {

	var	$id            = '';
	var $delimiter     = '';
	var $posts         = array();
	var $imported      = 0;
	var $skipped       = 0;


/* Our callback function for the WP_Importer to use.
 * */

		 public function load_bits() {

			 /*---------- Check if the post was submitted ----------*/
			if ( isset ( $_POST['bits_imported'] ) ) {
				
				if ( ! function_exists( 'wp_handle_upload' ) ) {
    				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				
				/*---------- Run the handle sequence ----------*/				
				$file = wp_import_handle_upload();
				$this->id = (int) $file['id'];

						/*---------- If it's ok, run this ----------*/
						if ( isset ( $file['error'] ) ) {
							
							$_SESSION['submitMessage'] = $file['error'];
							$file = '';
							$_POST['import'] = '';
							$submitMessage = $_SESSION['submitMessage'];
							$this->greet();
						} else if ( ! isset ( $file['error'] ) ) {

							/*---------- If the handle sequence ran true, check for attachment id ----------*/
							if ( $this->id ) {
								$file = get_attached_file( $this->id );
							}
							
							/*---------- If it comes back as a file, import it ----------*/
							if ( $file !== false )	{
								$this->import( $file );
							}
							
						}
	 
			} else {
				/*---------- In any other scenario, just load the base import form ----------*/
					$loaded = true;
					if ( $loaded ) {
						$this->greet();
					}
			}
		}

		/*
		 * UTF-8 encode the data if it isn't UTF-8.
		 * */
		 public function format_data_from_csv( $data, $enc ) {
			return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		}

		/*
		 * Import Function
		 * Handles the actual importing.
		 * */
		
		 public function import( $file ) {
			/*---------- Get the database so we have somewhere for the info to go ----------*/
			global $wpdb;

			$ran        = false;
			$loop       = 0;

			/*---------- Double checking that the file's legit ----------*/
			if ( ! is_file( $file ) ) {

				echo '<div class="error notice notice-error is-dismissible">' . __( 'The file does not exist or could not be read.', 'mycred' ) . '</div>';
				return true;

			} 


			/*---------- Open the file ----------*/
			if ( ( $handle = fopen( $file, "r" ) ) !== false ) {

				/*---------- Calculate the header row of the file so we know how many columns there are ----------*/
				$header        = fgetcsv( $handle, 0, "," );
				$no_of_columns = sizeof( $header );

				/*---------- Make sure we have the correct number of columns ----------*/
				if ( $no_of_columns == 3 || $no_of_columns == 4 ) {

					/*---------- Begin import loop ----------*/
					while ( ( $row = fgetcsv( $handle, 0, "," ) ) !== false ) {

						/*---------- Set log entry variable ----------*/
						$log_entry = '';
						/*---------- If there are 3 columns, it's just the id, amount, and point type ----------*/
						if ( $no_of_columns == 3 ) {
							list ( $identification, $balance, $point_type ) = $row;
						/*---------- Otherwise, it's the id, amount, point type, and a log entry ----------*/
						} else {
							list ( $identification, $balance, $point_type, $log_entry ) = $row;	
						}
						/*---------- Attempt to identify the user ----------*/
						$user_id = mycred_get_user_id( $identification );

						/*---------- Skip if you can't find them ----------*/
						if ( $user_id === false ) {
							$this->skipped ++;
							continue;
						}

						/* Look for the point type.
						 * If there isn't a match, make that column the log entry,
						 * and set the point type to the default.
						 * */
						
						if ( ! mycred_point_type_exists( $point_type ) ) {

							if ( $point_type != '' ) {
								$log_entry = $point_type;
							}
							$point_type = MYCRED_DEFAULT_TYPE_KEY;
						}
						
						/* Store the data set of id, amount, 
						 * point type, and log entry using mycred's
						 * handy-dandy function. Set the method
						 * for the add function and run it on a loop.
						 * */
						
						$bits = mycred( $point_type );
						$method = 'add';

						// If a log entry should be added with the import
						if ( ! empty( $log_entry ) ) {
							$bits->add_to_log( 'the_bits', $user_id, $balance, $log_entry );
						}

						if ( $method == 'add' ) {
							// Add to the balance
							$bits->update_users_balance( $user_id, $balance );
						} 

						$loop ++;
						$this->imported++;

					}
					
					/*---------- If we found the right number of columns,
					 * and the rest of that stuff worked, 
					 * give us a round of applause. ----------*/
					$ran = true;

				} else {
					
					/*---------- Otherwise, tell us that our script is broken. ----------*/
					$_SESSION['submitMessage'] = __( '<div>Invalid CSV file. Make sure there are only 3 or 4 columns containing:
					<ol>
						<li class="left-align-it">Email address</li>
						<li class="left-align-it">Amount of points</li>
						<li class="left-align-it">Point type (if applicable)</li>
						<li class="left-align-it">Log entry</li>
					</ol></div>');
					
					$this->greet();
				}
				/*---------- Close the file ----------*/
				fclose( $handle );

			}

			/*---------- If we made it out alive, tell us what cool things happened ----------*/
			if ( $ran ) {

				$_SESSION['submitMessage'] = sprintf( __( '<div>Import complete - Successfully imported: <strong>%d</strong> | Skipped: <strong>%d</strong></div>'), $this->imported, $this->skipped );
				$_POST['import'] = '';
				$file = '';
				$this->greet();
				header('Location: https://teamctg.com/confetti-bits', true, 303);
				ob_end_flush();
				exit;
				
			}

		}


		/* 
		 * Greet Screen
		 * Here's the default load screen. Gets replaced with 
		 * import shenanigans when a user tries to import a file.
		 * */
		
		 public function greet() {
			 
			$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size       = size_format( $bytes );
			$upload_dir = wp_upload_dir();
			
			if ( ! empty( $upload_dir['error'] ) ) :

?>
<div class="error notice notice-error"><p><?php echo $upload_dir['error']; ?></p></div>
<?php
			else : 
	?>
	<h4 class="confetti-bits-heading">
		Import a List of Users
	</h4>

	<form enctype="multipart/form-data" id="import-upload-form" method="post" >
		<ul class="award-form-page-section" id="award-form-data">
			<li class="award-form-line">
				<label class="award-form-label-top" for="import">Please choose a .csv file from your computer</label>
				<input type="file" id="import" name="import" accept=".csv" />
			</li>
			<input type="hidden" name="action" value="save" />
			<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
			<li class="award-form-line">
				<p><?php printf( __( 'Maximum size: %s' ), $size ); ?></p>
			</li>
		</ul>
	<div class="submit">
		<input type="submit" class="button button-primary" value="Import" />
		<input type="hidden" name="bits_imported" value="">
	</div>
</form>
</div>

<?php 	

endif;

	}
		
}
	
	/*---------- A function to build our new importer ----------*/
	function confetti_bits_importer() {
	require( ABSPATH . 'wp-admin/includes/import.php' );

if ( ! class_exists( 'WP_Importer' ) ) {
		$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		if ( file_exists( $class_wp_importer ) )
			require $class_wp_importer;
}
	
	$importer = new Confetti_Bits_Import;
	$importer->load_bits();
}
	
/*---------- Call out the importer ----------*/
confetti_bits_importer();
