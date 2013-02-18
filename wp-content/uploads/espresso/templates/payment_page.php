<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	
//Confirmation Page Template


if(isset($_REQUEST['is_donation']) && $_REQUEST['is_donation'] == 'true'){
	//if this is a confirmation page for a donation...

	$donation_amount = do_shortcode('[EE_ANSWER q="11" a="'.$attendee_id.'"]');

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

	<div class="espresso_payment_overview event-display-boxes ui-widget" >
	  <h3 class="section-heading ui-widget-header ui-corner-top">
			<?php _e('Donation Overview', 'event_espresso'); ?>
	  </h3>
		<div class="event-data-display ui-widget-content ui-corner-bottom" >


		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>
	  
		<div class="event-messages ui-state-highlight">
			<span class="ui-icon ui-icon-alert"></span>
			<p class="instruct">
				<?php _e('Your donation is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>
		
		<p>
			<span class="event_espresso_name section-title"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="event_espresso_value"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($donation_amount,2); ?></span>
		</p>
		
		<p>
			<span class="section-title"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span><?php echo $registration_id ?>
		</p>
		
		<p>
			<?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?>
		</p>

		
		</div><!-- / .event-data-display -->
	</div><!-- / .event-display-boxes -->




















	<?php
}
else{
?>
<div class="espresso_payment_overview event-display-boxes ui-widget" >
  <h3 class="section-heading ui-widget-header ui-corner-top">
		<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
	<div class="event-data-display ui-widget-content ui-corner-bottom" >
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
			<span class="event_espresso_name section-title"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="event_espresso_value"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($total_cost,2); ?></span>
		</p>
	  	
		<p>
			<span class="section-title"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span><?php echo $registration_id ?>
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