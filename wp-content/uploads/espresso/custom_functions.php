<?php

add_filter( 'wp_enqueue_scripts', 'enqueue_custom_jquery', 0 );

function enqueue_custom_jquery() {
    wp_enqueue_script( 'jquery.numeric', '/wp-content/uploads/espresso/templates/js/jquery.numeric.js', array( 'jquery' ) );
    wp_enqueue_script( 'lokkgary.custom', '/wp-content/uploads/espresso/templates/js/custom.js', array( 'jquery' ) );

}

if (!function_exists('print_a')) {
	function print_a($array,$return=FALSE){
		$string = "<pre>";
		$string .= print_r($array,true);
		$string .= "</pre>";
		if($return){
			return $string;
		}
		echo $string;
	}
}

function get_category_reference_array(){
	global $wpdb;
	//get categories and ids
	$cat_sql = "
		SELECT
			id,
			category_identifier
		FROM
			".EVENTS_CATEGORY_TABLE."
	";
	$cats = $wpdb->get_results( $wpdb->prepare( $cat_sql, NULL ), ARRAY_A );

	$cat_reference = array();
	foreach($cats as $cat){
		$cat_reference[$cat['id']] = $cat['category_identifier'];
	}
	return $cat_reference;
}


function event_espresso_calculate_total( $update_section = FALSE, $mer = TRUE ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	

	$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
	
	$grand_total = 0.00;
	
	$coupon_events = array();
	$coupon_notifications = '';
	$coupon_errors = '';
	
	$groupon_events = array();
	$groupon_notifications = '';
	$groupon_errors = '';


		
	if (is_array($events_in_session)) {

		$event_total_cost = 0;

		foreach ( $events_in_session as $event_id => $event ) {
		
			$event_id = absint( $event_id );
			$event_cost = 0;
			$event_individual_cost[$event_id] = 0;
			$attendee_quantity = 0;

			$is_donation = ( isset($_POST['is_donation'][$event_id]) && $_POST['is_donation'][$event_id] == 'true'?true:false);

			$coupon_results = array(
				'event_cost' => 0,
				'valid' => FALSE,
				'error' => '',
				'msg' => ''
			);
			
			$groupon_results = array(
				'event_cost' => 0,
				'valid' => FALSE,
				'error' => '',
				'msg' => ''
			);
			
			$use_coupon_code = isset( $_POST['use_coupon'][$event_id] ) ? $_POST['use_coupon'][$event_id] : 'N';
			if ( $use_coupon_code == 'Y' ) {
				add_filter( 'filter_hook_espresso_coupon_results', 'espresso_filter_coupon_results', 10, 3 );
			}

			$use_groupon_code = isset( $_POST['use_groupon'][$event_id] ) ? $_POST['use_groupon'][$event_id] : 'N';
			if ( $use_groupon_code == 'Y' ) {
				add_filter( 'filter_hook_espresso_groupon_results', 'espresso_filter_groupon_results', 10, 3 );
			}


			$start_time_id = '';
			if (array_key_exists('start_time_id', $_POST) && array_key_exists($event_id, $_POST['start_time_id'])) {
				$start_time_id = $_POST['start_time_id'][$event_id];
			}


			/*
			 * two ways the price id comes this way
			 * - from a dropdown >> price_id[event_id][price_id]
			 * - from a radio >> price_id[event_id] with a value of price_id
			 */

			$event_price = $_POST['price_id'][$event_id];
			//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

			if($is_donation){
				$event_cost = abs($event_price);
				$event_individual_cost[$event_id] = $event_cost;
			}
			else{

				if ( is_array( $event_price )) {
				
					foreach ( $event_price as $_price_id => $qty ) {					
						$attendee_quantity = absint( $qty );
						if ( $attendee_quantity > 0 ) {
						
							// Process coupons
							$coupon_results['event_cost'] = event_espresso_get_final_price( $_price_id, $event_id );
							$coupon_results = apply_filters( 'filter_hook_espresso_coupon_results', $coupon_results, $event_id, $mer );
							$coupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_notifications, $coupon_results['msg'] );
							$coupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_errors, $coupon_results['error'] );
							if ( $coupon_results['valid'] ) {
								$coupon_events = apply_filters( 'filter_hook_espresso_cart_coupon_events_array', $coupon_events, $event['event_name'] );
							}
							$event_cost = $coupon_results['event_cost'];
							
							if (function_exists('event_espresso_groupon_payment_page') && isset($_POST['event_espresso_groupon_code'])) {	

								// Process Groupons
								$groupon_results['event_cost'] = $event_cost;
								$groupon_results = apply_filters( 'filter_hook_espresso_groupon_results', $groupon_results, $event_id, $mer );
								$groupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_notifications, $groupon_results['msg'] );
								$groupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_errors, $groupon_results['error'] );
								if ( $groupon_results['valid'] ) {
									$groupon_events = apply_filters( 'filter_hook_espresso_cart_groupon_events_array', $groupon_events, $event['event_name'] );
								}
								//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
								$event_cost = $groupon_results['event_cost'];
							
							} 
							
							// now sum up costs so far
							$event_individual_cost[$event_id] += number_format( $event_cost * $attendee_quantity, 2, '.', '' );
							do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .': event_cost='.$event_cost );
							
						}
					}
					 
				} else {
				
					// Process coupons
					$coupon_results['event_cost'] = event_espresso_get_final_price( $event_price, $event_id );
					$coupon_results = apply_filters( 'filter_hook_espresso_coupon_results', $coupon_results, $event_id, $mer );
					$coupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_notifications, $coupon_results['msg'] );
					$coupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $coupon_errors, $coupon_results['error'] );
					if ( $coupon_results['valid'] ) {
						$coupon_events = apply_filters( 'filter_hook_espresso_cart_coupon_events_array', $coupon_events, $event['event_name'] );
					}
					$event_cost = $coupon_results['event_cost'];


					if (function_exists('event_espresso_groupon_payment_page') && isset($_POST['event_espresso_groupon_code'])) {	

						// Process groupons
						$groupon_results['event_cost'] = $event_cost;
						$groupon_results = apply_filters( 'filter_hook_espresso_groupon_results', $groupon_results, $event_id, $mer );
						$groupon_notifications = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_notifications, $groupon_results['msg'] );
						$groupon_errors = apply_filters( 'filter_hook_espresso_cart_modifier_strings', $groupon_errors, $groupon_results['error'] );
						if ( $groupon_results['valid'] ) {
							$groupon_events = apply_filters( 'filter_hook_espresso_cart_groupon_events_array', $groupon_events, $event['event_name'] );
						}
						//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
						$event_cost = $groupon_results['event_cost'];
						
					}
					
					// now sum up costs so far
					$event_individual_cost[$event_id] += number_format( $event_cost, 2, '.', '' );
					//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .': event_cost='.$event_cost );
					
				}
			}

			$_SESSION['espresso_session']['events_in_session'][$event_id]['cost'] = $event_individual_cost[$event_id];
			//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
			$event_total_cost += $event_individual_cost[$event_id];

		}
		
		$grand_total = number_format($event_total_cost, 2, '.', '');
		//echo '<h4>$grand_total : ' . $grand_total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

		$_SESSION['espresso_session']['pre_discount_total'] = $grand_total;
		$_SESSION['espresso_session']['grand_total'] = $grand_total;
		event_espresso_update_item_in_session( $update_section );
		
	}
		
