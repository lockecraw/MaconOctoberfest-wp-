<?php
include_once('../wp-config.php');
include_once('../wp-load.php');
include_once('../wp-includes/wp-db.php');

if( !isset($_POST['authenticate']) || $_POST['authenticate']==""){
	die("<?xml version='1.0'?><attendies><error>1</error><desc>Service Not Accessible</desc></attendies>");
}

if($_POST['authenticate'] != "fdljgrirtgibmpkjkgffdndfj1123124"){
	die("<?xml version='1.0'?><attendies><error>1</error><desc>Service Not Accessible</desc></attendies>");
}

if( !isset($_POST['eventcode']) || $_POST['eventcode']==""){
	die("<?xml version='1.0'?><attendies><error>1</error><desc>Event Code is empty</desc></attendies>");
}
$eventcode = $_POST['eventcode'];
	
$query_event = "SELECT * FROM {$wpdb->prefix}events_detail WHERE event_code='".$eventcode."'";
//var_dump($query_event);
$result_event = $wpdb->get_results($query_event);

if(count($result_event) == 0){
	die("<?xml version='1.0'?><attendies><error>3</error><desc>No Events For this Event Code</desc></attendies>");
}
//var_dump($result_event);

$eventId = $result_event[0];
$query = "SELECT * FROM {$wpdb->prefix}events_attendee WHERE event_id='".$eventId->id."'";
//var_dump($query);
$result =  $wpdb->get_results($query);

if(count($result) == 0){
	die("<?xml version='1.0'?><attendies><error>3</error><desc>No Attendees for this Event</desc></attendies>");
}

$response = "<?xml version='1.0'?><attendies><error>0</error><desc>Success</desc>";
foreach ($result as $row) {
	$response .= "<attendee>";
	$response .= "<id>".$row->id."</id>";
	$response .= "<registration_id>".$row->registration_id."</registration_id>";
	$response .= "<lname>".html_entity_decode($row->lname, ENT_QUOTES, 'UTF-8')."</lname>";
	$response .= "<fname>".html_entity_decode($row->fname, ENT_QUOTES, 'UTF-8')."</fname>";
	$response .= "<email>".$row->email."</email>";
	$response .= "<phone>".$row->phone."</phone>";
	$response .= "<payment>".$row->payment."</payment>";
	$response .= "<date>".$row->date."</date>";
	$response .= "<payment_status>".$row->payment_status."</payment_status>";
	$response .= "<amount_pd>".$row->final_price."</amount_pd>";
	$response .= "<price_option><![CDATA[".html_entity_decode($row->price_option, ENT_QUOTES, 'UTF-8')."]]></price_option>";
	$response .= "<coupon_code >".$row->coupon_code."</coupon_code>";
	$response .= "<quantity>".$row->quantity."</quantity>";
	$response .= "<payment_date>".$row->payment_date."</payment_date>";
	$response .= "<event_id>".$row->event_id."</event_id>";
	$response .= "<event_time>".event_date_display($row->event_time, get_option('time_format'))."</event_time>";
	$response .= "<end_time>".event_date_display($row->end_time, get_option('time_format'))."</end_time>";
	$response .= "<start_date>".$row->start_date."</start_date>";
	$response .= "<end_date>".$row->end_date."</end_date>";
	//$response .= "<attendee_session>".$row->attendee_session."</attendee_session>";
	//$response .= "<transaction_details>".$row->transaction_details."</transaction_details>";
	$response .= "<checked_in>".$row->checked_in."</checked_in>";
	$response .= "<checked_in_quantity>".$row->checked_in_quantity."</checked_in_quantity>";
	$response .= "</attendee>";
}
$response .= "</attendies>";
echo $response;