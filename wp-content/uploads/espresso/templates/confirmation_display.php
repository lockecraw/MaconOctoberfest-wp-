<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');

/* WARNING MODIFYING THIS AT YOUR OWN RISK  */
/* Payments template page. Currently this just shows the registration data block.*/
//This page gets all of the varaibles from includes/process-registration/payment_page.php
//Payment confirmation block

if(isset($_REQUEST['is_donation']) && $_REQUEST['is_donation'] == 'true'){
	//if this is a confirmation page for a donation...


	//$donation_amount = do_shortcode('[EE_ANSWER q="11" a="'.$attendee_id.'"]');
	$donation_amount = $_REQUEST['donation_amount'];

	$attendee_num = 1;

	?>
		<form id="form1" name="form1" method="post" action="<?php echo home_url()?>/?page_id=<?php echo $event_page_id?>">
			<div class="event-conf-block" >
			<h2 class="title">
				<?php _e('Verify Donation','event_espresso'); ?>
			</h2>
			<div >
				<table  id="">
					<tr>
						<th scope="row" class="header event-detail-label" style="width: 100px;">
							<?php _e('For:','event_espresso'); ?>
						</th>
						<td>
							<span class="event_espresso_value"><?php echo stripslashes_deep($event_name)?></span>
						</td>
					</tr>



					<tr>
						<th scope="row" class="header event-detail-label">
							<?php _e('Donor Name:','event_espresso'); ?>
						</th>
						<td  valign="top">
							<span class="event_espresso_value"><?php echo stripslashes_deep($attendee_name)?> (<?php echo $attendee_email?>)
							<?php
							//echo '<a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$attendee_id.'&amp;regevent_action=edit_attendee&amp;primary='.$attendee_id.'&amp;event_id='.$event_id.'&amp;attendee_num='.$attendee_num.'">'. __('Edit', 'event_espresso').'</a>';  // removed p_id='.$p_id.'&amp; coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;

							//Create additional attendees
							$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;
							$sql .= " WHERE registration_id = '" . espresso_registration_id( $attendee_id ) . "' AND id != '".$attendee_id."' ";
							//echo $sql;
							$x_attendees = $wpdb->get_results( $sql, ARRAY_A );

							if ( $wpdb->num_rows > 0 ) {
								foreach ($x_attendees as $x_attendee) {

									$attendee_num++;
									//echo $attendee_num;
									//print_r($x_attendees);
									echo "<br/>" . $x_attendee['fname'] . " " . $x_attendee['lname'] . " ";
									if ($x_attendee['email'] != '') {
										echo "(" . $x_attendee['email']  . ") ";
									}
									//Create edit link
									echo '<a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$x_attendee['id'].'&amp;regevent_action=register&amp;form_action=edit_attendee&amp;primary='.$attendee_id.'&amp;p_id='.$attendee_id.'&amp;attendee_num='.$attendee_num.'&amp;event_id='.$event_id.'">'. __('Edit', 'event_espresso').'</a>'; // removed coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;
									//Create delete link
									echo ' | <a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$x_attendee['id'].'&amp;regevent_action=register&amp;form_action=edit_attendee&amp;primary='.$attendee_id.'&amp;delete_attendee=true&amp;p_id='.$attendee_id.'&amp;event_id='.$event_id.'">'. __('Delete', 'event_espresso').'</a>'; // removed coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;
								}
							}
							?>
							</span>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="header event-detail-label">
							<?php _e('Total Donation:','event_espresso'); ?>
						</th>
						<td>
							<span class="event_espresso_value"><?php echo "$".number_format($donation_amount,2);  //echo $event_discount_label;?></span>
						</td>
					</tr>
				</table>

			</div>

			<p class="espresso_confirm_registration">
				<input class="event-button" type="submit" name="confirm" id="confirm" value="<?php _e('Confirm Donation', 'event_espresso'); ?>&nbsp;&raquo;" />
			</p>



			<?php /* This form builds the confirmation buttons */?>
			<input name="confirm_registration" id="confirm_registration" type="hidden" value="true" />
			<input type="hidden" name="attendee_id" id="attendee_id" value="<?php echo $attendee_id ?>" />
			<input type="hidden" name="registration_id" id="registration_id" value="<?php echo $registration_id ?>" />
			<input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id;?>" value="confirm_registration">
			<input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>">
			<input type="hidden" name="is_donation" id="is_donation-<?php echo $event_id;?>" value="true">
			<input type="hidden" name="donation_amount" id="donation_amount-<?php echo $event_id;?>" value="<?php echo $donation_amount; ?>">
			<?php wp_nonce_field('reg_nonce', 'reg_form_nonce'); ?>

		</div>
	</form>
	<?php





}
else{

	$attendee_num = 1;

	?>
		<form id="form1" name="form1" method="post" action="<?php echo home_url()?>/?page_id=<?php echo $event_page_id?>">
			<div class="event-conf-block" >
			<h2 class="title">
				<?php _e('Verify Registration','event_espresso'); ?>
			</h2>
			<div >
				<table   id="event_espresso_attendee_verify">
					<tr>
						<th scope="row" class="header">
							<?php _e('Event Name:','event_espresso'); ?>
						</th>
						<td>
							<span class="event_espresso_value"><?php echo stripslashes_deep($event_name)?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" class="header">
							<?php echo empty($price_type) ? __('Price per attendee:','event_espresso') : __('Type/Price per attendee:','event_espresso'); ?>
						</th>
						<td>
							<span class="event_espresso_value"><?php echo empty($price_type) ? $org_options['currency_symbol'] . number_format($final_price,2) : stripslashes_deep($price_type) . ' / ' .$org_options['currency_symbol'].number_format($final_price,2);?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" class="header">
							<?php _e('Attendee Name:','event_espresso'); ?>
						</th>
						<td  valign="top">
							<span class="event_espresso_value"><?php echo stripslashes_deep($attendee_name)?> (<?php echo $attendee_email?>)
							<?php
							echo '<a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$attendee_id.'&amp;regevent_action=edit_attendee&amp;primary='.$attendee_id.'&amp;event_id='.$event_id.'&amp;attendee_num='.$attendee_num.'">'. __('Edit', 'event_espresso').'</a>';  // removed p_id='.$p_id.'&amp; coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;

							//Create additional attendees
							$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;
							$sql .= " WHERE registration_id = '" . espresso_registration_id( $attendee_id ) . "' AND id != '".$attendee_id."' ";
							//echo $sql;
							$x_attendees = $wpdb->get_results( $sql, ARRAY_A );

							if ( $wpdb->num_rows > 0 ) {
								foreach ($x_attendees as $x_attendee) {

									$attendee_num++;
									//echo $attendee_num;
									//print_r($x_attendees);
									echo "<br/>" . $x_attendee['fname'] . " " . $x_attendee['lname'] . " ";
									if ($x_attendee['email'] != '') {
										echo "(" . $x_attendee['email']  . ") ";
									}
									//Create edit link
									echo '<a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$x_attendee['id'].'&amp;regevent_action=register&amp;form_action=edit_attendee&amp;primary='.$attendee_id.'&amp;p_id='.$attendee_id.'&amp;attendee_num='.$attendee_num.'&amp;event_id='.$event_id.'">'. __('Edit', 'event_espresso').'</a>'; // removed coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;
									//Create delete link
									echo ' | <a href="'.home_url().'/?page_id='.$event_page_id.'&amp;registration_id='.$registration_id.'&amp;id='.$x_attendee['id'].'&amp;regevent_action=register&amp;form_action=edit_attendee&amp;primary='.$attendee_id.'&amp;delete_attendee=true&amp;p_id='.$attendee_id.'&amp;event_id='.$event_id.'">'. __('Delete', 'event_espresso').'</a>'; // removed coupon_code='.$coupon_code.'&amp;groupon_code='.$groupon_code.'&amp;
								}
							}
							?>
							</span>
						</td>
					</tr>
					<?php if ($attendee_num > 1) { ?>
						<tr>
							<th scope="row" class="header">
								<?php _e('Total Registrants:','event_espresso'); ?>
							</th>
							<td>
								<span class="event_espresso_value"><?php echo (int)$attendee_num; ?></span>
							</td>
						</tr>
					<?php } ?>
					<tr valign="top">
						<th scope="row" class="header">
							<?php _e('Total Price:','event_espresso'); ?>
						</th>
						<td>
							<span class="event_espresso_value"><?php echo $display_cost;  //echo $event_discount_label;?></span>
						</td>
					</tr>
				</table>

			</div>


			<?php if ($display_questions != '') { ?>

			<div id="additional-conf-info">
					<h3><?php echo stripslashes_deep($attendee_name)?></h3>
					<div id="additional-conf-info">
						<table id="event_espresso_attendee_verify_questions">
						<?php foreach ($questions as $question) { ?>
							<tr>
								<th scope="row" class="header">
									<?php echo stripslashes( html_entity_decode( $question->question, ENT_QUOTES, 'UTF-8' )); ?>
								</th>
								<td>
									<span class="event_espresso_value"><?php echo stripslashes( html_entity_decode( $question->answer, ENT_QUOTES, 'UTF-8' )); ?></span>
								</td>
							</tr>
						<?php } ?>
						</table>
					</div>
					<!-- / .event-data-display -->
				</div>
				<!-- / .event-display-boxes -->

			<?php	} ?>

			<p class="espresso_confirm_registration">
				<input class="event-button" type="submit" name="confirm2" id="confirm2" value="<?php _e('Confirm Registration', 'event_espresso'); ?>&nbsp;&raquo;" />
			</p>

			<?php /* This form builds the confirmation buttons */?>
			<input name="confirm_registration" id="confirm_registration" type="hidden" value="true" />
			<input type="hidden" name="attendee_id" id="attendee_id" value="<?php echo $attendee_id ?>" />
			<input type="hidden" name="registration_id" id="registration_id" value="<?php echo $registration_id ?>" />
			<input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id;?>" value="confirm_registration">
			<input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>">
			<?php wp_nonce_field('reg_nonce', 'reg_form_nonce'); ?>

		</div>
	</form>
	<?php
}
?>