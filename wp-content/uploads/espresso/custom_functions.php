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

function event_espresso_cart_link($atts) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	global $org_options, $this_event_id;

	$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );

	extract(
		shortcode_atts(
			array(
				'event_id' => $this_event_id,
				'anchor' => __('Register', 'event_espresso'),
				'event_name' => ' ',
				'separator' => NULL,
				'view_cart' => FALSE,
				'event_page_id' => $org_options['event_page_id'], //instead of sending it in as a var, grab the id here.
				'direct_to_cart' => 0,
				'moving_to_cart' => "Please wait redirecting to cart page"
			),
			$atts
		)
	);

	$registration_cart_class = '';
	ob_start();

	// if event is already in session, return the view cart link
	if ($view_cart || (is_array($events_in_session) && array_key_exists($event_id, $events_in_session))) {

		$registration_cart_url = get_option('siteurl') . '/?page_id=' . $event_page_id . '&regevent_action=show_shopping_cart';
		$registration_cart_anchor = __("View Cart", 'event_espresso');

	} else {

		//show them the add to cart link
		$registration_cart_url = isset($externalURL) && $externalURL != '' ? $externalURL : get_option('siteurl') . '/?page_id=' . $event_page_id . '&regevent_action=add_event_to_cart&event_id=' . $event_id . '&name_of_event=' . stripslashes_deep($event_name);
		$registration_cart_anchor = $anchor;
		$registration_cart_class = 'event-button-add-to-cart ee_add_item_to_cart';

	}

	if ($view_cart && $direct_to_cart == 1) {
		echo "<span id='moving_to_cart'>{$moving_to_cart}</span>";
		echo "<script language='javascript'>window.location='" . $registration_cart_url . "';</script>";
	} else {
		echo $separator . '<a class="event-button ee_view_cart ' . $registration_cart_class . '" id="cart_link_' . $event_id . '" href="' . $registration_cart_url . '" title="' . stripslashes_deep($event_name) . '" moving_to_cart="' . urlencode($moving_to_cart) . '" direct_to_cart="' . $direct_to_cart . '" >' . $registration_cart_anchor . '</a>';
	}

	$buffer = ob_get_contents();
	ob_end_clean();
	return $buffer;

}

function event_espresso_add_question_groups($question_groups, $answer = '', $event_id = null, $multi_reg = 0, $meta = array(), $class = 'my_class') {
	global $wpdb;

	//If memebers addon is installed, check to see if we want to disable the form fields for members
	$disabled = '';
	if ( function_exists('espresso_members_installed') && espresso_members_installed() == true ) {
		$member_options = get_option('events_member_settings');
		if ( is_user_logged_in() && $member_options['autofilled_editable'] == 'N' )
		$disabled = 'disabled="disabled"';
	}

	$event_id = empty($_REQUEST['event_id']) ? $event_id : $_REQUEST['event_id'];
	if (count($question_groups) > 0) {
		$questions_in = '';

		$FILTER = '';
		if (isset($_REQUEST['regevent_action']))
			$FILTER = " AND q.admin_only != 'Y' ";

		//echo 'additional_attendee_reg_info = '.$meta['additional_attendee_reg_info'].'<br />';
		//Only personal inforamation for the additional attendees in each group
		if (isset($meta['additional_attendee_reg_info']) && $meta['additional_attendee_reg_info'] == '2' && isset($meta['attendee_number']) && $meta['attendee_number'] > 1)
			$FILTER .= " AND qg.system_group = 1 ";

		if (!is_array($question_groups) && !empty($question_groups)) {
			$question_groups = unserialize($question_groups);
		}

		//Debug
		//echo "<pre>".print_r($question_groups,true)."</pre>";

		foreach ($question_groups as $g_id) {
			$questions_in .= $g_id . ',';
		}

		$questions_in = substr($questions_in, 0, -1);
		$group_name = '';
		$counter = 0;

		$sql = "SELECT q.*, qg.group_name, qg.group_description, qg.show_group_name, qg.show_group_description, qg.group_identifier
				FROM " . EVENTS_QUESTION_TABLE . " q
				JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON q.id = qgr.question_id
				JOIN " . EVENTS_QST_GROUP_TABLE . " qg ON qg.id = qgr.group_id
				WHERE qgr.group_id in ( $questions_in ) $FILTER
				ORDER BY qg.group_order ASC, qg.id, q.sequence, q.id ASC";
		//echo $sql;

		$questions = $wpdb->get_results($sql);

		$num_rows = $wpdb->num_rows;
		$html = '';

		if ($num_rows > 0) {
			$questions_displayed = array();
			foreach ($questions as $question) {
				$counter++;
				if (!in_array($question->id, $questions_displayed)) {
					$questions_displayed[] = $question->id;

					//if new group, close fieldset
					$html .= ($group_name != '' && $group_name != $question->group_name) ? '</fieldset>' : '';

					if ($group_name != $question->group_name) {
						$html .= '<fieldset class="event-questions" id="' . $question->group_identifier . '">';
						$html .= $question->show_group_name != 0 ? "<h4 class=\"reg-quest-title section-title\">".stripslashes_deep($question->group_name)."</h4>" : '';
						$html .= $question->show_group_description != 0 && $question->group_description == true ? '<p class="quest-group-descript">' . stripslashes_deep($question->group_description) . '</p>' : '';
						$group_name = stripslashes_deep($question->group_name);
					}

					$html .= event_form_build($question, $answer, $event_id, $multi_reg, $meta, $class, $disabled);
				}
				$html .= $counter == $num_rows ? '</fieldset>' : '';
			}
		}//end questions display
	} else {
		$html = '';
	}
	return $html;
}


	function event_form_build($question, $answer = "", $event_id = null, $multi_reg = 0, $extra = array(), $class = 'ee-reg-page-questions', $disabled = '') {

		if ($question->admin_only == 'Y' && empty($extra['admin_only'])) {
			return;
		}

		$attendee_number = isset($extra['attendee_number']) ? $extra['attendee_number'] : 3;
		$price_id = isset($extra['price_id']) ? $extra['price_id'] : 0;
		$multi_name_adjust = $multi_reg == 1 ? "[$event_id][$price_id][$attendee_number]" : '';
		$text_input_class = ' ee-reg-page-text-input ';

		// XXXXXX will get replaced with the attendee number
		if (!empty($extra["x_attendee"])) {
			$field_name = ($question->system_name != '') ? "x_attendee_" . $question->system_name . "[XXXXXX]" : "x_attendee_" . $question->question_type . '_' . $question->id . '[XXXXXX]';
			$email_validate = $question->system_name == 'email' ? 'email' : '';
			$question->system_name = "x_attendee_" . $question->system_name . "[XXXXXX]";
			//$question->required = 'N';
		} else {
			$field_name = ($question->system_name != '') ? $question->system_name : $question->question_type . '_' . $question->id;
			$email_validate = $question->system_name == 'email' ? 'email' : '';
		}
		$email_validate .= " question_".$field_name;

		//adding email required for 'verify email' question
		if($question->id == 38){
			$email_validate .= " email equal-to ignore";
		}

		$question->question = stripslashes( $question->question );

		if ($question->required == "Y") {
			$required_title = ' title="' . $question->required_text . '"';
			$required_class = ' required ' . $email_validate . ' ';
			$required_label = "*";
		} else {
			$required_title = '';
			$required_class = '';
			$required_label = '';
		}
		$label = '<label for="' . $field_name . '" class="' . $class . '">' . trim( stripslashes( str_replace( '&#039;', "'", $question->question ))) . $required_label . '</label> ';

		if (is_array($answer) && array_key_exists('event_attendees', $answer) /*&& $attendee_number === 1*/) {
			$answer = isset($answer['event_attendees'][$price_id][$attendee_number][$field_name]) ? $answer['event_attendees'][$price_id][$attendee_number][$field_name] : '';
		}

		//If the members addon is installed, get the users information if available
		if ( function_exists('espresso_members_installed') && espresso_members_installed() == true ) {
			global $current_user;
			global $user_email;
			require_once(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
			$userid = $current_user->ID;
		}

		$html = '';

		if ( is_array( $answer )) {
			array_walk_recursive( $answer, 'trim' );
		} else {
			$answer = trim( $answer );
		}

		switch ($question->question_type) {

			case "TEXT" :

				if (defined('EVENT_ESPRESSO_MEMBERS_DIR') && (empty($_REQUEST['event_admin_reports']) || $_REQUEST['event_admin_reports'] != 'add_new_attendee')) {
					if (!empty($question->system_name)) {

						$answer = htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' );

						switch ($question->system_name) {
							case $question->system_name == 'fname':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( $current_user->first_name ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'lname':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( $current_user->last_name ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'email':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( $user_email ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'address':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_address', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'city':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_city', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'state':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_state', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'zip':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_zip', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'phone':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_phone', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
							case $question->system_name == 'country':

								$answer = $attendee_number === 1 ? htmlspecialchars( stripslashes( get_user_meta($userid, 'event_espresso_country', TRUE ) ), ENT_QUOTES, 'UTF-8' ) : $answer;
								$html .= $answer == '' ? '' : '<input name="' . $question->system_name . $multi_name_adjust . '" type="hidden" value="' . $answer . '" class="' . $class . '" />';

								break;
						}
					}
				}

				if (is_array($answer)) {
					$answer = '';
				}
				if ($answer == '') {
					$disabled = '';
				}

				$html .= '<div class="event-question-field event_form_field">';
				if($question->id == 37){
					//this is a songwriter competition upload field
					/*
					$html .= '
					<label>Upload MP3:</label>
					<div id="upload__'.$field_name . '-' . $event_id . '-' . $price_id . '-' . $attendee_number.'" class="songwriter-upload">
						<form enctype="multipart/form-data">
						<input name="file" type="file" />
						<input type="button" value="Upload" />
						</form>
						<progress></progress>
					</div>
					';
					*/
					$html .= '<input type="text" ' . $required_title . ' class="' . $required_class . $class . $text_input_class .'" id="' . $field_name . '-' . $event_id . '-' . $price_id . '-' . $attendee_number . '" name="' . $field_name . $multi_name_adjust . '" value="' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '" ' . $disabled . ' placeholder="' . trim( stripslashes( str_replace( '&#039;', "'", $question->question ))) . $required_label . '" '.($question->id==38?"data-equal-to='email$multi_name_adjust'":'').' />';
				}
				else{
					$html .= '<input type="text" ' . $required_title . ' class="' . $required_class . $class . $text_input_class .'" id="' . $field_name . '-' . $event_id . '-' . $price_id . '-' . $attendee_number . '" name="' . $field_name . $multi_name_adjust . '" value="' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '" ' . $disabled . ' placeholder="' . trim( stripslashes( str_replace( '&#039;', "'", $question->question ))) . $required_label . '" '.($question->id==38?"data-equal-to='email$multi_name_adjust'":'').' />';
				}
				$html .= '</div>';

				break;
			case "TEXTAREA" :

				if (is_array($answer)) $answer = '';
				$html .= '<div class="event_form_field event-quest-group-textarea">' . $label;
				$html .= '<textarea ' . $required_title . ' class="' . $required_class . $class . $text_input_class . '" id="' . $field_name . '-' . $event_id . '-' . $price_id . '-' . $attendee_number . '" name="' . $field_name . $multi_name_adjust . '" rows="5">' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '</textarea></div>';

				break;
			case "SINGLE" :

				$html .= '<div class="single-radio">' . $label;
				$html .= '<ul class="options-list-radio event_form_field">';

				$values = explode(",", $question->response);
				$answer = trim( stripslashes( str_replace( '&#039;', "'", $answer )));
				$answer = htmlspecialchars( $answer, ENT_QUOTES, 'UTF-8' );

				foreach ($values as $key => $value) {

					$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
					$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
					$checked = ( $value == $answer ) ? ' checked="checked"' : "";
					$value_id = 'SINGLE_' . $question->id . '_' . $key . '_' . $attendee_number;

					$html .= '
					<li>
						<label for="' . $value_id . '" class="' . $class . ' radio-btn-lbl">
							<input id="' . $value_id . '" ' . $required_title . '" class="' . $required_class . $class . '" name="' . $field_name . $multi_name_adjust . '"  type="radio" value="' . $value . '" ' . $checked . ' />
							<span>' . $value . '</span>
						</label>
					</li>';

				}

				$html .= '</ul>';
				$html .= '</div>';

				break;
			case "MULTIPLE" :

				$html .= '<div class="multi-checkbox">' . $label;
				$html .= '<ul class="options-list-check event_form_field">';

				if ( is_array( $answer )) {
					foreach ( $answer as $key => $value ) {
						$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
						$answer[$key] = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
					}
				} else {
					$answer = trim( stripslashes( str_replace( '&#039;', "'", $answer )));
					$answer = htmlspecialchars( $answer, ENT_QUOTES, 'UTF-8' );
				}


				$values = explode(",", $question->response);
				foreach ($values as $key => $value) {

					$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
					$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
					$checked = (is_array($answer) && in_array($value, $answer)) ? ' checked="checked"' : "";
					$value_id = str_replace(' ', '', $value) . '-' . $event_id . '_' . $attendee_number;

					$html .= '
					<li>
						<label for="' . $value_id . '" class="' . $class . ' checkbox-lbl">
							<input id="' . $value_id . '" ' . $required_title . ' class="' . $required_class . $class . '" name="' . $field_name . $multi_name_adjust . '[]"  type="checkbox" value="' . $value . '" ' . $checked . '/>
							<span>' . $value . '</span>
						</label>
					</li>';

				}

				$html .= '</ul>';
				$html .= '</div>';

				break;
			case "DROPDOWN" :

				$dd_type = $question->system_name == 'state' ? 'name="state"' : 'name="' . $field_name . $multi_name_adjust . '"';
				$html .= '
				<div class="event_form_field" class="' . $class . '">' . $label;
				$html .= '
					<select ' . $dd_type . ' ' . $required_title . ' class="' . $required_class . $class . '" id="DROPDOWN_' . $question->id . '-' . $event_id . '-' . $price_id . '-' . $attendee_number . '">';
				$html .= '
						<option value="">' . __('Select One', 'event_espresso') . "</option>";

				$answer = trim( stripslashes( str_replace( '&#039;', "'", $answer )));
				$answer = htmlspecialchars( $answer, ENT_QUOTES, 'UTF-8' );

				$values = explode( ',', $question->response );
				foreach ( $values as $key => $value ) {

					$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
					$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
					$selected = ( $value == $answer ) ? ' selected="selected"' : "";

					$html .= '
						<option value="' . $value . '"' . $selected . '> ' . $value . '</option>';
				}

				$html .= '
				</select>';
				$html .= '
				</div>';

				break;
			default :
				break;

		}
		if (is_numeric($attendee_number)) $attendee_number++;
		return $html;
	}



function event_espresso_price_dropdown($event_id, $atts) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	//Attention:
	//If changes to this function are not appearing, you may have the members addon installed and will need to update the function there.
	//echo "<pre>".print_r($atts,true)."</pre>";
	extract($atts);
    global $wpdb, $org_options;

	$html = '';

	$label = $label == '' ? '<span class="event-detail-label">'.__('Choose an Option: ', 'event_espresso').'</span>' : $label;

	//Will make the name an array and put the time id as a key so we know which event this belongs to
    $multi_name_adjust = isset($multi_reg) && $multi_reg == true ? "[$event_id]" : '';

    $surcharge_text = isset($org_options['surcharge_text']) ? $org_options['surcharge_text'] : __('Surcharge', 'event_espresso');

    $results = $wpdb->get_results("SELECT id, event_cost, surcharge, surcharge_type, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "' ORDER BY id ASC");

    if ($wpdb->num_rows > 1) {
       //Create the label for the drop down
		$html .= $show_label == 1 ? '<label for="event_cost">' . $label . '</label>' : '';

		//Create a dropdown of prices
		$html .= '<select name="price_option' . $multi_name_adjust . '" id="price_option-' . $event_id . '">';

        foreach ($results as $result) {

            $selected = isset($current_value) && $current_value == $result->id ? ' selected="selected" ' : '';

            // Addition for Early Registration discount
            if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
                $result->event_cost = $early_price_data['event_price'];
                $message = __(' Early Pricing', 'event_espresso');
            } else $message = '';

            $surcharge = '';

            if ($result->surcharge > 0 && $result->event_cost > 0.00) {
                $surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $surcharge_text;
                if ($result->surcharge_type == 'pct') {
                    $surcharge = " + {$result->surcharge}% " . $surcharge_text;
                }
            }

            //Using price ID
            $html .= '<option' . $selected . ' value="' . $result->id . '|' . stripslashes_deep($result->price_type) . '">' . stripslashes_deep($result->price_type) . ' (' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ') ' . $surcharge . ' </option>';
        }
        $html .= '</select><input type="hidden" name="price_select" id="price_select-' . $event_id . '" value="true" />';
    } else if ($wpdb->num_rows == 1) {
        foreach ($results as $result) {

            // Addition for Early Registration discount
            if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
                $result->event_cost = $early_price_data['event_price'];
                $message = sprintf(__(' (including %s early discount) ', 'event_espresso'), $early_price_data['early_disc']);
            }

            $surcharge = '';

            if ($result->surcharge > 0 && $result->event_cost > 0.00) {
                $surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . $surcharge_text;
                if ($result->surcharge_type == 'pct') {
                    $surcharge = " + {$result->surcharge}% " . $surcharge_text;
                }
            }
            $message = isset($message) ? $message : '';

            if ( $result->event_cost != '0.00' ) {
                $html .= '<span class="event-detail-label">' . __('Price:', 'event_espresso') . '</span> <span class="event-detail-value">' . $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . $surcharge . '</span>';
                $html .= '<input type="hidden" name="price_id' . $multi_name_adjust . '" id="price_id-' . $result->id . '" value="' . $result->id . '" />';
            } else {
                $html .= '<span class="free_event">' . __('Free Event', 'event_espresso') . '</span>';
                $html .= '<input type="hidden" name="payment' . $multi_name_adjust . '" id="payment-' . $event_id . '" value="' . __('free event', 'event_espresso') . '" />';
                $html .= '<input type="hidden" name="price_id' . $multi_name_adjust . '" id="price_id-' . $result->id . '" value="free" />';
            }
        }
    }
   	echo $html;
	return;
}
	add_action('espresso_price_select', 'event_espresso_price_dropdown', 20, 2);


function event_espresso_time_dropdown( $event_id = 'NULL', $label = 1, $multi_reg = 0, $value = '' ) {

        global $wpdb, $org_options;
		$html = '<div class="event-detail event-detail-time">';

	//Will make the name an array and put the event id as a key so we
    //know which event this belongs to
    $multi_name_adjust = $multi_reg == 1 ? "[$event_id]" : '';

    $SQL = "SELECT timezone_string FROM " . EVENTS_DETAIL_TABLE . " WHERE id= %d ";
    $timezone_string = $wpdb->get_var( $wpdb->prepare( $SQL, $event_id ));

    //This is the initial check to see if time slots are controlled by registration limits.
    if ( $org_options['time_reg_limit'] == 'Y' ) {
		// select everything from time table plus calculate available spaces by subtracting
		// attendee count from inner query from the individual reg limits returned by the outer query
		$SQL = "SELECT ESE.*, ( ESE.reg_limit - ";
		// count # of attendees with good registrations where event start and end times match outer query start and end times
		$SQL .= "( SELECT count(id) FROM " . EVENTS_ATTENDEE_TABLE . " ATT ";
		$SQL .= "WHERE ATT.event_id= %d ";
		$SQL .= "AND ATT.payment_status != 'Incomplete' ";
		$SQL .= "AND ATT.payment_status != 'Refund' ";
		$SQL .= "AND ATT.event_time = ESE.start_time ";
		$SQL .= "AND ATT.end_time = ESE.end_time ) ";
		$SQL .= ") AS available_spaces ";
		$SQL .= "FROM " . EVENTS_START_END_TABLE . " ESE ";
		$SQL .= "WHERE ESE.event_id= %d ";
		$SQL .= "GROUP BY ESE.id";
		$event_times = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id, $event_id ));

    } else {
		$SQL = "SELECT ESE.* FROM " . EVENTS_START_END_TABLE . " ESE ";
		$SQL .= "WHERE ESE.event_id= %d ";
		$SQL .= "GROUP BY ESE.id";
		$event_times = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ));
 }

