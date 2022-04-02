<?php
/* 
 * Confetti Bits Transfer Member Search Panel
 * @since Version 1.0.0
 * 
 */

if ( isset( $_POST['transfer_search_submitted'] ) ) {
	if (trim($_POST['search_terms']) === '') {
		$search_terms = '';
		$search_error = 'We need something to search for! Try entering a first name, last name, or both!';
		$hasError = true;
	} else {				
		$search_terms = $_POST['search_terms'];	
		global $wpdb;
		global $members_template;
		if(isset($_POST['search_terms'])) {
			
			
		if ( ! is_this_dustin() ) {
			$query = new BP_User_Query( array(
				'type' => 'alphabetical',
				'search_terms' => $search_terms,
				'exclude' => get_current_user_id(),
				'search_wildcard' => 'both',
				'count_total' => 'sql_count_found_rows',
				'per_page' => 10
				)
			);
		} else {
			$query = new BP_User_Query( array(
				'type' => 'alphabetical',
				'search_terms' => $search_terms,
				'search_wildcard' => 'both',
				'count_total' => 'sql_count_found_rows',
				'per_page' => 10
				)
			);
		}
			
			$query->__construct();
			$members = $query->results;
		} 
	}
} 

?>


<!-- Start the Search Markup -->

<div class="confetti-bits-module">
	<h4 class="confetti-bits-heading">
		Search for a Team Member to Send Bits
	</h4>
	<form class="transfer-form" method="post" name="team_search" autocomplete="off">
		<ul class="award-form-page-section">
			<li class="award-form-line">
				<label class="award-form-label-top">Team Member Search</label>
				<input type="search" name="search_terms" placeholder="Full name, email, office, anything!">
			</li>
			<li class="award-form-line">
				<div class="search-container">
					<input type="submit" class="award-submit" action="content-transfer-member-search.php" value="Search">
				</div>
			</li>
		</ul>
		<input type="hidden" name="transfer_search_submitted" id="transfer_search_submitted" value="true">
	</form>
	
	<?php 	if ( $search_error != '' ) { ?>
	<span class="error"><?php echo $search_error; ?></span>
	<?php } else if ( isset ( $_POST [ "transfer_search_submitted" ] ) ) {
					
		switch ( true ) {
						
			case ( count ( $members ) === 1 ): ?> 
	
				<h5 class="member-search-results-message"> Alrighty, we got <?php 
					echo count ( $members ) . ' result here for "' . $search_terms . '":'; ?> 
				</h5>
	
				<div class="member-selection-container">
					<?php foreach ( $members as $member ) { ?>
						<div class="memberSelect transfer-member" data-member-id="<?php echo $member->ID  ;  ?>"
							 data-member-display-name="<?php echo $member->display_name; ?>"
							 class="member-data" id="member-data-<?php echo $member->ID ; ?>">
							<?php  $user = new BP_Core_User( $member->ID ); ?>
							
							<div class="search-results-avatar"><?php  echo $user->avatar_thumb ?></div>
							<p class="memberName">
								<?php echo $member->display_name; ?>
							</p>
						</div>
					
					<?php } ; ?>
					
				</div>
<?php
			break;
						
			case ( ( count ( $members ) > 1 )  && ( count ( $members ) < 10 ) ): ?>
	
				<h5 class="member-search-results-message">
					Alrighty, we got <?php echo count($members) . ' results here for "' . $search_terms . '":'; ?>
				</h5>
	
				<div class="member-selection-container">
					<?php foreach ( $members as $member ) { ?>
					<div class="memberSelect transfer-member" data-member-id="<?php echo $member->ID  ;  ?>" 
						 data-member-display-name="<?php echo $member->display_name; ?>" 
						 class="member-data" id="member-data-<?php echo $member->ID ; ?>">
						<?php  $user = new BP_Core_User( $member->ID ); ?>
						<div class="search-results-avatar">
							<?php  echo $user->avatar_thumb ?>
						</div>
						<p class="memberName">
							<?php echo $member->display_name; ?>  
						</p>
					</div>

					<?php } ;?>

				</div>
<?php
						break;
						
				case ( count($members) >= 10 ): ?>
					
					<h5 class="member-search-results-message">
						Alrighty, we got <?php echo 'over 10 results for "' . $search_terms 
						. '". You might want to narrow your search down a bit. Try using someone\'s full name!'; ?>
					</h5>

					<div class="member-selection-container">
						<?php foreach ( $members as $member ) { ?>
							<div class="memberSelect transfer-member" data-member-id="<?php echo $member->ID  ;  ?>"
							data-member-display-name="<?php echo $member->display_name; ?>" 
							class="member-data" id="member-data-<?php echo $member->ID ; ?>">
						<?php  $user = new BP_Core_User( $member->ID ); ?>
						<div class="search-results-avatar"><?php  echo $user->avatar_thumb ?></div>
					<p class="memberName"><?php echo $member->display_name; ?>  </p></div><?php } ;?></div><?php
						break;
						
				default: ?>
	
					<p> 
						<?php 	echo 'We tried searching "' . $search_terms . '", but we didn\'t find anything. :/'; ?>
					</p>
<?php	}
} ?> 
	</div> 
<?php