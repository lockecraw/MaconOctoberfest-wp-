<?php
$category = "default";

if(isset($event_category) && trim($event_category) != ''){
	$category = $event_category;
}

//This is the event list template page.
//This is a template file for displaying an event lsit on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
/*
 * use the following shortcodes in a page or post:
 * [EVENT_LIST]
 * [EVENT_LIST limit=1]
 * [EVENT_LIST css_class=my-custom-class]
 * [EVENT_LIST show_expired=true]
 * [EVENT_LIST show_deleted=true]
 * [EVENT_LIST show_secondary=false]
 * [EVENT_LIST show_recurrence=true]
 * [EVENT_LIST category_identifier=your_category_identifier]
 *
 * Example:
 * [EVENT_LIST limit=5 show_recurrence=true category_identifier=your_category_identifier]
 *
 */

//Print out the array of event status options
//print_r (event_espresso_get_is_active($event_id));
//Here we can create messages based on the event status. These variables can be echoed anywhere on the page to display your status message.
$status = event_espresso_get_is_active(0,$event_meta);
$status_display = ' - ' . $status['display_custom'];
$status_display_ongoing = $status['status'] == 'ONGOING' ? ' - ' . $status['display_custom'] : '';
$status_display_deleted = $status['status'] == 'DELETED' ? ' - ' . $status['display_custom'] : '';
$status_display_secondary = $status['status'] == 'SECONDARY' ? ' - ' . $status['display_custom'] : ''; //Waitlist event
$status_display_draft = $status['status'] == 'DRAFT' ? ' - ' . $status['display_custom'] : '';
$status_display_pending = $status['status'] == 'PENDING' ? ' - ' . $status['display_custom'] : '';
$status_display_denied = $status['status'] == 'DENIED' ? ' - ' . $status['display_custom'] : '';
$status_display_expired = $status['status'] == 'EXPIRED' ? ' - ' . $status['display_custom'] : '';
$status_display_reg_closed = $status['status'] == 'REGISTRATION_CLOSED' ? ' - ' . $status['display_custom'] : '';
$status_display_not_open = $status['status'] == 'REGISTRATION_NOT_OPEN' ? ' - ' . $status['display_custom'] : '';
$status_display_open = $status['status'] == 'REGISTRATION_OPEN' ? ' - ' . $status['display_custom'] : '';