//printr( $event_times, '$event_times  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
 	// If one result, then display the times.
    if ($wpdb->num_rows == 1) {
        $html .= $label == 1 ? '<span class="event-detail-label">' . __('Start Time:', 'event_espresso') . '</span>' : '';
        foreach ($event_times as $time) {
            $html .= '<span class="event-detail-value">' . event_date_display($time->start_time, get_option('time_format')) . '</span>';
            $html .= $label == 1 ? '&nbsp;&nbsp;&nbsp;<span class="event-detail-label">' . __('End Time: ', 'event_espresso') . '</span>' : __(' to ', 'event_espresso');
            $html .= '<span class="event-detail-value">' . event_date_display($time->end_time, get_option('time_format')) . '</span>';
            $html .= '<input type="hidden" name="start_time_id' . $multi_name_adjust . '" id="start_time_id_' . $time->id . '" value="' . $time->id . '" />';
        }
    } else if ($wpdb->num_rows > 1) {//If more than one result, then display the dropdown
		//print_r($event_times);
        $html .= $label == 1 ? '<label class="start_time_id" for="start_time_id">' . __('Choose a Time: ', 'event_espresso') . '</label>' : '';
        $html .= '<select name="start_time_id' . $multi_name_adjust . '" id="start_time_id-' . $event_id . '">';
		//$html .= $label == 0 ?'<option  value="">' .__('Select a Time', 'event_espresso') . '</option>':'';
        foreach ($event_times as $time) {
            $selected = $value == $time->id ? ' selected="selected" ' : '';
            switch ( $org_options['time_reg_limit'] ) {//This checks to see if the time slots are controlled by registration limits.
                case 'Y':
                    //If the time slot is controlled by a registration limit.
                    //Then we need to check if there are enough spaces available.
                    //if (($time->reg_limit == 0)||($time->reg_limit > 0 && $time->reg_limit >=$num_attendees))
                    //If enough spaces are available, then show this time slot
                    if ($time->available_spaces > 0)
                        $html .= '<option' . $selected . ' value="' . $time->id . '">' . event_date_display($time->start_time, get_option('time_format')) . ' - ' . event_date_display($time->end_time, get_option('time_format')) . " ($time->available_spaces " . __('available spaces', 'event_espresso') . ")" . '</option>';
                    break;
                case 'N'://If time slots are not controlled by registration limits, then we show the default dropdown list of times.
                default:
                    $html .= '<option ' . $selected . ' value="' . $time->id . '">' . event_date_display($time->start_time, get_option('time_format')) . ' - ' . event_date_display($time->end_time, get_option('time_format')) . '</option>';
                    break;
            }
        }
        $html .= '</select>';
    }
	return $html."</div>";
}

