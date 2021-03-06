<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');
//As of version 3.0.17
//This is a logic file for displaying a registration form for an event on a page. This file will do all of the backend data retrieval functions.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
//Note: This entire function can be overridden using the "Custom Files" addon
if (!function_exists('register_attendees')) {

    function register_attendees($single_event_id = NULL, $event_id_sc =0, $reg_form_only = false) {
		//Declare the $data object
		$data = (object)array( 'event' => NULL );
		$template_name = ( 'registration_page_display.php' );
		$path = locate_template( $template_name );

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		//Run code for the seating chart addon
		if ( function_exists('espresso_seating_version') ){
			do_action('ee_seating_chart_css');
			do_action('ee_seating_chart_js');
			do_action('ee_seating_chart_flush_expired_seats');
		}

        global $wpdb, $org_options;

		//get category reference index array...
		$cat_reference = get_category_reference_array();


        if (isset($_REQUEST['ee']) && $_REQUEST['ee'] != '') {
            $_REQUEST['event_id'] = $_REQUEST['ee'];
        }

        $event_id = $event_id_sc != '0' ? $event_id_sc : $_REQUEST['event_id'];

        if (!empty($_REQUEST['event_id_time'])) {
            $pieces = explode('|', $_REQUEST['event_id_time'], 3);
            $event_id = $pieces[0];
            $start_time = $pieces[1];
            $time_id = $pieces[2];
            $time_selected = true;
        }

        //The following variables are used to get information about your organization
        $event_page_id = $org_options['event_page_id'];
        $Organization = stripslashes_deep($org_options['organization']);
        $Organization_street1 = $org_options['organization_street1'];
        $Organization_street2 = $org_options['organization_street2'];
        $Organization_city = $org_options['organization_city'];
        $Organization_state = $org_options['organization_state'];
        $Organization_zip = $org_options['organization_zip'];
        $contact = $org_options['contact_email'];
        $registrar = $org_options['contact_email'];
        $currency_format = isset($org_options['currency_format']) ? $org_options['currency_format'] : '';

        $message = $org_options['message'];

        //Build event queries
        $sql = "SELECT e.*, ese.start_time, ese.end_time ";
		if ( isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ) {
	 		$sql .= ", v.name venue_name, v.address venue_address, v.address2 venue_address2, v.city venue_city, v.state venue_state, v.zip venue_zip, v.country venue_country, v.meta venue_meta ";
		}
        $sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";
		$sql .= " LEFT JOIN " . EVENTS_START_END_TABLE . " ese ON ese.event_id = e.id ";

		if ( isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ) {
			$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = e.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id ";
		}
		$sql.= " WHERE e.is_active='Y' ";
		$sql.= " AND e.event_status != 'D' ";

		//Get the ID of a single event
		if ($single_event_id != NULL) {
			//If a single event needs to be displayed, get its ID
            $sql .= " AND event_identifier = '" . $single_event_id . "' ";
        } else {
			$sql.= " AND e.id = '" . $event_id . "' LIMIT 0,1";
        }

        //Support for diarise
        if (!empty($_REQUEST['post_event_id'])) {
            $sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . ' e';
            $sql .= " LEFT JOIN " . EVENTS_START_END_TABLE . " ese ON ese.event_id = e.id ";
            $sql .= " WHERE post_id = '" . $_REQUEST['post_event_id'] . "' ";
            $sql .= " LIMIT 0,1";
        }

        $data->event = $wpdb->get_row( $wpdb->prepare( $sql, NULL ), OBJECT);
        $num_rows = $wpdb->num_rows;


        //Build the registration page
        if ($num_rows > 0) {

            //These are the variables that can be used throughout the registration page
            //foreach ($events as $event) {
            global $this_event_id;
            $event_id = $data->event->id;
            $this_event_id = $event_id;

            $event_name = stripslashes_deep($data->event->event_name);
            $event_desc = stripslashes_deep($data->event->event_desc);
            $display_desc = $data->event->display_desc;
			if ( $reg_form_only == true )
				$display_desc = "N";

            $display_reg_form = $data->event->display_reg_form;
            $event_address = $data->event->address;
            $event_address2 = $data->event->address2;
            $event_city = $data->event->city;
            $event_state = $data->event->state;
            $event_zip = $data->event->zip;
            $event_country = $data->event->country;
            $event_description = stripslashes_deep($data->event->event_desc);
            $event_identifier = $data->event->event_identifier;
            $event_cost = isset($data->event->event_cost) ? $data->event->event_cost : "0.00";
            $member_only = $data->event->member_only;
            $reg_limit = $data->event->reg_limit;
            $allow_multiple = $data->event->allow_multiple;
            $start_date = $data->event->start_date;
            $end_date = $data->event->end_date;
            $allow_overflow = $data->event->allow_overflow;
            $overflow_event_id = $data->event->overflow_event_id;

            //Venue details
            $venue_title = $data->event->venue_title;
            $venue_url = $data->event->venue_url;
            $venue_image = $data->event->venue_image;
            $venue_phone = $data->event->venue_phone;
            $venue_address = '';
            $venue_address2 = '';
            $venue_city = '';
            $venue_state = '';
            $venue_zip = '';
            $venue_country = '';

            global $event_meta;
            $event_meta = unserialize($data->event->event_meta);

            //Venue information
            if ($org_options['use_venue_manager'] == 'Y') {
                $event_address = $data->event->venue_address;
                $event_address2 = $data->event->venue_address2;
                $event_city = $data->event->venue_city;
                $event_state = $data->event->venue_state;
                $event_zip = $data->event->venue_zip;
                $event_country = $data->event->venue_country;

                //Leaving these variables intact, just in case people wnat to use them
                $venue_title = $data->event->venue_name;
                $venue_address = $data->event->venue_address;
                $venue_address2 = $data->event->venue_address2;
                $venue_city = $data->event->venue_city;
                $venue_state = $data->event->venue_state;
                $venue_zip = $data->event->venue_zip;
                $venue_country = $data->event->venue_country;
                global $venue_meta;
                $add_venue_meta = array(
                    'venue_title' => $data->event->venue_name,
                    'venue_address' => $data->event->venue_address,
                    'venue_address2' => $data->event->venue_address2,
                    'venue_city' => $data->event->venue_city,
                    'venue_state' => $data->event->venue_state,
                    'venue_country' => $data->event->venue_country,
                );
                $venue_meta = (isset($data->event->venue_meta) && $data->event->venue_meta != '') && (isset($add_venue_meta) && $add_venue_meta != '') ? array_merge(unserialize($data->event->venue_meta), $add_venue_meta) : '';
                //print_r($venue_meta);
            }

            $virtual_url = stripslashes_deep($data->event->virtual_url);
            $virtual_phone = stripslashes_deep($data->event->virtual_phone);

            //Address formatting
           $location = (!empty($event_address) ? '<br />' . $event_address : '') . (!empty($event_address2) ? '<br />' . $event_address2 : '') . (!empty($event_city) ? '<br />' . $event_city : '') . (!empty($event_state)  ? ', ' . $event_state : '') . (!empty($event_zip) ? ' ' . $event_zip : '');

            //Google map link creation
            $google_map_link = espresso_google_map_link(array('address' => $event_address, 'city' => $event_city, 'state' => $event_state, 'zip' => $event_zip, 'country' => $event_country, 'text' => 'Map and Directions', 'type' => 'text'));

            $question_groups = unserialize($data->event->question_groups);
            $reg_start_date = $data->event->registration_start;
            $reg_end_date = $data->event->registration_end;
            $today = date("Y-m-d");
            if (isset($data->event->timezone_string) && $data->event->timezone_string != '') {
                $timezone_string = $data->event->timezone_string;
            } else {
                $timezone_string = get_option('timezone_string');
                if (!isset($timezone_string) || $timezone_string == '') {
                    $timezone_string = 'America/New_York';
                }
            }

            $t = time();
            $today = date_at_timezone("Y-m-d H:i A", $timezone_string, $t);
            //echo event_date_display($today, get_option('date_format'). ' ' .get_option('time_format')) . ' ' . $timezone_string;
            //echo espresso_ddtimezone_simple();
            $reg_limit = $data->event->reg_limit;
            $additional_limit = $data->event->additional_limit;



            //If the coupon code system is intalled then use it
            $use_coupon_code = $data->event->use_coupon_code;

            //If the groupon code addon is installed, then use it
            $use_groupon_code = $data->event->use_groupon_code;

            //Set a default value for additional limit
            if ($additional_limit == '') {
                $additional_limit = '5';
            }

            $num_attendees = get_number_of_attendees_reg_limit($event_id, 'num_attendees'); //Get the number of attendees
            $available_spaces = get_number_of_attendees_reg_limit($event_id, 'available_spaces'); //Gets a count of the available spaces
            $number_available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces'); //Gets the number of available spaces
            //echo $number_available_spaces;


            global $all_meta;
            $all_meta = array(
                'event_id' => $event_id,
				'event_name' => stripslashes_deep($event_name),
                'event_desc' => stripslashes_deep($event_desc),
                'event_address' => $event_address,
                'event_address2' => $event_address2,
                'event_city' => $event_city,
                'event_state' => $event_state,
                'event_zip' => $event_zip,
                'event_country' => $event_country,
                'venue_title' => $venue_title,
                'venue_address' => $venue_address,
                'venue_address2' => $venue_address2,
                'venue_city' => $venue_city,
                'venue_state' => $venue_state,
                'venue_country' => $venue_country,
				'location' => $location,
				'is_active' => $data->event->is_active,
				'event_status' => $data->event->event_status,
				'contact_email' => empty($data->event->alt_email) ? $org_options['contact_email'] : $data->event->alt_email,
				'start_time' => empty($data->event->start_time) ? '' : $data->event->start_time,
				'end_time' => empty($data->event->end_time) ? '' : $data->event->end_time,
				'registration_startT' => $data->event->registration_startT,
				'registration_start' => $data->event->registration_start,
				'registration_endT' => $data->event->registration_endT,
				'registration_end' => $data->event->registration_end,
				'start_date' => event_espresso_no_format_date($start_date, get_option('date_format')),
                'end_date' => event_date_display($end_date, get_option('date_format')),
                'google_map_link' => $google_map_link,
            );

			//print_r($all_meta);
			//This function gets the status of the event.
			$is_active = array();
			$is_active = event_espresso_get_is_active(0, $all_meta);
			//echo '<p>'.print_r(event_espresso_get_is_active($event_id, $all_meta)).'</p>';;

            if ($org_options['use_captcha'] == 'Y' && empty($_REQUEST['edit_details'])) {
                ?>
                <script type="text/javascript">
                    var RecaptchaOptions = {
                        theme : '<?php echo $org_options['recaptcha_theme'] == '' ? 'red' : $org_options['recaptcha_theme']; ?>',
                        lang : '<?php echo $org_options['recaptcha_language'] == '' ? 'en' : $org_options['recaptcha_language']; ?>'
                    };
                </script>
                <?php
            }
            //This is the start of the registration form. This is where you can start editing your display.
            //(Shows the regsitration form if enough spaces exist)
            if ($num_attendees >= $reg_limit) {
                ?>
                <div class="espresso_event_full event-display-boxes" id="espresso_event_full-<?php echo $event_id; ?>">
                    <h3 class="event_title"><?php echo stripslashes_deep($event_name) ?></h3>
                    <div class="event-messages">
                        <p class="event_full"><strong><?php _e('We are sorry but this event has reached the maximum number of attendees!', 'event_espresso'); ?></strong></p>
                        <p class="event_full"><strong><?php _e('Please check back in the event someone cancels.', 'event_espresso'); ?></strong></p>
                        <p class="num_attendees"><?php _e('Current Number of Attendees:', 'event_espresso'); ?> <?php echo $num_attendees ?></p>
                    </div>
                <?php
                $num_attendees = get_number_of_attendees_reg_limit($event_id, 'num_attendees'); //Get the number of attendees. Please visit http://eventespresso.com/forums/?p=247 for available parameters for the get_number_of_attendees_reg_limit() function.
                if (($num_attendees >= $reg_limit) && ($allow_overflow == 'Y' && $overflow_event_id != 0)) {
                    ?>
                        <div class="event-buttons" id="register_link-<?php echo $overflow_event_id ?>" class="register-link-footer">
                        	<a class="a_register_link ui-button ui-button-big ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all" id="a_register_link-<?php echo $overflow_event_id ?>" href="<?php echo espresso_reg_url($overflow_event_id); ?>" title="<?php echo stripslashes_deep($event_name) ?>">
                        		<?php _e('Join Waiting List', 'event_espresso'); ?>
                        	</a>
                        </div>
                    <?php } ?>
                </div>

                    <?php
                } else {
					$member_options = get_option('events_member_settings');
					//echo "<pre>".print_r($member_options,true)."</pre>";
                    //If enough spaces exist then show the form
                    //Check to see if the Members plugin is installed.
                    if ( function_exists('espresso_members_installed') && espresso_members_installed() == true && !is_user_logged_in() && ($member_only == 'Y' || $member_options['member_only_all'] == 'Y') ) {
                        event_espresso_user_login();
                    } else {
                        //Serve up the registration form


						$this_cat_id = $data->event->category_id;

						$this_cat_ids = explode(',',$this_cat_id);

						$category_identifier =  '';

						if(isset($cat_reference[ $this_cat_ids[0]])){
							$category_identifier = $cat_reference[ $this_cat_ids[0]];
						}

						$custom_display_templates = array('donation');

						$event_category = $category_identifier;

						//$template_name = ( 'registration_page_display'.(isset($category_identifier) && $category_identifier != '' && in_array($category_identifier,$custom_display_templates)?'-'.$category_identifier:'').'.php' );
						$template_name = ( 'registration_page_display.php' );
						$path = locate_template( $template_name );


						if ( empty( $path ) ) {
						  require( $template_name );
						} else {
						  require( $path );
						}
                    }
                }//End if ($num_attendees >= $reg_limit) (Shows the regsitration form if enough spaces exist)
            } else {//If there are no results from the query, display this message
                 echo '<h3>'.__('This event has expired or is no longer available.', 'event_espresso').'</h3>';
            }

            echo espresso_registration_footer();

            //Check to see how many database queries were performed
            //echo '<p>Database Queries: ' . get_num_queries() .'</p>';
        }

    }
