<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');
//Confirmation Page Template



if(isset($_REQUEST['is_donation']) && $_REQUEST['is_donation'] == 'true'){
	//if this is a confirmation page for a single donation...


	//$donation_amount = do_shortcode('[EE_ANSWER q="11" a="'.$attendee_id.'"]');
	$donation_amount = $_REQUEST['donation_amount'];

	//echo "DEBUG: updating price in attendee table for attendee_id $attendee_id to ".number_format($donation_amount,2)."<br />";

	//update the attendee record with the donation amount given...
	$wpdb->update(
		EVENTS_ATTENDEE_TABLE,
		array(
		'orig_price' => number_format($donation_amount,2),
		'final_price' => number_format($donation_amount,2),
		'total_cost' => number_format($donation_amount,2)
		),
		array( 'id' => $attendee_id )
	);


?>

	<div class="espresso_payment_overview" >
	  <h2 class="title">
			<?php _e('Donation Overview', 'event_espresso'); ?>
	  </h2>
		<div>


		<h3><?php echo $fname ?> <?php echo $lname ?>,</h3>

		<div class="event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p class="instruct">
				<?php _e('Your donation is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>

		<div class="event-detail">
			<span class="event-detail-label"><?php _e('Amount due: ', 'event_espresso'); ?></span>
			<span class="event-detail-value"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($donation_amount,2); ?></span>
		</div>

		<div class="event-detail">
			<span class="event-detail-label"><?php _e('Your Donation ID: ', 'event_espresso'); ?></span>
			<span class="event-detail-value"><?php echo $registration_id ?></span>
		</div>

		<p>
			<?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?>
		</p>


		</div><!-- / .event-data-display -->
	</div><!-- / .event-display-boxes -->




















	<?php
}
else{
?>
<div class="espresso_payment_overview" >
	<h2 class="title">
		<?php _e('Payment Overview', 'event_espresso'); ?>
	</h2>
	<div>
<?php
	if ( $total_cost == 0 ) {
		unset($_SESSION['espresso_session']['id']);
?>
		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>

		<div class="event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p class="instruct">
				<?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
	    			<b><?php echo stripslashes_deep($event_name) ?></b>
			</p>
		</div>
	  	<p>
			<span class="section-title"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span> <?php echo $registration_id ?>
	 	</p>
	  	<p class="instruct">
			<?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
	  	</p>

<?php }else{ ?>

		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>

		<div class="event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p class="instruct">
				<?php _e('Your registration is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>

	  	<p>
			<span class="event-detail-label"><?php _e('Amount due: ', 'event_espresso'); ?></span>
			<span class="event-detail-value"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($total_cost,2); ?></span>
		</p>

		<p>
			<span class="event-detail-label"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span>
			<span class="event-detail-value"><?php echo $registration_id ?></span>
		</p>

	  	<p>
			<?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?>
		</p>

<?php
}
?>

	</div><!-- / .event-data-display -->
</div><!-- / .event-display-boxes -->
<?php

}
?>