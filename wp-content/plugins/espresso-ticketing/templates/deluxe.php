<?php
global $org_options;

//Build the path to the css files
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "tickets/templates/css/base.css")) {
	$base_dir = EVENT_ESPRESSO_UPLOAD_URL . 'tickets/templates/css/';//If the template files have been moved to the uploads folder
} else {
	$base_dir = ESPRESSO_TICKETING_FULL_URL.'templates/css/';//Default location
}

//Output the $data (array) variable that contains the attendee information and ticket settings
//echo "<pre>".print_r($data,true)."</pre>";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo stripslashes_deep($org_options['organization']) ?> <?php echo sprintf( __( 'Ticket for %s', 'event_espresso' ), stripslashes_deep($data->attendee->fname . ' ' .$data->attendee->lname) ); ?> | <?php echo $data->attendee->registration_id ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!-- Base Stylesheet do not change or remove -->
<link rel="stylesheet" type="text/css" href="<?php echo ESPRESSO_TICKETING_FULL_URL; ?>templates/extra/deluxe.css" media="screen" />

<!-- Color options -->
<link rel="stylesheet" type="text/css" href="<?php echo $base_dir.$data->event->css_file; ?>" />

<!-- Make sure the buttons don't print -->
<style type="text/css">
@media print{
	.noPrint{display:none!important;}
}
</style>

</head>

<body id="deluxe-ticket">
	<div class="outside">
		<div class="print_button_div">
			<form>
				<input class="print_button noPrint" type="button" value=" <?php _e( 'Print Ticket', 'event_espresso' ); ?> " onclick="window.print();return false;" />
			</form>
			<form method="post" action="<?php echo espresso_ticket_url($data->attendee->id, $data->attendee->registration_id, '&pdf=true'); ?>" >
				<input class="print_button noPrint" type="submit" value=" <?php _e( 'Download PDF', 'event_espresso' ); ?> " />
			</form>
		</div>
		<div class="instructions"><?php _e( 'Print and bring this ticket with you to the event', 'event_espresso' ); ?></div>

		<table class="ticket">
			<tr>
				<td colspan="2" class="topinfo">
					<p>
						<span class="name">[event_name]</span><br>
						<span class="title"><?php _e( 'information:', 'event_espresso' ); ?> </span><span class="infotext">[ticket_content]</span>
					</p>
				</td>
			</tr>
			<tr>
				<td class="logo">
					<p>
						[ticket_logo_image]
					</p>
				</td>
				<td class="gravatar" valign="bottom">
					<p>
		    			[gravatar]
		    		</p>
		    		<p class="attendee">
			    		<span class="title2">[fname] [lname] (<?php _e( 'ID:', 'event_espresso' ); ?> [att_id])</span><br>
						<span class="infotext reg-id">[registration_id]</span>
					</p>
		    	</td>
		    </tr>
		</table>
		<table class="extra_info">
			<tr>
				<td class="ticket-info" width="50%">
					<span class="price">[cost]</span><br>
				    <span class="title"><?php _e( 'when:', 'event_espresso' ); ?> </span><span class="infotext">[start_date] [start_time]</span><br>
				    <span class="title"><?php _e( 'what:', 'event_espresso' ); ?> </span><span class="infotext">[ticket_type]</span><br>
				    <span class="title"><?php _e( 'where:', 'event_espresso' ); ?> </span><span class="infotext">[venue_title]</span><br><br>
				    <span class="title"><?php _e( 'location:', 'event_espresso' ); ?> </span><br>
				    <span class="infotext">[venue_address]</span><br>
				    <span class="infotext">[venue_city], [venue_state]</span><br>
				    <span class="infotext">[venue_phone]</span>
				</td>
				<td>
					<span class="map">[google_map_image]</span>
				</td>
				<td>
					<span class="qr_code">[qr_code]</span>
				</td>
			</tr>
		</table>
		<div class="footer"><?php echo sprintf( __( 'Powered by the <a href="%s" target="_blank">Event Espresso Ticketing System</a> for WordPress', 'event_espresso' ) , 'http://eventespresso.com' ); ?></div>
	</div>
</body>
</html>