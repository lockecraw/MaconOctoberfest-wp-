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
	<title><?php echo stripslashes_deep($org_options['organization']) ?> <?php _e('Ticket for', 'event_espresso'); ?> <?php echo stripslashes_deep($data->attendee->fname . ' ' .$data->attendee->lname) ?> | <?php echo $data->attendee->registration_id ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<!-- Base Stylesheet do not change or remove -->
	<link rel="stylesheet" type="text/css" href="<?php echo $base_dir.'base.css'; ?>" media="screen" />

	<!-- Primary Style Sheet -->
	<link rel="stylesheet" type="text/css" href="<?php echo ESPRESSO_TICKETING_FULL_URL . 'templates/extra/badge-tall.css'; ?>" />

	<!-- Make sure the buttons don't print -->
	<style type="text/css">
	@media print{
		.noPrint{display:none!important;}
	}
	</style>
</head>
<body>
	<div class="outside">
		<div class="print_button_div">
			<form>
				<input class="print_button noPrint" type="button" value=" <?php _e( 'Print Ticket', 'event_espresso' ); ?> " onclick="window.print();return false;" />
			</form>
			<form method="post" action="<?php echo espresso_ticket_url($data->attendee->id, $data->attendee->registration_id, '&pdf=true'); ?>" >
				<input class="print_button noPrint" type="submit" value=" <?php _e( 'Download PDF', 'event_espresso' ); ?> " />
			</form>
		</div>
		<div class="instructions"><?php _e( 'Print and bring this badge with you to the event.','event_espresso' ); ?></div>
		<div class="badge">
			<table class="badge-top" border="0">
				<tr>
					<td colspan="2" class="logo" valign="middle">
						[ticket_logo_image]
					</td>
				</tr>
				<tr>
					<td colspan="2" class="event-details">
						<span class="event-name">[event_name]</span><br>
						[start_date]<br>
						[venue_title]
					</td>
				</tr>
				<tr>
					<td class="gravatar" valign="middle" width="110">
						[gravatar]
					</td>
					<td class="attendee-details" valign="middle">
						<span class="attendee-name">[fname] [lname]</span><br>
						<?php _e( 'Attendee #:', 'event_espresso' ); ?> [att_id]<br>
						[ticket_type]<br>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="qr">
						[qr_code]<br>
						<span class="reg-id"><?php _e( 'Registration ID:', 'event_espresso' ); ?> [registration_id]</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="credit">
						<?php echo sprintf( __( 'Powered by the <a href="%s" target="_blank">Event Espresso Ticketing System</a>', 'event_espresso' ) , 'http://eventespresso.com' ); ?> <img src="<?php echo ESPRESSO_TICKETING_FULL_URL; ?>/templates/img/coffee-cup.png" alt="Event Espresso">
					</td>
				</tr>
			</table>
		</div>
		<div class="divider"></div>
		<div class="extra_info">
			<table width="650" border="0">
				<tr>
					<td class="attendee" align="left" valign="top">
						<div class="info-title"><?php _e( 'Ticket information', 'event_espresso' ); ?></div>
						<p>
							<span class="attendee-name">[fname] [lname]</span> (<?php _e( 'ID:', 'event_espresso' ); ?> [att_id])</p>
						<p>
							[registration_id]
						</p>
						<p>
							<strong>[event_name]</strong><br>
							<?php _e( '# of tickets:', 'event_espresso' ); ?> [ticket_qty]<br>
							<div class="price">[cost]</div>
						</p>
						<p>
							[qr_code]
						</p>
						<p>
							<div class="info-title"><?php _e( 'Additional Information', 'event_espresso' ); ?></div>
						</p>
						<p>
							[ticket_content]
						</p>
					</td>
					<td class="venue" align="left" valign="top">
						<p>
							<div class="info-title"><?php _e( 'Venue Information', 'event_espresso' ); ?></div>
						</p>
						<p class="gmap">
							[google_map_image]
						</p>
						<p>
							<div class="info-title">[venue_title]</div><br>
							[venue_address] [venue_address2]<br>
							[venue_city], [venue_state]<br>
							[venue_phone]<br>
							[venue_description]
						</p>
					</td>
				</tr>
			</table>
		</div>
	</div>
</body>
</html>