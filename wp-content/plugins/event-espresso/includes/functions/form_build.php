<?php
if (!function_exists('event_form_build')) {

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

		$question->question = stripslashes( $question->question );

		if ($question->required == "Y") {
			$required_title = ' title="' . $question->required_text . '"';
			$required_class = ' required ' . $email_validate . ' ';
			$required_label = "<em>*</em>";
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
				
				$html .= '<div class="event_form_field">' . $label;
				$html .= '<input type="text" ' . $required_title . ' class="' . $required_class . $class . $text_input_class .'" id="' . $field_name . '-' . $event_id . '-' . $price_id . '-' . $attendee_number . '" name="' . $field_name . $multi_name_adjust . '" value="' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '" ' . $disabled . ' /></div>';
				
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

}

function event_form_build_edit( $question, $answer, $show_admin_only = false, $class = 'ee-reg-page-questions' ) {

	$form_input = '';

	$email_validate = $question->system_name == 'email' ? 'email' : '';

	if ($question->required == "Y") {
		$required_title = ' title="' . $question->required_text . '"';
		$required_class = ' required ' . $email_validate . ' ';
		$required_label = "<em>*</em>";
	} else {
		$required_title = '';
		$required_class = '';
		$required_label = '';
	}


	//Sometimes the answer id is passed as the question id, so we need to make sure that we get the right question id.
	$answer_id = $question->id;

	if (isset($question->q_id)) {
		$question->id = $question->q_id;
	}
		
	if ($question->admin_only == 'Y' && $show_admin_only == false) {
		return;
	}
	
	$field_name = ($question->system_name != '') ? $question->system_name : 'TEXT_' . $question->id;
	$label = '<label for="' . $field_name . '">' . trim( stripslashes( str_replace( '&#039;', "'", $question->question ))) . $required_label . '</label>';
	
	if ( is_array( $answer )) {
		array_walk( $answer, 'trim' );
	} else {
		$answer = trim( $answer );
	}	
	
	switch ($question->question_type) {
	
		case "TEXT" :
			$form_input .= '<p class="event_form_field">' . $label;
			$form_input .= '<input type="text" ' . $required_title . ' class="' . $required_class . $class . '" id="' . $field_name . '"  name="' . $field_name . '" value="' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '" />';
			$form_input .= '</p>';
			break;
			
		case "TEXTAREA" :		
			$form_input .= '<p class="event_form_field">' . $label;
			$form_input .= '<textarea id="TEXTAREA_' . $question->id . '" ' . $required_title . ' class="' . $required_class . $class . '" name="TEXTAREA_' . $question->id . '" rows="5">' . htmlspecialchars( stripslashes( $answer ), ENT_QUOTES, 'UTF-8' ) . '</textarea>';
			$form_input .= '</p>';
			break;
			
		case "SINGLE" :
		
			$values = explode(",", $question->response);
			$answers = explode(",", $answer);

			foreach ( $answers as $key => $value ) {
				$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
				$answers[$key] = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
			}

			$form_input .= $label;
			$form_input .= '
	<ul class="edit-options-list-radio">';
			foreach ($values as $key => $value) {
				$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
				$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
				$checked = in_array( $value, $answers ) ? ' checked="checked"' : '';
				
				$form_input .= '
		<li>
			<label class="radio-btn-lbl">
				<input id="SINGLE_' . $question->id . '_' . $key . '" ' . $required_title . ' class="' . $required_class . $class . '" name="SINGLE_' . $question->id . '"  type="radio" value="' . $value . '" ' . $checked . '/>
				<span>' . $value . '</span>
			</label>
		</li>';
			}
			$form_input .= '
	</ul>';
			break;
			
		case "MULTIPLE" :
		
			$values = explode( ',', $question->response );
			$answers = explode( ',', $answer );

			foreach ( $answers as $key => $value ) {
				$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
				$answers[$key] = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
			}
			
			$form_input .= $label;
			$form_input .= '
	<ul class="edit-options-list-check">';
			foreach ($values as $key => $value) {
			
				$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
				$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
				$checked = in_array( $value, $answers) ? ' checked="checked"' : '';
				
				$form_input .= '
		<li>
			<label class="checkbox-lbl">
				<input id="' . $question->id . '_' . trim( stripslashes( $key )) . '" ' . $required_title . ' class="' . $required_class . $class . '" name="MULTIPLE_' . $question->id . '[]"  type="checkbox" value="' . $value . '" ' . $checked . '/>
				<span>' . $value . '</span>
			</label>
		</li>';
			}
			$form_input .= '
	</ul>';

			break;
			
		case "DROPDOWN" :
		
			$dd_type = $question->system_name == 'state' ? 'name="state"' : 'name="DROPDOWN_' . $question->id . '"';
			$values = explode(",", $question->response);

			$answer = trim( stripslashes( str_replace( '&#039;', "'", $answer )));
			$answer = htmlspecialchars( $answer, ENT_QUOTES, 'UTF-8' );

			$form_input .= '
			<div class="event_form_field">' . $label;
			$form_input .= '
				<select ' . $dd_type . ' ' . $required_title . ' class="' . $required_class . $class . '" id="DROPDOWN_' . $question->id . '"  />';
			
			foreach ($values as $key => $value) {

				$value = trim( stripslashes( str_replace( '&#039;', "'", $value )));
				$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

				$selected = ( $value == $answer ) ? ' selected="selected"' : "";

				$form_input .= '
					<option value="' . $value . '"' . $selected . '/> ' . $value . '</option>';
			}
			$form_input .= '
				</select>';
			$form_input .= '
			</div>';
			break;
			
		default :
			break;
			
	}
	
	return $form_input;
}