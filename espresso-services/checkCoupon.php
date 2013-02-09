<?php
include_once('../wp-config.php');
include_once('../wp-load.php');
include_once('../wp-includes/wp-db.php');

if( !isset($_POST['authenticate']) || $_POST['authenticate']==""){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>Service Not Accessible</desc></checkCoupon>");
}

if($_POST['authenticate'] != "fdljgrirtgibmpkjkgffdndfj1123124"){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>Service Not Accessible</desc></checkCoupon>");
}

if( !isset($_POST['eventcode']) || $_POST['eventcode']==""){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>Event Code is Empty</desc></checkCoupon>");
}
$event_code = $_POST['eventcode'];

if( !isset($_POST['attendeeId']) || $_POST['attendeeId']==""){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>Attendee Id is Empty</desc></checkCoupon>");
}
$attendeeId = $_POST['attendeeId'];

if( !isset($_POST['regId']) || $_POST['regId']==""){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>RegistrationId is Empty</desc></checkCoupon>");
}
$registrationId = $_POST['regId'];

if( !isset($_POST['ignorePayment']) || $_POST['ignorePayment']==""){
	die("<?xml version='1.0'?><checkCoupon><error>1</error><desc>Ignore Payment Field is Empty</desc></checkCoupon>");
}
$ignorePayment = $_POST['ignorePayment'];

$query = "SELECT a.*, e.event_code FROM {$wpdb->prefix}events_attendee a ";
$query .= " LEFT JOIN {$wpdb->prefix}events_detail e ON e.id = a.event_id ";
$query .= " WHERE a.id=".$attendeeId;
$query .= " AND a.registration_id='".$registrationId."'";
$result =  $wpdb->get_results($query);

if(count($result) == 0){
	die("<?xml version='1.0'?><checkCoupon><error>3</error><desc>No Ticket For This Attendee</desc></checkCoupon>");
}

$row = $result[0];

if($row->event_code != $event_code){
	die("<?xml version='1.0'?><checkCoupon><error>5</error><desc>Attendee at wrong Event</desc></checkCoupon>");
}

if($row->payment_status == "Incomplete" && strtolower($ignorePayment) == "no"){
	die("<?xml version='1.0'?><checkCoupon><error>4</error><desc>Payment Status Incomplete</desc></checkCoupon>");
}

if( $row->checked_in_quantity < $row->quantity || $row->checked_in_quantity == 0 ){
	$query_Update = "UPDATE {$wpdb->prefix}events_attendee SET checked_in_quantity = checked_in_quantity + 1, checked_in=1 WHERE id=".$row->id." AND registration_id='".$row->registration_id."'";
	if( $wpdb->query($query_Update)){
		$query1 = "SELECT * FROM {$wpdb->prefix}events_attendee WHERE id=".$row->id." AND registration_id='".$row->registration_id."'";	
		$res =  $wpdb->get_results($query1);
		
		$row1 = $res[0];
		
		$response .= "<?xml version='1.0'?><checkCoupon><error>0</error><desc>SUCCESS</desc><attendee>";
		$response .= "<id>".$row1->id."</id>";
		$response .= "<registration_id>".$row1->registration_id."</registration_id>";
		$response .= "<lname>".html_entity_decode($row1->lname, ENT_QUOTES, 'UTF-8')."</lname>";
		$response .= "<fname>".html_entity_decode($row1->fname, ENT_QUOTES, 'UTF-8')."</fname>";
		$response .= "<email>".$row1->email."</email>";
		$response .= "<phone>".$row1->phone."</phone>";
		$response .= "<payment>".$row1->payment."</payment>";
		$response .= "<date>".$row1->date."</date>";
		$response .= "<payment_status>".$row1->payment_status."</payment_status>";
		$response .= "<amount_pd>".$row1->final_price."</amount_pd>";
		$response .= "<price_option><![CDATA[".html_entity_decode($row1->price_option, ENT_QUOTES, 'UTF-8')."]]></price_option>";
		$response .= "<coupon_code >".$row1->coupon_code."</coupon_code>";
		$response .= "<quantity>".$row1->quantity."</quantity>";
		$response .= "<payment_date>".$row1->payment_date."</payment_date>";
		$response .= "<event_id>".$row1->event_id."</event_id>";
		$response .= "<event_time>".$row1->event_time."</event_time>";
		$response .= "<end_time>".$row1->end_time."</end_time>";
		$response .= "<start_date>".$row1->start_date."</start_date>";
		$response .= "<end_date>".$row1->end_date."</end_date>";
		//$response .= "<attendee_session>".$row1->attendee_session."</attendee_session>";
		//$response .= "<transaction_details><![CDATA[".html_entity_decode($row1->transaction_details, ENT_QUOTES, 'UTF-8')."]]></transaction_details>";
		$response .= "<checked_in>".$row1->checked_in."</checked_in>";
		$response .= "<checked_in_quantity>".$row1->checked_in_quantity."</checked_in_quantity>";
		$response .= "</attendee></checkCoupon>";
		echo $response;
	}else{
		echo "<?xml version='1.0'?><checkCoupon><error>2</error><desc>mysql_error</desc></checkCoupon>";
	}
	
}else{
	$query1 = "SELECT * FROM {$wpdb->prefix}events_attendee WHERE id=".$row->id." AND registration_id='".$row->registration_id."'";	
	$res =  $wpdb->get_results($query1);
	$row1 = $res[0];
	$response .= "<?xml version='1.0'?><checkCoupon><attendee>";
	$response .= "<id>".$row1->id."</id>";
	$response .= "<registration_id>".$row1->registration_id."</registration_id>";
	$response .= "<lname><![CDATA[".html_entity_decode($row1->lname, ENT_QUOTES, 'UTF-8')."]]></lname>";
	$response .= "<fname><![CDATA[".html_entity_decode($row1->fname, ENT_QUOTES, 'UTF-8')."]]></fname>";
	$response .= "<email>".$row1->email."</email>";
	$response .= "<phone>".$row1->phone."</phone>";
	$response .= "<payment>".$row1->payment."</payment>";
	$response .= "<date>".$row1->date."</date>";
	$response .= "<payment_status>".$row1->payment_status."</payment_status>";
	$response .= "<amount_pd>".$row1->final_price."</amount_pd>";
	$response .= "<price_option><![CDATA[".html_entity_decode($row1->price_option, ENT_QUOTES, 'UTF-8')."]]></price_option>";
	$response .= "<coupon_code >".$row1->coupon_code."</coupon_code>";
	$response .= "<quantity>".$row1->quantity."</quantity>";
	$response .= "<payment_date>".$row1->payment_date."</payment_date>";
	$response .= "<event_id>".$row1->event_id."</event_id>";
	$response .= "<event_time>".$row1->event_time."</event_time>";
	$response .= "<end_time>".$row1->end_time."</end_time>";
	$response .= "<start_date>".$row1->start_date."</start_date>";
	$response .= "<end_date>".$row1->end_date."</end_date>";
	//$response .= "<attendee_session>".$row1->attendee_session."</attendee_session>";
	//$response .= "<transaction_details><![CDATA[".html_entity_decode($row1->transaction_details, ENT_QUOTES, 'UTF-8')."]]></transaction_details>";
	$response .= "<checked_in>".$row1->checked_in."</checked_in>";
	$response .= "<checked_in_quantity>".$row1->checked_in_quantity."</checked_in_quantity>";
	$response .= "</attendee><error>0</error><desc>Attendee Already Checked In</desc></checkCoupon>";
	echo $response;	
}