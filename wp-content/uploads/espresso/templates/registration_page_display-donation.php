<?php
//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
?>
<div id="event_espresso_registration_form" class="event-display-boxes ui-widget donation-registration">
	
<?php
$ui_corner = 'ui-corner-all'; 
//This tells the system to hide the event title if we only need to display the registration form.
if ($reg_form_only == false) { 
?>
	<h3 class="event_title ui-widget-header ui-corner-top" id="event_title-<?php echo $event_id; ?>">
		<?php echo $event_name ?> <?php echo $is_active['status'] == 'EXPIRED' ? ' - <span class="expired_event">Event Expired</span>' : ''; ?> <?php echo $is_active['status'] == 'PENDING' ? ' - <span class="expired_event">Event is Pending</span>' : ''; ?> <?php echo $is_active['status'] == 'DRAFT' ? ' - <span class="expired_event">Event is a Draft</span>' : ''; ?>
	</h3>
	
<?php 
	$ui_corner = 'ui-corner-bottom';
}
?>
 <div class="event_espresso_form_wrapper event-data-display ui-widget-content <?php echo $ui_corner ?>">

	<?php
	if(!empty($event_meta['event_thumbnail_url'])){
	echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, !empty($event_meta['event_thumbnail_url']) ? 
	$event_meta['event_thumbnail_url'] : '');
	}
	?>

	<?php if ($display_desc == "Y") { //Show the description or not ?>
	<p class="section-title">
		<?php _e('Description:', 'event_espresso') ?>
	</p>
	<div class="event_description clearfix">
		<?php echo espresso_format_content($event_desc); //Code to show the actual description. The Wordpress function "wpautop" adds formatting to your description.   ?>
		
	</div>
	<?php
	}//End display description

	switch ($is_active['status']) {
	
		case 'EXPIRED': 
		
			//only show the event description.
			echo '<h3 class="expired_event">' . __('This event has passed.', 'event_espresso') . '</h3>';
			break;

		case 'REGISTRATION_CLOSED': 
		
			//only show the event description.
			// if todays date is after $reg_end_date
?>
	<div class="event-registration-closed event-messages ui-corner-all ui-state-highlight">
		<span class="ui-icon ui-icon-alert"></span>
		<p class="event_full">
			<strong>
				<?php _e('We are sorry but registration for this event is now closed.', 'event_espresso'); ?>
			</strong>
		</p>
		<p class="event_full">
			<strong>
				<?php  _e('Please ', 'event_espresso');?><a href="contact" title="<?php  _e('contact us ', 'event_espresso');?>"><?php  _e('contact us ', 'event_espresso');?></a><?php  _e('if you would like to know if spaces are still available.', 'event_espresso'); ?>
			</strong>
		</p>
	</div>
<?php
			break;

		case 'REGISTRATION_NOT_OPEN': 
			//only show the event description.
			// if todays date is after $reg_end_date
			// if todays date is prior to $reg_start_date
?>
	<div class="event-registration-pending event-messages ui-corner-all ui-state-highlight">
		<span class="ui-icon ui-icon-alert"></span>
			<p class="event_full">
				<strong>
					<?php _e('We are sorry but this event is not yet open for registration.', 'event_espresso'); ?>
				</strong>
			</p>
			<p class="event_full">
				<strong>
					<?php echo  __('You will be able to register starting ', 'event_espresso') . ' ' . event_espresso_no_format_date($reg_start_date, 'F d, Y'); ?>
				</strong>
			</p>
		</div>
<?php
		break;

		default: //This will display the registration form
?>
	<div class="event_espresso_form_wrapper">
		<form method="post" action="<?php echo get_permalink( $event_page_id );?>" id="registration_form">
	<?php
				
			//This hides the date/times and location when usign custom post types or the ESPRESSO_REG_FORM shortcode
				if ( $reg_form_only == false ){	
						
					/* Display the address and google map link if available */
					if ($location != '' && (empty($org_options['display_address_in_regform']) || $org_options['display_address_in_regform'] != 'N')) {

					}
					do_action('action_hook_espresso_social_display_buttons', $event_id);

				}

			// * * This section shows the registration form if it is an active event * *

				if ($display_reg_form == 'Y') {

?>

						<p class="event_prices" style="display: none;">
							<?php do_action( 'espresso_price_select', $event_id );?>
						</p>

				<div id="event-reg-form-groups">
				
					
	<?php
					//Outputs the custom form questions. This function can be overridden using the custom files addon
					echo event_espresso_add_question_groups( $question_groups, '', NULL, FALSE, array( 'attendee_number' => 1 ), 'ee-reg-page-questions' );
	?>
				</div>
				<input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id; ?>" value="post_attendee">
				<input type="hidden" name="event_id" id="event_id-<?php echo $event_id; ?>" value="<?php echo $event_id; ?>">
				<input type="hidden" name="num_people" id="num_people-<?php echo $event_id; ?>" value="1">
				<input type="hidden" name="use_coupon[<?php echo $event_id; ?>]" value="<?php echo $use_coupon_code; ?>" />
				<input type="hidden" name="use_groupon[<?php echo $event_id; ?>]" value="<?php echo $use_groupon_code; ?>" />
				<input type="hidden" name="is_donation" value="true" />
	<?php
					
					wp_nonce_field('reg_nonce', 'reg_form_nonce');
					
					//Recaptcha portion
					if ( $org_options['use_captcha'] == 'Y' && empty($_REQUEST['edit_details']) && ! is_user_logged_in()) {
					
						if ( ! function_exists('recaptcha_get_html')) {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/recaptchalib.php');
						}
						# the response from reCAPTCHA
						$resp = null;
						# the error code from reCAPTCHA, if any
						$error = null;
	?>
				<p class="event_form_field" id="captcha-<?php echo $event_id; ?>">
					<?php _e('Anti-Spam Measure: Please enter the following phrase', 'event_espresso'); ?>
					<?php echo recaptcha_get_html($org_options['recaptcha_publickey'], $error, is_ssl() ? true : false); ?>
				</p>
					
	<?php 
					} 
					//End use captcha  
	?>
				<p class="event_form_submit" id="event_form_submit-<?php echo $event_id; ?>">
					<input class="btn_event_form_submit ui-button ui-button-big ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all" id="event_form_field-<?php echo $event_id; ?>" type="submit" name="Submit" value="<?php _e('Submit', 'event_espresso'); ?>">
				</p>
				
	<?php } ?>

	    </form>
	</div>
	
<?php 
				break;
				
			}
			//End Switch statement to check the status of the event

		if (isset($ee_style['event_espresso_form_wrapper_close'])) {
			echo $ee_style['event_espresso_form_wrapper_close']; 
		}			
?>
<p class="edit-link-footer"><?php echo espresso_edit_this($event_id) ?></p>
</div>
</div>