function event_espresso_display_selected_time($time_id = 0, $format = 'NULL') {
    global $wpdb;
	$html = '';
    $event_times = $wpdb->get_results("SELECT * FROM " . EVENTS_START_END_TABLE . " WHERE id='" . $time_id . "'");
    foreach ($event_times as $time) {
        switch ($format) {
            case 'start' :
                $html .= event_date_display($time->start_time, get_option('time_format'));
                break;

            case 'end' :
                $html .= event_date_display($time->end_time, get_option('time_format'));
                break;
            default :
               $html .= '<span class="event-detail-label">'.__('Time:  ', 'event_espresso').'</span>';
			   $html .= '<span class="event-detail-value">'.event_date_display($time->start_time, get_option('time_format')) . ' - '. event_date_display($time->end_time, get_option('time_format')).'</span>';
               break;
        }
        $html .= '<input type="hidden" name="start_time_id" id="start_time_id-' . $time->id . '" value="' . $time->id . '"><input type="hidden" name="event_time" id="event_time-' . $time->start_time . '" value="' . $time->start_time . '">';

    }
	return $html;
}


function get_events_categories($event_id = 0){
	global $wpdb;

	//load categories and identifiers
	$cat_reference = get_category_reference_array();

	//get categories for event
	$cat_sql = "
		SELECT
			category_id
		FROM
			".EVENTS_DETAIL_TABLE."
		WHERE
			id = $event_id
		LIMIT 1
	";
	$cats = $wpdb->get_results( $wpdb->prepare( $cat_sql, NULL ), ARRAY_A );

	$category_string = $cats[0]['category_id'];

	$categories = explode(',',$category_string);

	$cat_identifiers = array();

	if(is_array($categories) && count($categories) > 0){

		foreach($categories as $cat){
			if(isset($cat_reference[$cat])){
				$cat_identifiers[] = $cat_reference[$cat];
			}
		}

	}

	//return an array of category identifiers
	return $cat_identifiers;

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

/* overriding this from event-espresso/includes/functions/cart.php
 * So I can check categories based on event ID to see if it's a donation.
 */
function event_espresso_add_event_process($event_id, $event_name) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	$event_categories = get_events_categories($event_id);

	$_SESSION['espresso_session']['events_in_session'][$event_id] = array(
			'id' => $event_id,
			'event_name' => stripslashes_deep($event_name),
			'attendee_quantitiy' => 1,
			'start_time_id' => '',
			'price_id' => array(),
			'cost' => 0.00,
			'event_attendees' => array(),
			'categories' => $event_categories
	);

	return true;

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

			//echo '<h4>event_cost : ' . $event_individual_cost[$event_id] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

			if($is_donation){

				//$event_price = array_shift(array_values($_POST['price_id'][$event_id]));
				$event_price = $_POST['donation_amount'][$event_id];
				$event_cost = abs($event_price);
				$event_individual_cost[$event_id] = $event_cost;
			}
			else{
				$event_price = $_POST['price_id'][$event_id];

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




function event_espresso_show_price_types($event_id, $is_donation = false) {

	global $wpdb, $org_options;

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');




	$SQL = "SELECT ept.id, ept.event_cost, ept.surcharge, ept.surcharge_type, ept.price_type, edt.allow_multiple, edt.additional_limit ";
	$SQL .= "FROM " . EVENTS_PRICES_TABLE . " ept ";
	$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . "  edt ON ept.event_id =  edt.id ";
	$SQL .= "WHERE event_id=%d ORDER BY ept.id ASC";
	// filter SQL statement
	$SQL = apply_filters( 'filter_hook_espresso_group_price_dropdown_sql', $SQL );
	// get results
	$results = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ));

	if ($wpdb->num_rows > 0) {?>
	 <div class="price_list_wrapper" <?php if($is_donation){?>style="display:none;"<?php } ?>>
		<span class="event-detail-label">Price:</span>
		<table class="price_list">
		<?php
		foreach ($results as $result) {

			$surcharge = '';

			if ($result->surcharge > 0 && $result->event_cost > 0.00) {
				$surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . __('Surcharge', 'event_espresso');
				if ($result->surcharge_type == 'pct') {
					$surcharge = " + {$result->surcharge}% " . __('Surcharge', 'event_espresso');
				}
			}

			?>
			<tr>
				<td class="price_type"><?php echo $result->price_type; ?></td>
				<td class="price">
					<?php
						if (!isset($message))
							$message = '';
						echo $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ' ' . $surcharge;
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<div class="addToCartInstructions">
		<em>To purchase tickets or register for this event, please add this event to your cart.</em>
	</div>
	</div>

	<?php
	}

}



function event_espresso_group_price_dropdown($event_id, $label = 1, $multi_reg = 0, $value = '') {

	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	/*
	 * find out pricing type.
	 * - If multiple price options, for each one
	 * -- Create a row in a table with a name
	 * -- qty dropdown
	 *
	 */
	 $is_donation = false;
	 if(isset($_SESSION['espresso_session']['events_in_session'][$event_id]) &&
	 	(
	 		isset($_SESSION['espresso_session']['events_in_session'][$event_id]['categories']) &&
	 		is_array($_SESSION['espresso_session']['events_in_session'][$event_id]['categories']) &&
	 		in_array('donation',$_SESSION['espresso_session']['events_in_session'][$event_id]['categories'])
	 	) OR (
			$_SESSION['espresso_session']['events_in_session'][$event_id]['is_donation'] == true
		)
	 	){

			$is_donation = true;
	 }

	//Will make the name an array and put the time id as a key so we
	//know which event this belongs to
	$multi_name_adjust = $multi_reg == 1 ? "[$event_id]" : '';

	$SQL = "SELECT ept.id, ept.event_cost, ept.surcharge, ept.surcharge_type, ept.price_type, edt.allow_multiple, edt.additional_limit ";
	$SQL .= "FROM " . EVENTS_PRICES_TABLE . " ept ";
	$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . "  edt ON ept.event_id =  edt.id ";
	$SQL .= "WHERE event_id=%d ORDER BY ept.id ASC";
	// filter SQL statement
	$SQL = apply_filters( 'filter_hook_espresso_group_price_dropdown_sql', $SQL );
	// get results
	$results = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ));

	if ($wpdb->num_rows > 0) {
		if($is_donation){
			$price_id = $results[0]->id;
			?>
			<div class="event_form_field">
				<?php //print_a($_SESSION['espresso_session']['events_in_session'][$event_id]); ?>
				<label for="donation_amount[<?php echo $event_id; ?>]" class="ee-reg-page-questions">Donation Amount</label>
				<input
					type="text"
					name="donation_amount[<?php echo $event_id; ?>]"
					id="donation_amount[<?php echo $event_id; ?>]"
					class="donation_amount price_id required  ee-reg-page-questions ee-reg-page-text-input numeric-only"
					value="0.00" />
					<input type="hidden" name="price_id[<?php echo $event_id; ?>][<?php echo $price_id; ?>]" value="1" />
					<input type="hidden" name="is_donation[<?php echo $event_id; ?>]" value="true" />
			</div>

			<?php
			return;
		}

		$attendee_limit = 1;
		//echo $label==1?'<label for="event_cost">' . __('Choose an Option: ','event_espresso') . '</label>':'';
		//echo '<input type="radio" name="price_option' . $multi_name_adjust . '" id="price_option-' . $event_id . '">';
		?>

<table class="price_list">
<?php
		$available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces');
		foreach ($results as $result) {

			//Setting this field for use on the registration form
			$_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['price_type'] = stripslashes_deep($result->price_type);
			// Addition for Early Registration discount
			if ($early_price_data = early_discount_amount($event_id, $result->event_cost)) {
				$result->event_cost = $early_price_data['event_price'];
				$message = __(' Early Pricing', 'event_espresso');
			}


			$surcharge = '';

			if ($result->surcharge > 0 && $result->event_cost > 0.00) {
				$surcharge = " + {$org_options['currency_symbol']}{$result->surcharge} " . __('Surcharge', 'event_espresso');
				if ($result->surcharge_type == 'pct') {
					$surcharge = " + {$result->surcharge}% " . __('Surcharge', 'event_espresso');
				}
			}

			?>
<tr>
	<td class="price_type"><?php echo $result->price_type; ?></td>
	<td class="price"><?php
						if (!isset($message))
							$message = '';
						echo $org_options['currency_symbol'] . number_format($result->event_cost, 2) . $message . ' ' . $surcharge;
						?></td>
	<td class="selection">
		<?php
			$attendee_limit = 1;
			$att_qty = empty($_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['attendee_quantity']) ? '' : $_SESSION['espresso_session']['events_in_session'][$event_id]['price_id'][$result->id]['attendee_quantity'];

			if ($result->allow_multiple == 'Y') {
				$attendee_limit = $result->additional_limit;
				if ($available_spaces != 'Unlimited') {
					$attendee_limit = ($attendee_limit <= $available_spaces) ? $attendee_limit : $available_spaces;
				}
			}

			event_espresso_multi_qty_dd( $event_id, $result->id,  $attendee_limit, $att_qty );

		?>
	</td>
</tr>
<?php
		}
		?>
<tr>
	<td colspan="3" class="reg-allowed-limit">
		<?php printf(__("You can register a maximum of %d attendees for this event.", 'event_espresso'), $attendee_limit); ?>
		</td>
	</tr>
</table>

<input type="hidden" id="max_attendees-<?php echo $event_id; ?>" class="max_attendees" value= "<?php echo $attendee_limit; ?>" />
<?php
		} else if ($wpdb->num_rows == 0) {
			echo '<span class="free_event">' . __('Free Event', 'event_espresso') . '</span>';
		echo '<input type="hidden" name="payment' . $multi_name_adjust . '" id="payment-' . $event_id . '" value="' . __('free event', 'event_espresso') . '">';
	}

}