//		echo '$coupon_notifications = ' . $coupon_notifications . '<br/>';
//		echo '$coupon_errors = ' . $coupon_errors . '<br/>';
//		echo '$groupon_notifications = ' . $groupon_notifications . '<br/>';
//		echo '$groupon_errors = ' . $groupon_errors . '<br/>';	
	$coupon_events =array_unique( $coupon_events );
	$coupon_count = count( $coupon_events );
	if ( ! strpos( $coupon_notifications, 'event_espresso_invalid_coupon' ) && $coupon_count > 0 ) {
		$events = implode( $coupon_events, '<br/>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' );
		$coupon_notifications .= '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' . $events . '</p>';
		$coupon_errors = FALSE;
	}

	$groupon_events =array_unique( $groupon_events );
	$groupon_count = count( $groupon_events );
	if ( ! strpos( $groupon_notifications, 'event_espresso_invalid_groupon' ) && $groupon_count > 0 ) {
		$events = implode( $groupon_events, '<br/>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' );
		$groupon_notifications .= '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;' . $events . '</p>';
		$groupon_errors = FALSE;
	}
//		echo '$coupon_notifications = ' . $coupon_notifications . '<br/>';
//		echo '$coupon_errors = ' . $coupon_errors . '<br/>';
//		echo '$groupon_notifications = ' . $groupon_notifications . '<br/>';
//		echo '$groupon_errors = ' . $groupon_errors . '<br/>';	

	// add space between $coupon_notifications and  $coupon_errors ( if any $coupon_errors exist )
	$coupon_notifications = $coupon_count && $coupon_errors ? $coupon_notifications . '<br/>' : $coupon_notifications;
	// combine $coupon_notifications & $coupon_errors
	$coupon_notifications .= $coupon_errors;
	// add space between $coupon_notifications and $groupon_notifications ( if any $groupon_notifications exist )
	$coupon_notifications = ( $coupon_count || $coupon_errors ) && ( $groupon_count || $groupon_errors )  ? $coupon_notifications . '<br/>' : $coupon_notifications;
	// add space between $groupon_notifications and  $groupon_errors ( if any $groupon_errors exist )
	$groupon_notifications = $groupon_count && $groupon_errors ? $groupon_notifications . '<br/>' : $groupon_notifications;
	// ALL together now!!!
	$notifications = $coupon_notifications . $groupon_notifications . $groupon_errors;


	if ( ! $update_section ) {
		echo event_espresso_json_response(array('grand_total' => number_format( $grand_total, 2, '.', '' ), 'msg' => $notifications ));
		die();
	}
	
}












