<?php
/*
 * Confetti Bits Transfer Form
 * @since Version 1.0.0
 * 
 */

if(isset($_POST['transfer_submitted'])) {


	// If the id is empty, throw an error, else set the id for the point add function
	if(trim($_POST['transfer_user_id']) === '') {
		$transfer_user_id = '';
		$transfer_user_idError = 'Please select someone to award bits to.';
		$hasError = true;
		$formSuccess = false;
	} else {
		$identifier = trim($_POST['transfer_user_id']);
		$transfer_user_id = mycred_get_user_id($identifier);	
	}

	// If the amount is empty, throw an error, else set the amount for the point add function
	if(trim($_POST['transfer_amount']) === '') {
		$transfer_amount = '';
		$amountError = 'Please enter an amount to send.';
		$hasError = true;
		$formSuccess = false;
	} else {
		$transfer_amount = trim($_POST['transfer_amount']);
	}
		
	// If the log reference is empty, throw an error, else set the log reference for the point add function
	if(trim($_POST['log_ref']) === '') {
		$log_ref = '';
		$log_refError = 'We need to know why you\'re sending bits, pretty please!';
		$hasError = true;
		$formSuccess = false;
	} else {
		$log_ref = trim($_POST['log_ref']);
	}

	/* If there's no error and the post was sent, call the point add function.
	 * Then, set the values back to empty strings.
	 * Then, set the session message in a variable, and P-R-G with the 
	 * stored message.
	 * */

	if( !$hasError && $_POST['transfer_submitted'] ) {

		mycred_subtract('transfer', get_current_user_id(), $transfer_amount, 'Confetti Bits Transfer');
		mycred_add('transfer', $transfer_user_id, $transfer_amount, $log_ref);
//		$_SESSION['submitMessage'] = mycred_display_users_total_balance(get_current_user_id());
	

		$user_id = '';
		$transfer_amount = '';
		$log_ref= '';

		$_SESSION['submitMessage'] = 'We successfully sent the bits!';
		
		header('Location:https://teamctg.com/confetti-bits/', true, 303);
		ob_end_flush();
		exit;



/* Else if there's an error there, let us know by 
 * storing a fail message, and emptying the input values.
 * */
	} else if ( $hasError && $_POST['transfer_submitted'] ) {

		$_SESSION['submitMessage'] = "Something went wrong. Couldn't send the bits!";
		$transfer_user_id = '';
		$transfer_amount = '';
		$log_ref= '';

		
	} else {
		return false;
	}
}
?>

<!-- Start the Award Markup -->
	
<div class="confetti-bits-module">
	<h4 class="confetti-bits-heading">
		Send Bits to Team Members
	</h4>
	<form class="award-form" method="post" name="award_form" autocomplete="off">
		<ul class="award-form-page-section" id="award-form-data">
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="transfer_member_display_name">Team Member</label>
				<input class="award-form-textbox" 
					   type="text" 
					   name="transfer_member_display_name" 
					   id="transfer_member_display_name" 
					   value="" 
					   disabled="true" 
					   placeholder="Select a team member from the search panel">
				
				<?php if($user_idError != '') { ?>
					<span class="error"><?php echo $user_idError; ?></span>
				<?php } ?>
			</li>
			
			<li class="award-form-line">
				<input class="award-form-textbox" 
					   type="hidden" 
					   name="transfer_user_id" 
					   id="transfer_user_id" 
					   value="" 
					   placeholder="">
			</li>
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="log_ref" >Log Reference</label>
				<input  type="text"
						class="award-form-textbox" 
						name="log_ref" 
						id="log_ref" 
						placeholder="Let us know what it's for!">
				
				<?php if($log_refError != '') { ?>
					<span class="error"><?php echo $log_refError; ?></span>
				<?php } ?>
			</li>
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="transfer_amount" >Amount to Send</label>
				<input class="award-form-textbox" 
					   type="text" 
					   name="transfer_amount" 
					   id="transfer_amount" 
					   placeholder="How many bits do you want to send?"
					   value="">
					   

				<?php if($amountError != '') { ?>
					<span class="error"><?php echo $amountError; ?></span>
				<?php } ?>
			</li>
				
			<li class="award-form-line">
				<div class="submit-container">
					<input class="award-submit" 
						   type="submit" 
						   action="content-transfer-form.php" 
						   value="Submit">
				</div>
			</li>
			</ul>
			<input type="hidden" name="transfer_submitted" id="transfer_submitted" value="true" />
		</form>

	</div><!-- End of Module -->

<?php 