function event_espresso_additional_attendees( $event_id = 0, $additional_limit = 2, $available_spaces = 999, $label = '', $show_label = true, $event_meta = '', $qstn_class = '' ) {
	global $espresso_premium;
	$event_id = $event_id == 0 ? $_REQUEST['event_id'] : $event_id;

	if ($event_meta == 'admin') {
		$admin = true;
		$event_meta = '';
	}
	if ($event_meta == '' && ($event_id != '' || $event_id != 0)) {
		$event_meta = event_espresso_get_event_meta($event_id);
	}

	//If the additional attednee questions are empty, then default to the first question group
	if (empty($event_meta['add_attendee_question_groups']))
		$event_meta['add_attendee_question_groups'] = array(1 => 1);


	$i = 0;
	if ( (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1) || $espresso_premium == FALSE ) {

		$label = $label == '' ? __('Number of Tickets', 'event_espresso') : $label;
		$html = '<p class="espresso_additional_limit highlight-bg">';
		$html .= $show_label == true ? '<label for="num_people">' . $label . '</label>' : '';
		$html .= '<select name="num_people" id="num_people-' . $event_id . '" style="width:70px;">';
		while (($i < $additional_limit) && ($i < $available_spaces)) {
			$i++;
			$html .= '<option value="' . $i . '">' . $i . '</option>';
		}
		$html .= '</select>';
		//$html .= '<br />';
		$html .= '<input type="hidden" name="espresso_addtl_limit_dd" value="true">';
		$html .= '</p>';
		$buffer = '';

	} else {

//			while (($i < $additional_limit) && ($i < $available_spaces)) {
//				$i++;
//			}
		$i = min( $additional_limit, $available_spaces ) - 1;

		$html = '';
		// fixed for translation string, previous string untranslatable - http://events.codebasehq.com/projects/event-espresso/tickets/11
		$html .= '<a id="add-additional-attendee-0" rel="0" class="event-button event-button-add-additional-attendees add-additional-attendee-lnk additional-attendee-lnk">' . __('Add More Attendees? (click to toggle, limit ', 'event_espresso');
		$html .= $i . ')</a>';


		//ob_start();
		$attendee_form = '<div id="additional_attendee_XXXXXX" class="espresso_add_attendee">';
		$attendee_form .= '<h3 class="section-heading">' . __('Additional Attendee #', 'event_espresso') . 'XXXXXX</h4>';

		if ($event_meta['additional_attendee_reg_info'] == 2) {
			$attendee_form .= '<div class="event-question-field">';
			$attendee_form .= '<input type="text" name="x_attendee_fname[XXXXXX]" class="input" placeholder="'.__('First Name:', 'event_espresso').'" />';
			$attendee_form .= '</div>';
			$attendee_form .= '<div class="event-question-field">';
			$attendee_form .= '<input type="text" name="x_attendee_lname[XXXXXX]" class="input" placeholder="'.__('Last Name:', 'event_espresso').'" />';
			$attendee_form .= '</div>';
			$attendee_form .= '<div class="event-question-field">';
			$attendee_form .= '<input type="text" name="x_attendee_email[XXXXXX]" class="input" placeholder="'.__('Email:', 'event_espresso').'" />';
			$attendee_form .= '</div>';
		} else {
			$meta = array("x_attendee" => true);
			if(!empty($admin)) {
				$meta['admin_only'] = true;
			}
			$attendee_form .= event_espresso_add_question_groups( $event_meta['add_attendee_question_groups'], '', null, 0, $meta, $qstn_class );
		}
		$attendee_form .= '<div class="espresso_add_subtract_attendees">';

		$attendee_form .= '
		<a href="#" id="remove-additional-attendee-XXXXXX" rel="XXXXXX" class="event-button event-button-remove-attendee ee_view_cart remove-additional-attendee-lnk additional-attendee-lnk" title="' . __('Remove the above Attendee', 'event_espresso') . '">
			<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" alt="' . __('Remove Attendee', 'event_espresso') . '" />
			' . __('Remove the above Attendee:', 'event_espresso') . '
		</a>';

		$attendee_form .= '
		<a href="#"  id="add-additional-attendee-XXXXXX" rel="XXXXXX" class="event-button event-button-add-attendee add-additional-attendee-lnk additional-attendee-lnk" title="' . __('Add an Additonal Attendee', 'event_espresso') . '">
			<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/add.png" alt="' . __('Add an Additonal Attendee', 'event_espresso') . '" />
			' . __('Add an Additonal Attendee:', 'event_espresso') . '
		</a>';


		$attendee_form .= '</div></div>';

		wp_register_script( 'espresso_add_reg_attendees', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_add_reg_attendees.js', array('jquery'), '0.1', TRUE );
		wp_enqueue_script( 'espresso_add_reg_attendees' );

		$espresso_add_reg_attendees = array( 'additional_limit' => $additional_limit, 'attendee_form' => stripslashes( $attendee_form ));
		wp_localize_script( 'espresso_add_reg_attendees', 'espresso_add_reg_attendees', $espresso_add_reg_attendees );
	}
	return $html;
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

function event_espresso_add_attendees_to_db_multi() {

	//echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	global $wpdb, $org_options;


	//echo "<h1>DEBUG: In ".__FILE__." ----  ". __FUNCTION__."</h1>";

	$donations = array();

	if ( espresso_verify_recaptcha() ) {

		$primary_registration_id = NULL;
		$multi_reg = true;

		$events_in_session = $_SESSION['espresso_session']['events_in_session'];
		//echo "Events In Session<br />";
		//print_a($events_in_session);
		if (event_espresso_invoke_cart_error($events_in_session)) {
			return false;
		}

		$count_of_events = count($events_in_session);
		$current_session_id = $_SESSION['espresso_session']['id'];
		$biz_name = $count_of_events . ' ' . $org_options['organization'] . __(' events', 'event_espresso');
		$event_cost = $_SESSION['espresso_session']['grand_total'];
		$event_cost = apply_filters('filter_hook_espresso_cart_grand_total', $event_cost);

		// If there are events in the session, add them one by one to the attendee table
		if ($count_of_events > 0) {

			//first event key will be used to find the first attendee
			$first_event_id = key($events_in_session);

			reset($events_in_session);
			foreach ($events_in_session as $event_id => $event) {

				//is this event a donation?
				 $is_donation = false;
				 if(isset($event['categories']) && is_array($event['categories']) && in_array('donation',$event['categories'])){
					$is_donation = true;
					$donations[$event_id] = $event['cost'];
				 }

				$event_meta = event_espresso_get_event_meta($event_id);
				$session_vars['data'] = $event;

				if ( is_array( $event['event_attendees'] )) {
					$counter = 1;
					//foreach price type in event attendees
					foreach ( $event['event_attendees'] as $price_id => $event_attendees ) {

						$session_vars['data'] = $event;

						foreach ( $event_attendees as $attendee_index => $attendee) {


							//echo "Attendee $attendee_index: ";
							$attendee['price_id'] = $price_id;
							//this has all the attendee information, name, questions....
							$session_vars['event_attendees'] = $attendee;
							$session_vars['data']['price_type'] = stripslashes_deep($event['price_id'][$price_id]['price_type']);


							//print_a($attendee);
							//$stein_upgrade = do_shortcode('[EE_ANSWER q="13" a="'.$attendee_id.'"]');
							//echo "Stein Upgrade: ".$stein_upgrade."<br />";
							$attendee['stein_upgrade_cost'] = 0;
							if(isset($attendee['SINGLE_13'])){
								//echo "Gotta add ".$attendee['SINGLE_13']." to cost<br />";
								preg_match('/\$([0-9]+[\.]*[0-9]*)/', $attendee['SINGLE_13'], $match);
								if(isset($match[1]) && is_numeric($match[1])){
									$attendee['stein_upgrade_cost'] = $match[1];
								}
								else{
									$attendee['stein_upgrade_cost'] = 0;
								}
								//$session_vars['data']['price_type'] .= " - ".$attendee['SINGLE_13'];
								$events_in_session[$event_id]['event_attendees'][$price_id][$attendee_index]['stein_upgrade_cost'] = $attendee['stein_upgrade_cost'];
								$_SESSION['espresso_session']['events_in_session'][$event_id]['event_attendees'][$price_id][$attendee_index]['stein_upgrade_cost'] = $attendee['stein_upgrade_cost'];
								$session_vars['data']['event_attendees'][$price_id][$attendee_index]['stein_upgrade_cost'] = $attendee['stein_upgrade_cost'];
								$session_vars['event_attendees']['stein_upgrade_cost'] = $attendee['stein_upgrade_cost'];
								//echo "That's $".$stein_upgrade_cost."!!!<br />";
							}
							//print_a($session_vars);

							if ( isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1 ) {

								$num_people = (int)$event['price_id'][$price_id]['attendee_quantity'];
								$session_vars['data']['num_people'] = empty($num_people) || $num_people == 0 ? 1 : $num_people;

							}

//							echo "<hr />Session Vars:";
//							print_a($session_vars);
//							echo "<hr />";
							// ADD ATTENDEE TO DB
							$return_data = event_espresso_add_attendees_to_db( $event_id, $session_vars, TRUE );

							$tmp_registration_id = $return_data['registration_id'];
							$notifications = $return_data['notifications'];

							if ($primary_registration_id === NULL) {
								$primary_registration_id = $tmp_registration_id;
							}

							$SQL = "SELECT * FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . "  ";
							$SQL .= "WHERE primary_registration_id = %s AND registration_id = %s";
							$check = $wpdb->get_row( $wpdb->prepare( $SQL, $primary_registration_id, $tmp_registration_id ));

							if ( $check === NULL) {
								$tmp_data = array( 'primary_registration_id' => $primary_registration_id, 'registration_id' => $tmp_registration_id );
								$wpdb->insert( EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE, $tmp_data, array( '%s', '%s' ));
							}
						$counter++;

						}
					}
				}
			}



			$SQL = "SELECT a.*, ed.id AS event_id, ed.event_name, dc.coupon_code_price, dc.use_percentage ";
			$SQL .= "FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
			$SQL .= "LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
			$SQL .= "WHERE attendee_session=%s ORDER BY a.id ASC";

			$attendees			= $wpdb->get_results( $wpdb->prepare( $SQL, $current_session_id ));
			$quantity			= 0;
			$final_total		= 0;
			$sub_total			= 0;
			$discounted_total	= 0;
			$discount_amount	= 0;
			$is_coupon_pct		= ! empty( $attendees[0]->use_percentage ) && $attendees[0]->use_percentage == 'Y' ? TRUE : FALSE;

			//printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			foreach ($attendees as $attendee) {




				if ( $attendee->is_primary ) {
					$primary_attendee_id	= $attendee_id = $attendee->id;
					$coupon_code			= $attendee->coupon_code;
					$event_id				= $attendee->event_id;
					$fname					= $attendee->fname;
					$lname					= $attendee->lname;
					$address				= $attendee->address;
					$city					= $attendee->city;
					$state					= $attendee->state;
					$zip					= $attendee->zip;
					$attendee_email			= $attendee->email;
					$registration_id		= $attendee->registration_id;
				}


				$final_total		+= $attendee->final_price;
				$sub_total			+= (int)$attendee->quantity * $attendee->orig_price;
				$discounted_total	+= (int)$attendee->quantity * $attendee->final_price;
				$quantity			+= (int)$attendee->quantity;

			}
			$discount_amount	= $sub_total - $discounted_total;
			$total_cost			= $discounted_total;
			$total_cost			= $total_cost < 0 ? 0.00 : (float)$total_cost;


			if ( function_exists( 'espresso_update_attendee_coupon_info' ) && $primary_attendee_id && ! empty( $attendee->coupon_code )) {
				espresso_update_attendee_coupon_info( $primary_attendee_id, $attendee->coupon_code  );
			}

			if ( function_exists( 'espresso_update_groupon' ) && $primary_attendee_id && ! empty( $coupon_code )) {
				espresso_update_groupon( $primary_attendee_id, $coupon_code  );
			}

			espresso_update_primary_attendee_total_cost( $primary_attendee_id, $total_cost, __FILE__ );

			if ( ! empty( $notifications['coupons'] ) || ! empty( $notifications['groupons'] )) {
				echo '<div id="event_espresso_notifications" class="clearfix event-data-display no-hide">';
				echo $notifications['coupons'];
				// add space between $coupon_notifications and  $groupon_notifications ( if any $groupon_notifications exist )
				echo ! empty( $notifications['coupons'] ) && ! empty( $notifications['groupons'] ) ? '<br/>' : '';
				echo $notifications['groupons'];
				echo '</div>';
			}

			//Post the gateway page with the payment options
				if ( $total_cost > 0 ) {
?>


<div class="espresso_payment_overview" >
  <h2 class="title">
	<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
<div class="event-details" >
	<p>
	Please review your order before making your purchase. You will receive an e-mail confirmation and printable electronic tickets. Your QR-coded tickets will be scanned upon entrance.
	</p>

	<div class="event-messages ui-state-highlight"> <span class="ui-icon ui-icon-alert"></span>
		<p class="instruct">
			<?php _e('Your transaction is not complete until payment is received.', 'event_espresso'); ?>
		</p>
	</div>
	<p><?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?></p>
	<table>
		<?php
		foreach ($attendees as $attendee) {
			$is_donation = false;
			if(isset($donations[$attendee->event_id])){
				$is_donation = true;
			}
		?>
		<tr>
			<td width="70%">
				<?php echo '<strong>'.stripslashes_deep($attendee->event_name ) . '</strong>'?>&nbsp;-&nbsp;<?php echo stripslashes_deep( $attendee->price_option ) ?> <?php echo $attendee->final_price < $attendee->orig_price ? '<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:.8em;">' . $org_options['currency_symbol'] . number_format($attendee->orig_price - $attendee->final_price, 2) . __(' discount per registration','event_espresso') . '</span>' : ''; ?><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;<?php echo __(($is_donation?'Donor:':'Attendee:'),'event_espresso') . ' ' . stripslashes_deep($attendee->fname . ' ' . $attendee->lname) ?>
			</td>
			<td width="10%"><?php if(!$is_donation) {echo $org_options['currency_symbol'] . number_format($attendee->final_price, 2);} ?></td>
			<td width="10%"><?php if(!$is_donation) {echo 'x ' . (int)$attendee->quantity;} ?></td>
			<td width="10%" style="text-align:right;"><?php echo $org_options['currency_symbol'] . number_format( $attendee->final_price * (int)$attendee->quantity, 2) ?></td>
		</tr>
		<?php } ?>

		<tr>
			<td colspan="3"><?php _e('Sub-Total:','event_espresso'); ?></td>
			<td colspan="" style="text-align:right"><?php echo $org_options['currency_symbol'] . number_format($sub_total, 2); ?></td>
		</tr>
		<?php
				if (!empty($discount_amount)) {
						?>
		<tr>
			<td colspan="3"><?php _e('Total Discounts:','event_espresso'); ?></td>
			<td colspan="" style="text-align:right"><?php echo '-' . $org_options['currency_symbol'] . number_format( $discount_amount, 2 ); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="3"><strong class="event_espresso_name">
				<?php _e('Total Amount due: ', 'event_espresso'); ?>
				</strong></td>
			<td colspan="" style="text-align:right"><?php echo $org_options['currency_symbol'] ?><?php echo number_format($total_cost,2); ?></td>
		</tr>
	</table>
	<p class="event_espresso_refresh_total">
		<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart">
		<?php _e('Edit Cart', 'event_espresso'); ?>
		</a>
		<?php /*_e(' or ', 'event_espresso'); ?>
		<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&registration_id=<?php echo $registration_id; ?>&id=<?php echo $attendee_id; ?>&regevent_action=edit_attendee&primary=<?php echo $primary_attendee_id; ?>&event_id=<?php echo $event_id; ?>&attendee_num=1">
		<?php _e('Edit Registrant Information', 'event_espresso'); ?>
		</a>
		<?php */ ?>
	</p>
</div>
</div>
<br/><br/>
<?php
					//Show payment options
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")) {
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
				} else {
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
				}
				//Check to see if the site owner wants to send an confirmation eamil before payment is recieved.
				if ($org_options['email_before_payment'] == 'Y') {
					event_espresso_email_confirmations(array('session_id' => $_SESSION['espresso_session']['id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true));
				}

			} elseif ( $total_cost == 0.00 ) {
				?>
<p>
<?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
<strong><?php echo stripslashes_deep( $biz_name ) ?></strong></p>
<p>
<?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
</p>
<?php
					event_espresso_email_confirmations(array('session_id' => $_SESSION['espresso_session']['id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true));

				event_espresso_clear_session();
			}
		}

	}

}

function event_espresso_send_payment_notification($atts) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	global $wpdb, $org_options;
	//Extract the attendee_id and registration_id
	extract($atts);

	$registration_id = is_array( $registration_id ) ? $registration_id[0] : $registration_id;

	if ( empty( $registration_id ) && isset( $attendee_id )) {
		$registration_id = espresso_registration_id($attendee_id);
	}

	if ( empty( $registration_id )) {
		return __('No Registration ID was supplied', 'event_espresso');
	}

	//Get the attendee  id or registration_id and create the sql statement
	$SQL = "SELECT a.* FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$SQL .= " WHERE a.registration_id = %s ";
	$attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));

	if ($org_options['default_mail'] == 'Y') {
		foreach ($attendees as $attendee) {
			event_espresso_email_confirmations(array('attendee_id' => $attendee->id, 'send_admin_email' => 'false', 'send_attendee_email' => 'true', 'custom_data' => array('email_type' => 'payment', 'payment_subject' => $org_options['payment_subject'], 'payment_message' => $org_options['payment_message'])));
		}
	}

	return;
}

function event_espresso_load_checkout_page() {

	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );
//		printr( $events_in_session, '$events_in_session  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	$event_count = count( $events_in_session );

	if (event_espresso_invoke_cart_error($events_in_session))
		return false;

	//echo "<pre>", print_r( $_SESSION ), "</pre>";
	if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php")) {
		require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "multi_registration_page.php"); //This is the path to the template file if available
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/multi_registration_page.php");
	}

	$response['html'] = '';
	//if the counte of event in the session >0, ok to process
	if ( $event_count > 0 ) {
		//for each one of the events in session, grab the event ids, drop into temp array, impode to construct SQL IN clasue (IN(1,5,7))
		foreach ($events_in_session as $event) {
			// echo $event['id'];
			if (is_numeric($event['id']))
				$events_IN[] = $event['id'];
		}

		$events_IN = implode(',', $events_IN);


		$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
		$sql .= " WHERE e.id in ($events_IN) ";
		$sql .= " ORDER BY e.start_date ";

		$result = $wpdb->get_results($sql);

		//will hold data to pass to the form builder function
		$meta = array();
		//echo "<pre>", print_r($_POST), "</pre>";
		?>
<br />
<h2>Get Registered!</h2>
<p>
<h3>Please fill in the fields below.</h3>
If you are purchasing tickets for multiple events
or contests, for your convenience, simply click "yes" below to automatically input
your information into all required registration forms. <br/> <br/>
<em>Please review this page carefully before proceeding to checkout, as some events may require additional information.</em>
</p>
<div class = "event_espresso_form_wrapper">
<form id="event_espresso_checkout_form" method="post" action="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=post_multi_attendee">
	<?php
				$err = '';
				$edit_cart_link = '<a href="?page_id='.$org_options['event_page_id'].'&regevent_action=show_shopping_cart" rel="nofollow" class="btn_event_form_submit inline-link">'.__('Edit Cart', 'event_espresso').'</a>';

				ob_start();
				//will be used if sj is off or they somehow select more than allotted attendees
				$show_checkout_button = true;
				$counter = 1;
				foreach ($result as $r) {

					$event_id = $r->id;
					$event_meta = unserialize($r->event_meta);

					$event_meta['is_active'] = $r->is_active;
					$event_meta['event_status'] = $r->event_status;
					$event_meta['start_time'] = empty($r->start_time) ? '' : $r->start_time;
					$event_meta['start_date'] = $r->start_date;

					$event_meta['registration_startT'] = $r->registration_startT;
					$event_meta['registration_start'] = $r->registration_start;

					$event_meta['registration_endT'] = $r->registration_endT;
					$event_meta['registration_end'] = $r->registration_end;

					$r->event_meta = serialize( $event_meta );

					//If the event is still active, then show it.
					if (event_espresso_get_status($event_id) == 'ACTIVE') {

						//DEPRECATED
						//Pull the detail from the event detail row, find out which route to take for additional attendees
						//Can be 1) no questios asked, just record qty 2) ask for only personal info 3) ask all attendees the full reg questions
						//#1 is not in use as of ..P35
						$meta['additional_attendee_reg_info'] = (is_array($event_meta) && array_key_exists('additional_attendee_reg_info', $event_meta) && $event_meta['additional_attendee_reg_info'] > 1) ? $event_meta['additional_attendee_reg_info'] : 2;

						//In case the js is off, the attendee qty dropdowns will not
						//function properly, allowing for registering more than allowed limit.
						//The info from the following 5 lines will determine
						//if they have surpassed the limit.
						$available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces');

						$attendee_limit = $r->additional_limit + 1;

						if ($available_spaces != 'Unlimited')
							$attendee_limit = ($attendee_limit <= $available_spaces) ? $attendee_limit : $available_spaces;

						$total_attendees_per_event = 0;

						$attendee_overflow = false;

						//assign variable
						$meta['additional_attendee'] = 0;
						$meta['attendee_number'] = 1;

						//used for "Copy From" dropdown on the reg form
						$meta['copy_link'] = $counter;

						//Grab the event price ids from the session.  All event must have at least one price id
						$price_ids = $events_in_session[$event_id]['price_id'];




						//Just to make sure, check if is array
						if (is_array($price_ids)) {
							//for each one of the price ids, load an attendee question section
							foreach ($price_ids as $_price_id => $val) {

								if (isset($val['attendee_quantity']) && $val['attendee_quantity'] > 0) { //only show reg form if attendee qty is set
									$meta['price_id'] = $_price_id; //will be used to keep track of the attendee in the group
									$meta['price_type'] = $val['price_type']; //will be used to keep track of the attendee in the group
									$meta['attendee_quantity'] = $val['attendee_quantity'];
									$total_attendees_per_event += $val['attendee_quantity'];
									multi_register_attendees( null, $event_id, $meta, $r );
									$meta['attendee_number'] += $val['attendee_quantity'];
								}
							}

							//If they have selected more than allowed max group registration
							//Dispaly an error instead of the continue button
							if ($total_attendees_per_event > $attendee_limit || $total_attendees_per_event == 0) {
								$attendee_overflow = true;
								$show_checkout_button = false;
							}
						}


						if ($attendee_overflow) {

							$err .= "<div class='multi_reg_cart_block event_espresso_error'><p><em>Attention</em>";
							$err .= sprintf(__("For %s, please make sure to select between 1 and %d attendees or delete it from your cart.", 'event_espresso'), stripslashes($r->event_name), $attendee_limit);
							$err .= '<span class="remove-cart-item"><img class="ee_delete_item_from_cart" id="cart_link_' . $event_id . '" alt="Remove this item from your cart" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" /></span> ';
							$err .= "</p></div>";
						}


						$counter++;
					}
				}

				$output = ob_get_contents();
				ob_end_clean();

				if ($err != '')
					echo $err;

				if ($show_checkout_button) {

					echo $output;

					//Recaptcha portion
					if ( $org_options['use_captcha'] == 'Y'  && ! is_user_logged_in()  ) { // && isset( $_REQUEST['edit_details'] ) && $_REQUEST['edit_details'] != 'true'
						// this is probably superfluous because it's already being loaded elsewhere...trying to cover all my bases ~c  ?>
						<script type="text/javascript">
							var RecaptchaOptions = {
								theme : '<?php echo $org_options['recaptcha_theme'] == '' ? 'red' : $org_options['recaptcha_theme']; ?>',
								lang : '<?php echo $org_options['recaptcha_language'] == '' ? 'en' : $org_options['recaptcha_language']; ?>'
							};
						</script>
					<?php
						if ( ! function_exists( 'recaptcha_get_html' )) {
							require_once( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/recaptchalib.php' );
						}//End require captcha library
						# the response from reCAPTCHA
						$resp = true;
						# the error code from reCAPTCHA, if any
						$error = null;
						?>
						<p class="event_form_field" id="captcha-<?php echo $event_id; ?>">
							<?php _e('Anti-Spam Measure: Please enter the following phrase', 'event_espresso'); ?>
							<?php echo recaptcha_get_html($org_options['recaptcha_publickey'], $error, is_ssl() ? true : false); ?>
						</p>
		<?php } //End use captcha	?>



			<input type="submit" class="event-button" name="payment_page" value="<?php _e('Confirm and go to payment page', 'event_espresso'); ?>&nbsp;&raquo;" />
			<span style="padding-left:20px"> - <?php _e('or', 'event_espresso'); ?> - </span>

	<?php } ?>
			<!--<p id="event_espresso_edit_cart">-->
				<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart" class="btn_event_form_submit inline-link">
					<?php _e('Edit Cart', 'event_espresso'); ?>
				</a>
			<!--</p>-->

</form>
</div>

<script>
jQuery(function(){
	//Registration form validation
	//jQuery('#event_espresso_checkout_form').validate();

		jQuery("#event_espresso_checkout_form").validate({
		  rules: {
		    email: "required",
		    TEXT_38: {
		      equalTo: "#email"
		    }
		  },
		  ignore: ".equal-to"
		});
		/*
		jQuery(".equal-to").each(function(){
			var equal_to_name = jQuery(this).attr('data-equal-to');
			jQuery(this).change(function(){
				console.log('Comparing '+jQuery(this).val() +' and '+jQuery('[name="'+equal_to_name+'"]').val()+'.');
				if(jQuery(this).val() != jQuery('[name="'+equal_to_name+'"]').val()){
					console.log('error!');
					console.log(jQuery(this).attr('class'));
					jQuery(this).removeClass('valid');
					jQuery(this).addClass('error');
					console.log(jQuery(this).attr('class'));
				}
				else{
					console.log('all good!');
					console.log(jQuery(this).attr('class'));
					//jQuery(this).removeClass('error');
					//jQuery(this).addClass('valid');
					console.log(jQuery(this).attr('class'));
				}
			});
		});
		*/
});
</script>
<?php
		}


		//echo json_encode( $response );
	//die();
}

function event_espresso_add_attendees_to_db( $event_id = NULL, $session_vars = NULL, $skip_check = FALSE ) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	//echo "<h1>DEBUG: In ".__FILE__." ----  ". __FUNCTION__."</h1>";

	//Security check using nonce
	if ( empty($_POST['reg_form_nonce']) || !wp_verify_nonce($_POST['reg_form_nonce'],'reg_nonce') ){
		print '<h3 class="error">'.__('Sorry, there was a security error and your registration was not saved.', 'event_espresso').'</h3>';
		return;
	}

	global $wpdb, $org_options, $espresso_premium;

	//Defaults
	$data_source = $_POST;
	$att_data_source = $_POST;
	$multi_reg = FALSE;
	$notifications = array( 'coupons' => '', 'groupons' => '' );
	$donations = array();

	if ( ! is_null($event_id) && ! is_null($session_vars)) {
		//event details, ie qty, price, start..
		$data_source = $session_vars['data'];
		//event attendee info ie name, questions....
		$att_data_source = $session_vars['event_attendees'];
		$multi_reg = TRUE;
	} else {
		$event_id = absint( $data_source['event_id'] );
	}

	$is_donation = false;
	if(isset($data_source['categories']) &&
		is_array($data_source['categories']) &&
		in_array('donation',$data_source['categories'])){

		$is_donation = true;
		$donations[$event_id] = $data_source['event_cost'];
	}

	//Check for existing registrations
	//check if user has already hit this page before ( ie: going back n forth thru reg process )
	$prev_session_id = isset($_SESSION['espresso_session']['id']) && !empty($_SESSION['espresso_session']['id']) ? $_SESSION['espresso_session']['id'] : '';
	if ( is_null( $session_vars )) {
		$SQL = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session=%s";
		$prev_session_attendee_id = $wpdb->get_col( $wpdb->prepare( $SQL, $_SESSION['espresso_session']['id'] ));
		if ( ! empty( $prev_session_attendee_id )) {
			$_SESSION['espresso_session']['id'] = array();
			ee_init_session();
		}
	}


	//Check to see if the registration id already exists
	$incomplete_filter = ! $multi_reg ? " AND payment_status ='Incomplete'" : '';
	$SQL = "SELECT attendee_session, id, registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session =%s AND event_id = %d";
	$SQL .= $incomplete_filter;
	$check_sql = $wpdb->get_results($wpdb->prepare( $SQL, $prev_session_id, $event_id ));
	$nmbr_of_regs = $wpdb->num_rows;
	static $loop_number = 1;


	// delete previous entries from this session in case user is jumping back n forth between pages during the reg process
	if ( $nmbr_of_regs > 0 && $loop_number == 1 ) {
		if ( !isset( $data_source['admin'] )) {

			$SQL = "SELECT id, registration_id FROM " . EVENTS_ATTENDEE_TABLE . ' ';
			$SQL .= "WHERE attendee_session = %s ";
			$SQL .= $incomplete_filter;

			if ( $mer_attendee_ids = $wpdb->get_results($wpdb->prepare( $SQL, $prev_session_id ))) {
				foreach ( $mer_attendee_ids as $v ) {
					//Added for seating chart addon
					if ( defined('ESPRESSO_SEATING_CHART')) {
						$SQL = "DELETE FROM " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . ' ';
						$SQL .= "WHERE attendee_id = %d";
						$wpdb->query($wpdb->prepare( $SQL, $v->id ));
					}
					//Delete the old attendee meta
					do_action('action_hook_espresso_save_attendee_meta', $v->id, 'original_attendee_details', '', TRUE);
				}
			}

			$SQL = "DELETE t1, t2 FROM " . EVENTS_ATTENDEE_TABLE . "  t1 ";
			$SQL .= "JOIN  " . EVENTS_ANSWER_TABLE . " t2 on t1.id = t2.attendee_id ";
			$SQL .= "WHERE t1.attendee_session = %s ";
			$SQL .= $incomplete_filter;
			$wpdb->query($wpdb->prepare( $SQL, $prev_session_id ));

			//Added by Imon
			// First delete attempt might fail if there is no data in answer table. So, second attempt without joining answer table is taken bellow -
			$SQL = " DELETE FROM " . EVENTS_ATTENDEE_TABLE . ' ';
			$SQL .= "WHERE attendee_session = %s ";
			$SQL .= $incomplete_filter;
			$wpdb->query($wpdb->prepare( $SQL, $prev_session_id ));

			// Clean up any attendee information from attendee_cost table where attendee is not available in attendee table
			event_espresso_cleanup_multi_event_registration_id_group_data();

		}
	}
	$loop_number++;

	//Check if added admin
	$skip_check = $skip_check || isset( $data_source['admin'] ) ? TRUE : FALSE;

	//If added by admin, skip the recaptcha check
	if ( espresso_verify_recaptcha( $skip_check )) {

		array_walk_recursive($data_source, 'wp_strip_all_tags');
		array_walk_recursive($att_data_source, 'wp_strip_all_tags');

		array_walk_recursive($data_source, 'espresso_apply_htmlentities');
		array_walk_recursive($att_data_source, 'espresso_apply_htmlentities');

		// Will be used for multi events to keep track of event id change in the loop, for recording event total cost for each group
		static $temp_event_id = '';
		//using this var to keep track of the first attendee
		static $attendee_number = 1;
		static $total_cost = 0;
		static $primary_att_id = NULL;

		if ($temp_event_id == '' || $temp_event_id != $event_id) {
			$temp_event_id = $event_id;
			$event_change = 1;
		} else {
			$event_change = 0;
		}

//		echo "<h1>Data Source $loop_number</h1>";
//		print_a($data_source);
//		echo "<h1>ATT Data Source $loop_number</h1>";
//		print_a($att_data_source);

		$event_cost = isset($data_source['cost']) && $data_source['cost'] != '' ? $data_source['cost'] : 0.00;

		$final_price = $event_cost;


		$fname		= isset($att_data_source['fname']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['fname']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$lname		= isset($att_data_source['lname']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['lname']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$address	= isset($att_data_source['address']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['address']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$address2	= isset($att_data_source['address2']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['address2']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$city		= isset($att_data_source['city']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['city']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$state		= isset($att_data_source['state']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['state']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$zip		= isset($att_data_source['zip']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['zip']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$phone		= isset($att_data_source['phone']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['phone']) ), ENT_QUOTES, 'UTF-8' ) : '';
		$email		= isset($att_data_source['email']) ? html_entity_decode( trim( sanitize_text_field($att_data_source['email']) ), ENT_QUOTES, 'UTF-8' ) : '';


		$SQL = "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . " WHERE id = %d";
		$questions = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));
		$event_meta = maybe_unserialize( $questions->event_meta );
		$questions = maybe_unserialize( $questions->question_groups );

		// Adding attenddee specific cost to events_attendee table
		if (isset($data_source['admin'])) {
			$attendee_quantity = 1;
			$final_price	= (float)$data_source['event_cost'];
			$orig_price		= (float)$data_source['event_cost'];
			$price_type		=  __('Admin', 'event_espresso');
		} elseif (isset($data_source['seat_id'])) {
			// Added for seating chart add-on
			// If a seat was selected then price of that seating will be used instead of event price
			$final_price	= (float)seating_chart::get_purchase_price($data_source['seat_id']);
			$orig_price		= (float)$final_price;
			$price_type		= $data_source['seat_id'];

		} elseif ( isset( $att_data_source['price_id'] ) && ! empty( $att_data_source['price_id'] )) {

			if ( $att_data_source['price_id'] == 'free' ) {
				$orig_price		= 0.00;
				$final_price	= 0.00;
				$price_type		=  __('Free Event', 'event_espresso');
			} else {
				if($is_donation){
					$final_price	= $event_cost;
					$price_type		= isset( $att_data_source['price_id'] ) ? espresso_ticket_information( array( 'type' => 'ticket', 'price_option' => absint($att_data_source['price_id']) )) : '';
					$surcharge		= 0;
					$orig_price		= (float)number_format( $event_cost, 2, '.', '' );
				}
				else{
					$orig_price		= event_espresso_get_orig_price_and_surcharge( (int)$att_data_source['price_id'] );
					$final_price	= isset( $att_data_source['price_id'] ) ? event_espresso_get_final_price( absint($att_data_source['price_id']), $event_id, $orig_price ) : 0.00;
					$price_type		= isset( $att_data_source['price_id'] ) ? espresso_ticket_information( array( 'type' => 'ticket', 'price_option' => absint($att_data_source['price_id']) )) : '';
					$surcharge		= event_espresso_calculate_surcharge( (float)$orig_price->event_cost , (float)$orig_price->surcharge, $orig_price->surcharge_type );
					$orig_price		= (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' );

					if(isset($att_data_source['stein_upgrade_cost'])){
						$orig_price += ($att_data_source['stein_upgrade_cost'] * 1);
						$final_price += ($att_data_source['stein_upgrade_cost'] * 1);
						$price_type .= " ( + ".$att_data_source['SINGLE_13'].")";
					}
				}
			}

		} elseif ( isset( $data_source['price_select'] ) && $data_source['price_select'] == TRUE ) {

			//Figure out if the person has registered using a price selection
			$price_options	= explode( '|', sanitize_text_field($data_source['price_option']), 2 );
			$price_id		= absint($price_options[0]);
			$price_type		= $price_options[1];
			if($is_donation){
				$orig_price		= (float)number_format( $data_source['event_cost'], 2, '.', '' );
				$final_price	= $orig_price;
				$surcharge		= 0;
			}
			else{
				$orig_price		= event_espresso_get_orig_price_and_surcharge( $price_id );
				$final_price	= event_espresso_get_final_price( $price_id, $event_id, $orig_price );
				$surcharge		= event_espresso_calculate_surcharge( $orig_price->event_cost , $orig_price->surcharge, $orig_price->surcharge_type );
				$orig_price		= (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' );
			}
		} else {

			if ( $data_source['price_id'] == 'free' ) {
				$orig_price		= 0.00;
				$final_price	= 0.00;
				$price_type		=  __('Free Event', 'event_espresso');
			} else {


				if($is_donation){
					$orig_price		= (float)number_format( $data_source['event_cost'], 2, '.', '' );
					$final_price	= $orig_price;
					$price_type		= isset($data_source['price_id']) ? espresso_ticket_information(array('type' => 'ticket', 'price_option' => absint($data_source['price_id']))) : '';
					$surcharge		= 0;
				}
				else{
					$orig_price		= event_espresso_get_orig_price_and_surcharge( absint($data_source['price_id']) );
					$final_price	= isset( $data_source['price_id'] ) ? event_espresso_get_final_price( absint($data_source['price_id']), $event_id, $orig_price ) : 0.00;
					$price_type		= isset($data_source['price_id']) ? espresso_ticket_information(array('type' => 'ticket', 'price_option' => absint($data_source['price_id']))) : '';
					$surcharge		= event_espresso_calculate_surcharge( $orig_price->event_cost , $orig_price->surcharge, $orig_price->surcharge_type );
					$orig_price		= (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' );
				}

			}

		}

		$final_price		= apply_filters( 'filter_hook_espresso_attendee_cost', $final_price );
		$attendee_quantity	= isset( $data_source['num_people'] ) ? $data_source['num_people'] : 1;
		$coupon_code		= '';

		if ($multi_reg) {
			$event_cost		= $_SESSION['espresso_session']['grand_total'];
		}

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .' : attendee_cost=' . $final_price);

		$event_cost = apply_filters( 'filter_hook_espresso_cart_grand_total', $event_cost );
		$amount_pd = 0.00;


		//Check if the registration id has been created previously.
		$registration_id = empty($wpdb->last_result[0]->registration_id) ? apply_filters('filter_hook_espresso_registration_id', $event_id) : $wpdb->last_result[0]->registration_id;

		$txn_type = "";

		if (isset($data_source['admin'])) {

			$payment_status		= "Completed";
			$payment			= "Admin";
			$txn_type			= __('Added by Admin', 'event_espresso');
			$payment_date		= date(get_option('date_format'));
			$amount_pd			= !empty($data_source['event_cost']) ? $data_source['event_cost'] : 0.00;
			$registration_id	= uniqid('', true);
			$_SESSION['espresso_session']['id'] = uniqid('', true);


		} else {

			//print_r( $event_meta);
			$default_payment_status = $event_meta['default_payment_status'] != '' ? $event_meta['default_payment_status'] : $org_options['default_payment_status'];
			$payment_status = ( $multi_reg && $data_source['cost'] == 0.00 ) ? "Completed" : $default_payment_status;
			$payment = '';

		}

		$times_sql = "SELECT ese.start_time, ese.end_time, e.start_date, e.end_date ";
		$times_sql .= "FROM " . EVENTS_START_END_TABLE . " ese ";
		$times_sql .= "LEFT JOIN " . EVENTS_DETAIL_TABLE . " e ON ese.event_id = e.id WHERE ";
		$times_sql .= "e.id=%d";
		if (!empty($data_source['start_time_id'])) {
			$times_sql .= " AND ese.id=" . absint($data_source['start_time_id']);
		}

		$times = $wpdb->get_results($wpdb->prepare( $times_sql, $event_id ));
		foreach ($times as $time) {
			$start_time		= $time->start_time;
			$end_time		= $time->end_time;
			$start_date		= $time->start_date;
			$end_date		= $time->end_date;
		}


		//If we are using the number of attendees dropdown, add that number to the DB
		//echo $data_source['espresso_addtl_limit_dd'];
		if (isset($data_source['espresso_addtl_limit_dd'])) {
			$num_people = absint($data_source ['num_people']);
		} elseif (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1) {
			$num_people = absint($data_source ['num_people']);
		} else {
			$num_people = 1;
		}


		// check for coupon
		if ( function_exists( 'event_espresso_process_coupon' )) {
			if ( $coupon_results = event_espresso_process_coupon( $event_id, $final_price, $multi_reg )) {
				//printr( $coupon_results, '$coupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				if ( $coupon_results['valid'] ) {
					$final_price = number_format( $coupon_results['event_cost'], 2, '.', '' );
					$coupon_code = $coupon_results['code'];
				}
				if ( ! $multi_reg && ! empty( $coupon_results['msg'] )) {
					$notifications['coupons'] = $coupon_results['msg'];
				}
			}
		}

		// check for groupon
		if ( function_exists( 'event_espresso_process_groupon' )) {
			if ( $groupon_results = event_espresso_process_groupon( $event_id, $final_price, $multi_reg )) {
				//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				if ( $groupon_results['valid'] ) {
					$final_price = number_format( $groupon_results['event_cost'], 2, '.', '' );
					$coupon_code = $groupon_results['code'];
				}
				if ( ! $multi_reg && ! empty( $groupon_results['msg'] )) {
					$notifications['groupons'] = $groupon_results['msg'];
				}
			}
		}

		$start_time			= empty($start_time) ? '' : $start_time;
		$end_time			= empty($end_time) ? '' : $end_time;
		$start_date			= empty($start_date) ? '' : $start_date;
		$end_date			= empty($end_date) ? '' : $end_date;
		$organization_name	= empty($organization_name) ? '' : $organization_name;
		$country_id			= empty($country_id) ? '' : $country_id;
		$payment_date		= empty($payment_date) ? '' : $payment_date;
		$coupon_code		= empty($coupon_code) ? '' : $coupon_code;

		$amount_pd			= number_format( (float)$amount_pd, 2, '.', '' );
		if(isset($donations[$event_id])){
			$orig_price			= number_format( (float)$donations[$event_id], 2, '.', '' );
			$final_price		= number_format( (float)$donations[$event_id], 2, '.', '' );
		}
		else{
			$orig_price			= number_format( (float)$orig_price, 2, '.', '' );
			$final_price		= number_format( (float)$final_price, 2, '.', '' );
		}

		$total_cost			= $total_cost + $final_price;

		$columns_and_values = array(
			'registration_id'		=> $registration_id,
			'is_primary'			=> $attendee_number == 1 ? TRUE : FALSE,
			'attendee_session'		=> $_SESSION['espresso_session']['id'],
			'lname'					=> $lname,
			'fname'					=> $fname,
			'address'				=> $address,
			'address2'				=> $address2,
			'city'					=> $city,
			'state'					=> $state,
			'zip'					=> $zip,
			'email'					=> $email,
			'phone'					=> $phone,
			'payment'				=> $payment,
			'txn_type'				=> $txn_type,
			'coupon_code'			=> $coupon_code,
			'event_time'			=> $start_time,
			'end_time'				=> $end_time,
			'start_date'			=> $start_date,
			'end_date'				=> $end_date,
			'price_option'			=> $price_type,
			'organization_name'		=> $organization_name,
			'country_id'			=> $country_id,
			'payment_status'		=> $payment_status,
			'payment_date'			=> $payment_date,
			'event_id'				=> $event_id,
			'quantity'				=> (int)$num_people,
			'amount_pd'				=> $amount_pd,
			'orig_price'			=> $orig_price,
			'final_price'			=> $final_price
		);


		$data_formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f' );

		// save the attendee details - FINALLY !!!
		if ( ! $wpdb->insert( EVENTS_ATTENDEE_TABLE, $columns_and_values, $data_formats )) {
			$error = true;
		}

		$attendee_id = $wpdb->insert_id;

		//Save the attendee data as a meta value
		do_action('action_hook_espresso_save_attendee_meta', $attendee_id, 'original_attendee_details', serialize($columns_and_values));

		// save attendee id for the primary attendee
		$primary_att_id = $attendee_number == 1 ? $attendee_id : FALSE;


		// Added for seating chart addon
		$booking_id = 0;
		if (defined('ESPRESSO_SEATING_CHART')) {
			if (seating_chart::check_event_has_seating_chart($event_id) !== false) {
				if (isset($_POST['seat_id'])) {
					$booking_id = seating_chart::parse_booking_info(sanitize_text_field($_POST['seat_id']));
					if ($booking_id > 0) {
						seating_chart::confirm_a_seat($booking_id, $attendee_id);
					}
				}
			}
		}

		//Add a record for the primary attendee
		if ( $attendee_number == 1 ) {

			$columns_and_values = array(
				'attendee_id'	=> $primary_att_id,
				'meta_key'		=> 'primary_attendee',
				'meta_value'	=> 1
			);
			$data_formats = array('%s', '%s', '%s');

			if ( !$wpdb->insert(EVENTS_ATTENDEE_META_TABLE, $columns_and_values, $data_formats) ) {
				$error = true;
			}

		}


		if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
			MailChimpController::list_subscribe($event_id, $attendee_id, $fname, $lname, $email);
		}

		//Defining the $base_questions variable in case there are no additional attendee questions
		$base_questions = $questions;

		//Since main attendee and additional attendees may have different questions,
		//$attendee_number check for 2 because is it statically set at 1 first and is incremented for the primary attendee above, hence 2
		$questions = ( $attendee_number > 1 && isset($event_meta['add_attendee_question_groups'])) ? $event_meta['add_attendee_question_groups'] : $questions;

		add_attendee_questions( $questions, $registration_id, $attendee_id, array( 'session_vars' => $att_data_source ));

		//Add additional attendees to the database
		if ($event_meta['additional_attendee_reg_info'] > 1) {

			$questions = $event_meta['add_attendee_question_groups'];

			if (empty($questions)) {
				$questions = $base_questions;
			}


			if ( isset( $att_data_source['x_attendee_fname'] )) {
				foreach ( $att_data_source['x_attendee_fname'] as $k => $v ) {

					if ( trim($v) != '' && trim( $att_data_source['x_attendee_lname'][$k] ) != '' ) {

						// Added for seating chart addon
						$seat_check = true;
						$x_booking_id = 0;
						if ( defined('ESPRESSO_SEATING_CHART')) {
							if (seating_chart::check_event_has_seating_chart($event_id) !== false) {
								if (!isset($att_data_source['x_seat_id'][$k]) || trim($att_data_source['x_seat_id'][$k]) == '') {
									$seat_check = false;
								} else {
									$x_booking_id = seating_chart::parse_booking_info($att_data_source['x_seat_id'][$k]);
									if ($x_booking_id > 0) {
										$seat_check = true;
										$price_type =  $att_data_source['x_seat_id'][$k];
										$final_price = seating_chart::get_purchase_price($att_data_source['x_seat_id'][$k]);
										$orig_price = $final_price;
									} else {
										$seat_check = false; //Keeps the system from adding an additional attndee if no seat is selected
									}
								}
							}
						}

						if ($seat_check) {

							$ext_att_data_source = array(
								'registration_id'	=> $registration_id,
								'attendee_session'	=> $_SESSION['espresso_session']['id'],
								'lname'				=> sanitize_text_field($att_data_source['x_attendee_lname'][$k]),
								'fname'				=> sanitize_text_field($v),
								'email'				=> sanitize_text_field($att_data_source['x_attendee_email'][$k]),
								'address'			=> empty($att_data_source['x_attendee_address'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_address'][$k]),
								'address2'			=> empty($att_data_source['x_attendee_address2'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_address2'][$k]),
								'city'				=> empty($att_data_source['x_attendee_city'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_city'][$k]),
								'state'				=> empty($att_data_source['x_attendee_state'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_state'][$k]),
								'zip'				=> empty($att_data_source['x_attendee_zip'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_zip'][$k]),
								'phone'				=> empty($att_data_source['x_attendee_phone'][$k]) ? '' : sanitize_text_field($att_data_source['x_attendee_phone'][$k]),
								'payment'			=> $payment,
								'event_time'		=> $start_time,
								'end_time'			=> $end_time,
								'start_date'		=> $start_date,
								'end_date'			=> $end_date,
								'price_option'		=> $price_type,
								'organization_name'	=> $organization_name,
								'country_id'		=> $country_id,
								'payment_status'	=> $payment_status,
								'payment_date'		=> $payment_date,
								'event_id'			=> $event_id,
								'quantity'			=> (int)$num_people,
								'amount_pd'			=> 0.00,
								'orig_price'		=> $orig_price,
								'final_price'		=> $final_price
							);

							$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f' );
							$wpdb->insert( EVENTS_ATTENDEE_TABLE, $ext_att_data_source, $format );

							//Added by Imon
							$ext_attendee_id = $wpdb->insert_id;

							//Save the attendee data as a meta value
							do_action('action_hook_espresso_save_attendee_meta', $ext_attendee_id, 'original_attendee_details', serialize($ext_att_data_source));

							$mailchimp_attendee_id = $ext_attendee_id;

							if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
								MailChimpController::list_subscribe($event_id, $mailchimp_attendee_id, $v, $att_data_source['x_attendee_lname'][$k], $att_data_source['x_attendee_email'][$k]);
							}

							if ( ! is_array($questions) && !empty($questions)) {
								$questions = unserialize($questions);
							}

							$questions_in = '';
							foreach ($questions as $g_id) {
								$questions_in .= $g_id . ',';
							}
							$questions_in = substr($questions_in, 0, -1);

							$SQL = "SELECT q.*, qg.group_name FROM " . EVENTS_QUESTION_TABLE . " q ";
							$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
							$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
							$SQL .= "WHERE qgr.group_id in ( $questions_in ) ";
							$SQL .= "ORDER BY q.id ASC";

							$questions_list = $wpdb->get_results($wpdb->prepare( $SQL, NULL ));
							foreach ($questions_list as $question_list) {
								if ($question_list->system_name != '') {
									$ext_att_data_source[$question_list->system_name] = $att_data_source['x_attendee_' . $question_list->system_name][$k];
								} else {
									$ext_att_data_source[$question_list->question_type . '_' . $question_list->id] = isset($att_data_source['x_attendee_' . $question_list->question_type . '_' . $question_list->id][$k]) && !empty($att_data_source['x_attendee_' . $question_list->question_type . '_' . $question_list->id][$k]) ? $att_data_source['x_attendee_' . $question_list->question_type . '_' . $question_list->id][$k] : '';
								}
							}

							echo add_attendee_questions($questions, $registration_id, $ext_attendee_id, array('session_vars' => $ext_att_data_source));

						}

						// Added for seating chart addon
						if (defined('ESPRESSO_SEATING_CHART')) {
							if (seating_chart::check_event_has_seating_chart($event_id) !== false && $x_booking_id > 0) {
								seating_chart::confirm_a_seat($x_booking_id, $ext_attendee_id);
							}
						}
					}
				}
			}
		}


		//Add member data if needed
		if (defined('EVENTS_MEMBER_REL_TABLE')) {
			require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
			require(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
			if ($userid != 0) {
				event_espresso_add_user_to_event( $event_id, $userid, $attendee_id );
			}
		}

		$attendee_number++;

		if (isset($data_source['admin'])) {
			return $attendee_id;
		}


		//This shows the payment page
		if ( ! $multi_reg) {
			return events_payment_page( $attendee_id, $notifications );
		}

		return array( 'registration_id' => $registration_id, 'notifications' => $notifications );

	}
}


function event_espresso_update_item_in_session( $update_section = FALSE ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	global $wpdb;

	// grab the event sessions
	// loop through the events and for each one
	// - update the pricing, time options
	//-  update the attendee information


	$events_in_session = isset( $_SESSION['espresso_session']['events_in_session'] ) ? $_SESSION['espresso_session']['events_in_session'] : event_espresso_clear_session( TRUE );

	if ( ! is_array( $events_in_session )) {
		return false;
	}

	//holds the updated infromation
	$updated_events_in_session = $events_in_session;

	if ( $update_section == 'details' ) {

		foreach ($events_in_session as $event_id => $v) {

			$event_cost = 0;
			$event_individual_cost[$event_id] = 0;
			$updated_events_in_session[$event_id]['id'] = $event_id;
			/*
			 * if the array key exists, update that array key with the value from post
			 */


			//Start time selection
			$start_time_id = '';
			if (array_key_exists('start_time_id', $_POST) && array_key_exists($event_id, $_POST['start_time_id'])) {

				$updated_events_in_session[$event_id]['start_time_id'] = $wpdb->escape($_POST['start_time_id'][$event_id]);

				//unset the post key so it doesn't get added below
				unset($_POST['start_time_id'][$event_id]);
			}

			//Pricing selection
			$price_id = null;

			//resetting this session var for just in case the event organizer makes changes when someone is
			//registering, the old price ids don't stay in the session
			$updated_events_in_session[$event_id]['price_id'] = array();


			/*
			 * the price id comes this way
			 * - from a dropdown >> price_id[event_id][price_id]
			 * - from a radio >> price_id[event_id] with a value of price_id
			 */
			$attendee_quantity = 1;
			$price_id = $_POST['price_id'][$event_id];

			if (is_array($price_id)) {
				foreach ($price_id as $_price_id => $val) {
					//assign the event type and the quantity
					$updated_events_in_session[$event_id]['price_id'][$_price_id]['attendee_quantity'] = $wpdb->escape($val);
					$updated_events_in_session[$event_id]['price_id'][$_price_id]['price_type'] = $events_in_session[$event_id]['price_id'][$_price_id]['price_type'];

					$attendee_quantity++;
				}
			} else {
				if (isset($price_id)) {
					$updated_events_in_session[$event_id]['price_id'][$price_id]['attendee_quantity'] = 1;
					$updated_events_in_session[$event_id]['price_id'][$price_id]['price_type'] = $events_in_session[$event_id]['price_id'][$price_id]['price_type'];
				}
			}

			$updated_events_in_session[$event_id]['attendee_quantitiy'] = $attendee_quantity;

			//Get Cost of each event
			//$updated_events_in_session[$event_id]['cost'] = $event_individual_cost[$event_id];
			//$updated_events_in_session[$event_id]['event_name'] = $wpdb->escape( $_POST['event_name'][$event_id] );

			if (isset($_POST['event_espresso_coupon_code'])) {
				$_SESSION['espresso_session']['event_espresso_coupon_code'] = $wpdb->escape($_POST['event_espresso_coupon_code']);
			}

			if (isset($_POST['event_espresso_groupon_code'])) {
				$_SESSION['espresso_session']['groupon_code'] = $wpdb->escape($_POST['event_espresso_groupon_code']);
			}
		}

	} elseif ( $update_section == 'attendees' ) {
		//show the empty cart error
		if (event_espresso_invoke_cart_error($events_in_session))
			return false;

		foreach ($events_in_session as $k_event_id => $v_event_id) {
			//unset the event attendees array because they may have decreased the number of attendees
			if (isset($updated_events_in_session[$k_event_id]['event_attendees']))
				$updated_events_in_session[$k_event_id]['event_attendees'] = array();

			$price_id = $v_event_id['price_id'];

			if (is_array($price_id)) {
				foreach ($price_id as $_price_id => $val) {
					$index = 1;
					//assign the event type and the quantity
					foreach ($_POST as $post_name => $post_value) {
						//$field_values come in as arrays since their names are designated as arrays,e.g. fname[eventid][price_id][index]
						if (is_array($post_value) && array_key_exists($k_event_id, $post_value) && array_key_exists($_price_id, $post_value[$k_event_id])) {

							foreach ($post_value[$k_event_id][$_price_id] as $mkey => $mval) {
					            if (is_array($mval)) {
					                array_walk_recursive($mval, 'sanitize_text_field');
					            } else {
					                $mval = sanitize_text_field($mval);
					            }
								$updated_events_in_session[$k_event_id]['event_attendees'][$_price_id][$mkey][$post_name] = $mval;
								//echo "multi $k > $field_name >" . $mkey . " > " . $mval . "<br />";
							}
						}
					}
				}
			}
		}
	}

	$_SESSION['espresso_session']['events_in_session'] = $updated_events_in_session;
	//echo "<pre>", print_r($updated_events_in_session), "</pre>";

	return true;

	die();

}


function event_espresso_email_confirmations($atts) {

	extract($atts);
	//print_r($atts);

	$multi_reg = empty( $multi_reg ) ? FALSE :  $multi_reg;
	$send_admin_email = empty( $send_admin_email ) ? FALSE :  $send_admin_email;
	$send_attendee_email = empty( $send_attendee_email ) ? FALSE :  $send_attendee_email;
	$custom_data = empty( $custom_data ) ? '' :  $custom_data;

	if ( ! empty( $attendee_id ) && ! $multi_reg ) {

		email_by_attendee_id($attendee_id, $send_attendee_email, $send_admin_email, $multi_reg, $custom_data);

	} elseif ( ! empty( $registration_id ) && ! $multi_reg ) {

		global $wpdb;
        $sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = %s";
		$attendees = $wpdb->get_col( $wpdb->prepare( $sql, $registration_id ));
		foreach ($attendees as $attendee_id) {
			email_by_attendee_id($attendee_id, $send_attendee_email, $send_admin_email, $multi_reg, $custom_data);
		}

	} elseif ( ! empty( $session_id )) {

		email_by_session_id($session_id, $send_attendee_email, $send_admin_email, $multi_reg);

	}
}

function myTruncate($string, $limit, $break=" ", $pad="...")
{
  // return with no change if string is shorter than $limit
  if(strlen($string) <= $limit) return $string;

  // is $break present between $limit and the end of the string?
  if(false !== ($breakpoint = strpos($string, $break, $limit))) {
    if($breakpoint < strlen($string) - 1) {
      $string = substr($string, 0, $breakpoint) . $pad;
    }
  }

  return $string;
}



function event_espresso_get_final_price( $price_id = FALSE, $event_id = FALSE, $orig_price = FALSE ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	if ( ! $price_id || ! $event_id ) {
		return FALSE;
	}

	global $wpdb;

	$result = $orig_price !== FALSE ? $orig_price : event_espresso_get_orig_price_and_surcharge( $price_id );

	if ( isset( $result->event_cost )) {
		$result->event_cost = (float)$result->event_cost;
	} else {
		$result = new stdClass();
		$result->event_cost = (float)$orig_price;
	}


	// if price is anything other than zero
	if ( $result->event_cost > 0.00 ) {
		// Addition for Early Registration discount
		if ( $early_price_data = early_discount_amount( $event_id, $result->event_cost )) {
			$result->event_cost = $early_price_data['event_price'];
		}
	}

	$surcharge = event_espresso_calculate_surcharge( $result->event_cost , $result->surcharge, $result->surcharge_type );
	$surcharge = ! empty($surcharge) ? (float)$surcharge : 0;
	$event_cost = $result->event_cost + $surcharge;

//		echo '<h4>$event_cost : ' . $event_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

	return (float)number_format( $event_cost, 2, '.', '' );
}