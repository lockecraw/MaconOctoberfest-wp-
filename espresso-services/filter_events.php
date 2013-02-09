<?php
include_once('../wp-config.php');
include_once('../wp-load.php');
include_once('../wp-includes/wp-db.php');

//Uncomment the next line if using multiple event managers
//$multi_user = true;

if( !isset($_POST['authenticate']) || $_POST['authenticate']==""){
	die("<?xml version='1.0'?><events><error>1</error><desc>Service Not Accessible</desc></events>");
}

if($_POST['authenticate'] != "fdljgrirtgibmpkjkgffdndfj1123124"){
	die("<?xml version='1.0'?><events><error>1</error><desc>Service Not Accessible</desc></events>");
}

if( !isset($_POST['username']) || $_POST['username']==""){
	die("<?xml version='1.0'?><events><error>1</error><desc>UserId is empty</desc></events>");
}
$username = $_POST['username'];

if( !isset($_POST['substring']) || $_POST['substring']==""){
	die("<?xml version='1.0'?><events><error>1</error><desc>Substring is empty</desc></events>");
}
$substring = $_POST['substring'];


$query_user = "SELECT ID FROM {$wpdb->prefix}users WHERE user_login='".$username."'";
$result_user = $wpdb->get_results($query_user);
if(count($result_user)==0){
	die("<?xml version='1.0'?><events><error>4</error><desc>Invalid Username</desc></events>");
}else{
	$userid = $result_user[0]->ID;
}

//get total count of event , this is required for paging
$countQuery = "SELECT id FROM {$wpdb->prefix}events_detail WHERE event_status != 'D'" ;
$multi_user == true ? $countQuery .= " AND wp_user='". $userid ."' ":'';
$countresult = $wpdb->get_results($countQuery);
$count = count($countresult);

//get he events
$query = "SELECT e.* ";
isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $query .= ", v.name venue_name " : '';
$query .= " FROM {$wpdb->prefix}events_detail e ";
isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $query .= " LEFT JOIN {$wpdb->prefix}events_venue_rel r ON r.event_id = e.id LEFT JOIN {$wpdb->prefix}events_venue v ON v.id = r.venue_id " : '';
$query .= " WHERE e.event_status != 'D' ";
$multi_user == true ? $query .= " AND wp_user='". $userid ."' ":'';
$query .= " AND event_name like '%".$substring."%'";
$result = $wpdb->get_results($query);

if(count($result)==0){
	die("<?xml version='1.0'?><events><error>4</error><desc>No events found</desc></events>");
}

$response = "<?xml version='1.0'?><events><error>0</error><desc>Success</desc><count>".$count."</count>";
foreach ($result as $row) {
	$response .= "<event>";
		$response .= "<id>".$row->id."</id>";
		$response .= "<event_code>".$row->event_code."</event_code>";
		$response .= "<event_name><![CDATA[".html_entity_decode($row->event_name, ENT_QUOTES, 'UTF-8')."]]></event_name>";
		$response .= "<event_identifier>".$row->event_identifier."</event_identifier>";
		$response .= "<start_date>".$row->start_date."</start_date>";
		$response .= "<end_date>".$row->end_date."</end_date>";
		$response .= "<venue_title><![CDATA[".html_entity_decode($row->venue_name, ENT_QUOTES, 'UTF-8')."]]></venue_title>";
		$response .= "</event>";
}
$response .= "</events>";
	
echo $response;