/*
Function Name: Maximum Date Display
Author: Seth Shoultes
Contact: seth@smartwebutah.com
Website: http://shoultes.net
Description: This function is used in the Events Table Display template file to show events for a maximum number of days in the future
Usage Example: 
Requirements: Events Table Display template file
Notes: 
*/
function display_event_espresso_date_max($max_days="null"){
	global $wpdb;
	//$org_options = get_option('events_organization_settings');
	//$event_page_id =$org_options['event_page_id'];
	if ($max_days != "null"){
		if ($_REQUEST['show_date_max'] == '1'){
			foreach ($_REQUEST as $k=>$v) $$k=$v;
		}
		$max_days = $max_days;
		$sql  = "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE ADDDATE('".date ( 'Y-m-d' )."', INTERVAL ".$max_days." DAY) >= start_date AND start_date >= '".date ( 'Y-m-d' )."' AND is_active = 'Y' ORDER BY date(start_date)";
		event_espresso_get_event_details($sql);//This function is called from the event_list.php file which should be located in your templates directory.

	}				
}

/*
Function Name: Event Status
Author: Seth Shoultes
Contact: seth@eventespresso.com
Website: http://eventespresso.com
Description: This function is used to display the status of an event.
Usage Example: Can be used to display custom status messages in your events.
Requirements: 
Notes: 
*/
if (!function_exists('espresso_event_status')) {
	function espresso_event_status($event_id){
		$event_status = event_espresso_get_is_active($event_id);
		
		//These messages can be uesd to display the status of the an event.
		switch ($event_status['status']){
			case 'EXPIRED':
				$event_status_text = __('This event is expired.','event_espresso');
			break;
			
			case 'ACTIVE':
				$event_status_text = __('This event is active.','event_espresso');
			break;
			
			case 'NOT_ACTIVE':
				$event_status_text = __('This event is not active.','event_espresso');
			break;
			
			case 'ONGOING':
				$event_status_text = __('This is an ongoing event.','event_espresso');
			break;
			
			case 'SECONDARY':
				$event_status_text = __('This is a secondary/waiting list event.','event_espresso');
			break;
			
		}
		return $event_status_text;
	}
}

/*
Function Name: Custom Event List Builder
Author: Seth Shoultes
Contact: seth@eventespresso.com
Website: http://eventespresso.com
Description: This function creates lists of events using custom templates.
Usage Example: Create a page or widget template to show events.
Requirements: Template files must be stored in your wp-content/uploads/espresso/templates directory
Notes: 
*/
if (!function_exists('espresso_list_builder')) {
	function espresso_list_builder($sql, $template_file, $before, $after){
		
		global $wpdb, $org_options;
		//echo 'This page is located in ' . get_option( 'upload_path' );
		$event_page_id = $org_options['event_page_id'];
		$currency_symbol = $org_options['currency_symbol'];
		$events = $wpdb->get_results($sql);
		$category_id = $wpdb->last_result[0]->id;
		$category_name = $wpdb->last_result[0]->category_name;
		$category_desc = html_entity_decode( wpautop($wpdb->last_result[0]->category_desc) );
		$display_desc = $wpdb->last_result[0]->display_desc;
		
		if ($display_desc == 'Y'){
			echo '<p id="events_category_name-'. $category_id . '" class="events_category_name">' . stripslashes_deep($category_name) . '</p>';
			echo wpautop($category_desc);				
		}
		
		foreach ($events as $event){
			$event_id = $event->id;
			$event_name = $event->event_name;
			$event_identifier = $event->event_identifier;
			$active = $event->is_active;
			$registration_start = $event->registration_start;
			$registration_end = $event->registration_end;
			$start_date = $event->start_date;
			$end_date = $event->end_date;
			$reg_limit = $event->reg_limit;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$member_only = $event->member_only;
			$externalURL = $event->externalURL;
			$recurrence_id = $event->recurrence_id;
			
			$allow_overflow = $event->allow_overflow;
			$overflow_event_id = $event->overflow_event_id;
			
			//Address formatting
			$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			
			//Google map link creation
			$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country, 'text'=> 'Map and Directions', 'type'=> 'text') );
			
			//These variables can be used with other the espresso_countdown, espresso_countup, and espresso_duration functions and/or any javascript based functions.
			$start_timestamp = espresso_event_time($event_id, 'start_timestamp', get_option('time_format'));
			$end_timestamp = espresso_event_time($event_id, 'end_timestamp', get_option('time_format'));
			
			//This can be used in place of the registration link if you are usign the external URL feature
			$registration_url = $externalURL != '' ? $externalURL : get_option('siteurl') . '/?page_id='.$event_page_id.'&regevent_action=register&event_id='. $event_id;
		
			if (!is_user_logged_in() && get_option('events_members_active') == 'true' && $member_only == 'Y') {
				//Display a message if the user is not logged in.
				 //_e('Member Only Event. Please ','event_espresso') . event_espresso_user_login_link() . '.';
			}else{
	//Serve up the event list
	//As of version 3.0.17 the event lsit details have been moved to event_list_display.php
				echo $before = $before == ''? '' : $before;
				include('templates/'. $template_file);
				echo $after = $after == ''? '' : $after;
			} 
		}
	//Check to see how many database queries were performed
	//echo '<p>Database Queries: ' . get_num_queries() .'</p>';
	}
}