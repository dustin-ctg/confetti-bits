<?php
/*
 * 
 * Confetti Bits Member Dashboard
 * @since Version 1.0.0
 * 
 */

?>

<div class="confetti-bits-wrapper">
	<div class="confetti-bits-module unstretch-it">
		<h4>Current Leaderboard:</h4>
		<div class="confetti-bits-leaderboard">
			<?php	echo do_shortcode('[mycred_leaderboard number=5 current=1 total=1 wrap="div"]') ;

			?>			
		</div>
<?php			/*	mycred_get_leaderboard($args = array(
'number' => '5',
	'order'       => 'DESC',
			'user_fields' => 'user_login,display_name,user_email,user_nicename,user_url',
			'offset'      => 0,
			'type'        => 'mycred_default',
			'template'    => '#%ranking% %user_profile_link% %cred_f%'
	
));*/?>
	</div>
	
	<div class="confetti-bits-module">
		<div>
			<?php	echo do_shortcode(	'[mycred_history user_id="current" show_user=1 number=5 pagination=3]' ); ?>
		</div>			
	</div>

</div>
<div class="confetti-bits-wrapper">
<?php get_template_part('confetti-bits-test/template-parts/content','transfer-member-search'); ?>
<?php get_template_part('confetti-bits-test/template-parts/content','transfer-form'); ?>
</div>
<div style="margin:0;color:#dbb778;"><?php echo do_shortcode('[mycred_transfer placeholder="scott" show_balance=1 balance_label="Confetti Bits:" show_limit=1 recipient_label="Tag Name"]'); ?></div>



<?php 