<?php
/*
 * 
 * Confetti Bits Award Form
 * @since Version 1.0.0
 * 
 */

/*---------- Start Award Form Functions ----------*/


/*---------- Uses the hidden input value on submit as a boolean ----------*/

if(isset($_POST['submitted'])) {


	// If the id is empty, throw an error, else set the id for the point add function
	if(trim($_POST['user_id']) === '') {
		$user_id = '';
		$user_idError = 'Please select someone to award bits to.';
		$hasError = true;
		$formSuccess = false;
	} else {
		$identifier = trim($_POST['user_id']);
		$user_id = mycred_get_user_id($identifier);	
	}

	// If the amount is empty, throw an error, else set the amount for the point add function
	if(trim($_POST['award_amount']) === '') {
		$amount = '';
		$amountError = 'Please enter an amount to send.';
		$hasError = true;
		$formSuccess = false;
	} else {
		$amount = trim($_POST['award_amount']);
	}
		
	// If the log reference is empty, throw an error, else set the log reference for the point add function
	if(trim($_POST['log_ref']) === '') {
		$log_ref = '';
		$log_refError = 'We need to know what that award was for, pretty please!';
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

	if( !$hasError && $_POST['submitted'] ) {

		mycred_add('award', $user_id, $amount, $log_ref);

		$user_id = '';
		$amount = '';
		$log_ref= '';

		$_SESSION['submitMessage'] = 'We successfully sent the bits!';
			
		header('Location:https://teamctg.com/confetti-bits/', true, 303);
		ob_end_flush();
		exit;



/* Else if there's an error there, let us know by 
 * storing a fail message, and emptying the input values.
 * */
	} else if ( $hasError && $_POST['submitted'] ) {

		$_SESSION['submitMessage'] = "Something went wrong. Couldn't send the bits!";
		$user_id = '';
		$amount = '';
		$log_ref= '';

		
	} else {
		return false;
	}
}
?>

<!-- Start the Award Markup -->
	
<div class="confetti-bits-module">
	<h4 class="confetti-bits-heading">
		Award Points to Team Members
	</h4>
	<form class="award-form" method="post" name="award_form" autocomplete="off">
		<ul class="award-form-page-section" id="award-form-data">
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="member_display_name">Team Member</label>
				<input class="award-form-textbox" 
					   type="text" 
					   name="member_display_name" 
					   id="member_display_name" 
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
					   name="user_id" 
					   id="user_id" 
					   value="" 
					   placeholder="">
			</li>
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="log_ref" >Log Reference</label>
				<select class="award-form-textbox" 
						name="log_ref" 
						id="log_ref" 
						placeholder="">
					<option disabled selected >--- Select an Option ---</option>
					<option value="Attending Company Party/Event" 
							class="logEntry" 
							data-award-value="20">Attending Company Party/Event</option>
					<option value="Member of Contest-Winning Office" 
							class="logEntry" 
							data-award-value="10">Member of Contest-Winning Office</option>
					<option value="Contest Winner" 
							class="logEntry" 
							data-award-value="10">Contest Winner</option>
					<option value="Spirit Day Participation" 
							class="logEntry" 
							data-award-value="5">Spirit Day Participation</option>
					<option value="Monthly Meeting" 
							class="logEntry" 
							data-award-value="5">Monthly Meeting</option>
					<option value="Amanda's Workshop" 
							class="logEntry" 
							data-award-value="5">Amanda's Workshop</option>
					<option value="Confetti Captain Participation" 
							class="logEntry" 
							data-award-value="5">Confetti Captain Participation</option>
					<option value="Office Decoration Participation" 
							class="logEntry" 
							data-award-value="5">Office Decoration Participation</option>
					<option value="Monthly Culture Club Planners" 
							class="logEntry" 
							data-award-value="5">Monthly Culture Club Planners</option>
					<option value="All other participation" 
							class="logEntry" 
							data-award-value="5">All other participation</option>
					<option value="Leadership Award" 
							class="logEntry" 
							data-award-value="5">Leadership Award</option>
				</select>
				
				<?php if($log_refError != '') { ?>
					<span class="error"><?php echo $log_refError; ?></span>
				<?php } ?>
			</li>
				
			<li class="award-form-line">
				<label class="award-form-label-top" for="award_amount" >Amount to Send</label>
				<input class="award-form-textbox" 
					   type="text" 
					   name="award_amount" 
					   id="award_amount" 
					   placeholder="Please select a log reference first" 
					   value=""
					   readonly="true">
					   

				<?php if($amountError != '') { ?>
					<span class="error"><?php echo $amountError; ?></span>
				<?php } ?>
			</li>
				
			<li class="award-form-line">
				<div class="submit-container">
					<input class="award-submit" 
						   type="submit" 
						   action="content-confetti-bits-award-form.php" 
						   value="Submit">
				</div>
			</li>
			</ul>
			<input type="hidden" name="submitted" id="submitted" value="true" />
		</form>

	</div><!-- End of Module -->

<?php 