<?php
/*
 * Confetti Bits Flash Messages
 * @since Version 1.0.0
 * 
 * */
/*---------- Set the message to show up on the GET request ----------*/
		
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		if(isset($_SESSION['submitMessage'])) {
			$submitMessage = $_SESSION['submitMessage'];
			unset($_SESSION['submitMessage']);
			?>
<div class="confetti-bits-module unstretch-it center-it error">
			<?php echo $submitMessage; ?>	
</div>
<?php
		}
	}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
		if(isset($_SESSION['submitMessage'])) {
			$submitMessage = $_SESSION['submitMessage'];
			unset($_SESSION['submitMessage']);
			?>
<div class="submission-message-popup">
			<?php echo $submitMessage; ?>	
</div>
<?php
		}
	}