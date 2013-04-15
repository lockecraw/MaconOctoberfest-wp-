<?php
//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.

?>

<!-- Template: <?php echo __FILE__; ?> -->
<div class="event-registration-container single-event-registration-container single-<?php echo $event_category; ?>">


	<div class="event-content">
		<?php
			if(!empty($event_meta['event_thumbnail_url'])){
				echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, !empty($event_meta['event_thumbnail_url']) ?
				$event_meta['event_thumbnail_url'] : '');
			}
			?>
			<?php if ($reg_form_only == false) { ?>
				<h2 class="eventTitle">Events</h2>
				<hr/>
				<h1 class="eventTitle" id="event_title-<?php echo $event_id; ?>">
					<?php echo $event_name ?> <?php echo $is_active['status'] == 'EXPIRED' ? ' - <span class="expired_event">Event Expired</span>' : ''; ?> <?php echo $is_active['status'] == 'PENDING' ? ' - <span class="expired_event">Event is Pending</span>' : ''; ?> <?php echo $is_active['status'] == 'DRAFT' ? ' - <span class="expired_event">Event is a Draft</span>' : ''; ?>
				</h1>
			<?php } ?>
		<?php


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
				<div class="event-registration-form-wrapper">
					<form method="post" action="<?php echo get_permalink( $event_page_id );?>" id="registration_form">
					<?php
					//This hides the date/times and location when using custom post types or the ESPRESSO_REG_FORM shortcode
					if ( $reg_form_only == false ){
						?>
						<div class="event-details">

							<?php if($event_category != 'donation' && $event_category != 'merchandise'){ ?>
								<div class="event-detail event-detail-date">
									<?php if ($end_date !== $start_date) { ?>
										<span class="event-detail-label">
										<?php _e('Start Date: ', 'event_espresso'); ?>
										</span>
									<?php } else { ?>
										<span class="event-detail-label">
										<?php _e('Date: ', 'event_espresso'); ?>
										</span>
									<?php } ?>
									<span class="event-detail-value">
										<?php echo event_date_display($start_date, get_option('date_format')); ?>
									</span>
									<?php if ($end_date !== $start_date) { ?>
										</div>
										<div class="event-detail event-detail-date">
										<span class="event-detail-label">
											<?php _e('End Date: ', 'event_espresso'); ?>
										</span>
										<span class="event-detail-value">
											<?php echo event_date_display($end_date, get_option('date_format')); ?>
										</span>
									<?php } ?>
									<?php echo apply_filters('filter_hook_espresso_display_ical', $all_meta); ?>
								</div>

								<?php
									//This block of code is used to display the times of an event in either a dropdown or text format.
									if (isset($time_selected) && $time_selected == true) {//If the customer is coming from a page where the time was preselected.
										echo event_espresso_display_selected_time($time_id); //Optional parameters start, end, default
									} else {
										echo event_espresso_time_dropdown($event_id);
									}//End time selected
								?>
							<?php } ?>

							<?php /*  ?>
							<div class="event-detail event-detail-price" <?php if($event_category=='donation'){echo 'style="display:none;"';}?>>
								<?php
								$price_label = '<span class="event-detail-label">'.__('Choose an Option: ', 'event_espresso').'</span>';
								do_action( 'espresso_price_select', $event_id, array('show_label'=>TRUE, 'label'=>$price_label) );
								?>
							</div>
							<?php */ ?>

							<?php if($event_category != 'donation' && $event_category != 'merchandise'){ ?>
								<?php
								/* Display the address and google map link if available */
								if ($location != '' && (empty($org_options['display_address_in_regform']) || $org_options['display_address_in_regform'] != 'N')) {
									?>
									<div class="event-detail event-detail-address" id="event_address-<?php echo $event_id ?>">

										<span class="event-detail-label"><?php echo __('Location:', 'event_espresso'); ?></span> <br />
										<span class="event-detail-value">
											<?php echo $venue_title; ?>, <?php echo stripslashes_deep($location); ?><br />
											<span class="google-map-link"><?php echo $google_map_link; ?></span>
										</span>
									</div><!-- /.event-detail-address -->
									<?php
								}
							}
							do_action('action_hook_espresso_social_display_buttons', $event_id);
							?>
						</div><!-- /.event-details -->
						<div class="event-description">
			<?php if ($display_desc == "Y") { //Show the description or not ?>
				<?php echo espresso_format_content($event_desc);?>
			<?php }//End display description ?>
		</div>

						<?php
					}

					if ($display_reg_form == 'Y') {
						?>
						<div id="event-reg-form-groups">

							<?php if($event_category == 'donation'){ ?>
								<fieldset>
									<div class="event_form_field">
										<label for="donation_amount" class="event-detail-label">Donation Amount</label>
										<br />
										<input
											type="text"
											name="donation_amount"
											id="donation_amount"
											class="donation_amount price_id required numeric-only"
											value="0.00" />
									</div>
								</fieldset>
								<span class="event-detail-label">Donor Information</span>
							<?php } else if($event_category == 'merchandise'){ ?>
								<h3 class="section-heading"><?php _e('Buyer Details', 'event_espresso'); ?></h3>
							<?php } else { ?>
								<h3 class="section-heading"><?php _e('Registration Details', 'event_espresso'); ?></h3>
							<?php } ?>




							<?php
							//Outputs the custom form questions. This function can be overridden using the custom files addon
							echo event_espresso_add_question_groups( $question_groups, '', NULL, FALSE, array( 'attendee_number' => 1 ), 'event-registration-questions' );
							?>
						</div>

					<?php
						//Coupons
					?>
					<input type="hidden" name="use_coupon[<?php echo $event_id; ?>]" value="<?php echo $use_coupon_code; ?>" />
					<?php
						if ( $use_coupon_code == 'Y' && function_exists( 'event_espresso_coupon_registration_page' )) {
							echo event_espresso_coupon_registration_page($use_coupon_code, $event_id);
						}
						//End coupons display

						//Groupons
					?>
					<input type="hidden" name="use_groupon[<?php echo $event_id; ?>]" value="<?php echo $use_groupon_code; ?>" />
					<?php
						if ( $use_groupon_code == 'Y' && function_exists( 'event_espresso_groupon_registration_page' )) {
							echo event_espresso_groupon_registration_page($use_groupon_code, $event_id);
						}
						//End groupons display
					?>
					<input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id; ?>" value="post_attendee">
					<input type="hidden" name="event_id" id="event_id-<?php echo $event_id; ?>" value="<?php echo $event_id; ?>">

					<?php if($event_category == 'donation'){?>
						<input type="hidden" name="is_donation" value="true" />
					<?php } ?>

					<?php
					//Multiple Attendees
					if ( $allow_multiple == "Y" && $number_available_spaces > 1 ) {
						//This returns the additional attendee form fields. Can be overridden in the custom files addon.
						echo event_espresso_additional_attendees($event_id, $additional_limit, $number_available_spaces, __('Number of Tickets', 'event_espresso'), true, $event_meta);
					} else {
						?>
						<input type="hidden" name="num_people" id="num_people-<?php echo $event_id; ?>" value="1">
						<?php
					}
					//End allow multiple

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
					<div class="event_form_submit" id="event_form_submit-<?php echo $event_id; ?>">
					<br />
					<br />
						<input class="event-button" id="event_form_field-<?php echo $event_id; ?>" type="submit" name="Submit" value="<?php _e('Continue', 'event_espresso'); ?>">
					</div>

				<?php } ?>



		    </form>
		</div>

		<?php event_espresso_show_price_types($event_id, ($event_category == 'donation'?true:false)); ?>


	<?php if ($event_id == 8){
				?>
				<div style="float: left;">
			<a class="event-button back-to-events" href="/events/" title="Back to Events">Back to Events</a>&nbsp;
		</div>
				<a class="event-button ee_view_cart " target="_blank" id="a_register_link-<?php echo $event_id ?>" href="http://maconoctoberfest.com/beer-competition/" title="Register For Competition">
						Register
					</a>
			<?php
			}
			else {
			?>


		<div style="float: left;">
			<a class="event-button back-to-events" href="/events/" title="Back to Events">Back to Events</a>&nbsp;
		</div>
		<div>
				<?php
				$params = array(
					//REQUIRED, the id of the event that needs to be added to the cart
					'event_id' => $event_id,
					//REQUIRED, Anchor of the link, can use text or image
					'anchor' => __("Add to Cart", 'event_espresso'), //'anchor' => '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/cart_add.png" />',
					//REQUIRED, if not available at this point, use the next line before this array declaration
					// $event_name = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event_id);
					'event_name' => $event_name,
					//OPTIONAL, will place this term before the link
					//'separator' => __(" or ", 'event_espresso')
				);

				$cart_link = event_espresso_cart_link($params);
				echo $cart_link;
				?>
		</div>

		<?php
		}
			break;

		}
		//End Switch statement to check the status of the event

		if (isset($ee_style['event_espresso_form_wrapper_close'])) {
			echo $ee_style['event_espresso_form_wrapper_close'];
		}
		?>
		<p class="edit-link-footer"><?php echo espresso_edit_this($event_id) ?></p>
	</div><!-- /.event-content -->
</div><!-- /.event-registration-container -->