//You can also display a custom message. For example, this is a custom registration not open message:
$status_display_custom_closed = $status['status'] == 'REGISTRATION_CLOSED' ? ' - <span class="espresso_closed">' . __('Regsitration is closed', 'event_espresso') . '</span>' : '';
global $this_event_id;
$this_event_id = $event_id;
?>
<div id="event_data-<?php echo $event_id ?>" class="event-listing-container clearfix <?php echo $css_class; ?> <?php echo $category;?>">
	<?php //Featured image
	if(!empty($event_meta['event_thumbnail_url'])){
		echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, !empty($event_meta['event_thumbnail_url']) ? $event_meta['event_thumbnail_url'] : '');
	}
	?>
	<h1 id="event_title-<?php echo $event_id ?>">
		<a title="<?php echo stripslashes_deep($event_name) ?>" class="a_event_title" id="a_event_title-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>">
			<?php echo stripslashes_deep($event_name) ?>
		</a>
	</h1>
	<div class="event-content">
		<?php
		$event->event_cost = empty($event->event_cost) ? '' : $event->event_cost;
		?>


		<div class="event-details">
			<?php if($category != 'donation'){ ?>
			<div class="event-detail event-detail-price" id="p_event_price-<?php echo $event_id ?>">
				 <span class="event-detail-label">
				 	<?php  echo __('Price: ', 'event_espresso'); ?>
				 </span>
				<?php if ( $event->event_cost != '0.00' ) { ?>
					<span class="event-detail-value">
						<?php echo  $org_options['currency_symbol'].$event->event_cost; ?>
					</span>
				<?php } else { ?>
					<span class="event-detail-value">
				 		<?php echo __('Free Event', 'event_espresso'); ?>
					</span>
				<?php } ?>
			</div>
			<?php } ?>


			<?php if($category != 'donation' && $category != 'merchandise'){ ?>
				<div class="event-detail event-detail-price" id="event_date-<?php echo $event_id ?>">
					<span class="event-detail-label">
						<?php _e('Date:', 'event_espresso'); ?>
					</span>
					<span class="event-detail-value">
						<?php
							echo event_date_display($start_date, get_option('date_format'));
							//Add to calendar button
							//echo " ".apply_filters('filter_hook_espresso_display_ical', $all_meta);
						?>
					</span>
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

		</div>


		<?php if($category != 'donation' && $category != 'merchandise'){ ?>
			<?php if ( (isset($location) && $location != '' ) && (isset($org_options['display_address_in_event_list']) && $org_options['display_address_in_event_list'] == 'Y') ) { ?>
				<div class="event-detail event-detail-address" id="event_address-<?php echo $event_id ?>">
					<span class="event-detail-label">
						<?php echo __('Address:', 'event_espresso'); ?>
					</span>
					<span class="event-detail-value">
						<?php echo $venue_title; ?>, <?php echo stripslashes_deep($location); ?>
						<span class="google-map-link"><?php echo $google_map_link; ?></span>
					</span>
				</div>
				<?php
			}
		}

		//Social media buttons
		do_action('espresso_social_display_buttons', $event_id);

		/* Don't show available spaces
		if($category != 'donation' && $category != 'merchandise'){
			$num_attendees = get_number_of_attendees_reg_limit($event_id, 'num_attendees'); //Get the number of attendees. Please visit http://eventespresso.com/forums/?p=247 for available parameters for the get_number_of_attendees_reg_limit() function.
			if ($num_attendees >= $reg_limit) {
				?>
				<div class="event-detail event-detail-spaces" id="event_spaces-<?php echo $event_id ?>">
					<span class="event-detail-label">
						<?php _e('Available Spaces:', 'event_espresso') ?>
					</span>
					<span class="event-detail-value">
						<?php echo get_number_of_attendees_reg_limit($event_id, 'available_spaces', 'All Seats Reserved') ?>
					</span>
				</div>

				<?php
			} else {
				if ($display_reg_form == 'Y' && $externalURL == '') {
					?>
					<div class="event-detail event-detail-spaces" id="event_spaces-<?php echo $event_id ?>">
						<span class="event-detail-label">
							<?php _e('Available Spaces:', 'event_espresso') ?>
						</span>
						<span class="event-detail-value">
							<?php echo get_number_of_attendees_reg_limit($event_id, 'available_spaces') ?>
						</span>
					</div>
					<?php
				}

			}
		}
		*/

		//Show short descriptions
		if (!empty($event_desc) && isset($org_options['display_short_description_in_event_list']) && $org_options['display_short_description_in_event_list'] == 'Y') {
			?>
			<div class="event-description">
				<?php echo myTruncate(strip_tags(espresso_format_content($event_desc)),200,' ','<a title="'.stripslashes_deep($event_name).'" class="read-more-link" href="'.$registration_url.'">... read more</a>'); ?>
			</div>
			<?php
		}


		if ($num_attendees >= $reg_limit) {
			if ($overflow_event_id != '0' && $allow_overflow == 'Y') {
				?>
				<div class="event-buttons" id="register_link-<?php echo $overflow_event_id ?>">
					<a class="event-button event-button-register" id="a_register_link-<?php echo $overflow_event_id ?>" href="<?php echo espresso_reg_url($overflow_event_id); ?>" title="<?php echo stripslashes_deep($event_name) ?>">
						<?php _e('Join Waiting List', 'event_espresso'); ?>
					</a>
				</div>
				<?php
			}
		}
		else{

			/**
			 * Load the multi event link.
			 * */
			//Un-comment these next lines to check if the event is active
			//echo event_espresso_get_status($event_id);
			//print_r( event_espresso_get_is_active($event_id));

			if ($multi_reg && event_espresso_get_status($event_id) == 'ACTIVE'/* && $display_reg_form == 'Y'*/) {
			// Uncomment && $display_reg_form == 'Y' in the line above to hide the add to cart link/button form the event list when the registration form is turned off.

				$params = array(
					//REQUIRED, the id of the event that needs to be added to the cart
					'event_id' => $event_id,
					//REQUIRED, Anchor of the link, can use text or image
					'anchor' => __("Add to Cart", 'event_espresso'), //'anchor' => '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/cart_add.png" />',
					//REQUIRED, if not available at this point, use the next line before this array declaration
					// $event_name = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event_id);
					'event_name' => $event_name,
					//OPTIONAL, will place this term before the link
					'separator' => __(" or ", 'event_espresso')
				);

				$cart_link = event_espresso_cart_link($params);
			}else{
				$cart_link = false;
			}
			if ($display_reg_form == 'Y') {
				//Check to see if the Members plugin is installed.
				$member_options = get_option('events_member_settings');
				if ( function_exists('espresso_members_installed') && espresso_members_installed() == true && !is_user_logged_in() && ($member_only == 'Y' || $member_options['member_only_all'] == 'Y') ) {
					echo '<p class="ee_member_only">'.__('Member Only Event', 'event_espresso').'</p>';
				}else{
				?>
					<div class="event-buttons" id="register_link-<?php echo $event_id ?>">
						<a class="event-button event-button-register event-button-<?php echo $event_category; ?>" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>">
							<?php
								if($event_category == 'donation'){
									_e('Donate', 'event_espresso');
								}
								else if($event_category == 'merchandise'){
									_e('Purchase', 'event_espresso');
								}
								else if($event_category == 'competition'){
									_e('Register', 'event_espresso');
								}
								else{
									_e('Buy Tickets', 'event_espresso');
								}
							?>
						</a>
						<?php echo isset($cart_link) && $externalURL == '' ? $cart_link : ''; ?>
					</div>
				<?php
				}
			} else {
			?>
				<div id="register_link-<?php echo $event_id ?>">
					<a class="event-button event-button-register" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>">
						<?php _e('View Details', 'event_espresso'); ?>
					</a>
					<?php echo isset($cart_link) && $externalURL == '' ? $cart_link : ''; ?>
				</div>

			<?php
			}
		}
		?>

	</div><!-- / .event-content -->
</div><!-- / .event-listing-container -->
