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
		<link rel="stylesheet" type="text/css" href="<?php echo $base_dir; ?>base.css" media="screen" />

		<!-- Primary Style Sheet -->
		<link rel="stylesheet" type="text/css" href="<?php echo ESPRESSO_TICKETING_FULL_URL .'templates/extra/hello.css'; ?>" />

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
			<div class="instructions"><?php _e( 'Print and bring this ticket with you to the event.', 'event_espresso' ); ?></div>
			<div class="ticket">
				<table class="hello" border="0">
					<tr>
						<td width="100%" colspan="3" class="hello-top" valign="top">
							<?php echo sprintf( __( '%1$sHello%2$s my name is', 'event_espresso' ), '<span class="top_event_title">', '</span><br>' ); ?>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="3" class="hello-body" valign="middle">
							[fname]
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="3">
							<table class="hello-bottom" width="100%">
								<tr>
									<td width="100%" border="0">
										[event_name]
									</td>
								</tr>
								<tr>
									<td width="100%" class="credit" border="0">
										<?php echo sprintf( __( 'Powered by the <a href="%s" target="_blank">Event Espresso Ticketing System</a> for WordPress', 'event_espresso' ) , 'http://eventespresso.com' ); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="divider"></div>
			<div class="extra_info">
				<table width="100%" border="0">
					<tr>
						<td width="45%" align="left" valign="top">
							<p>
								<div class="info-title"><?php _e( 'Attendee Information', 'event_espresso' ); ?></div>
							</p>
							<table width="100%">
								<tr>
									<td width="33%" align="center">
										[gravatar]
									</td>
									<td align="left">
										[fname] [lname] (ID: [att_id])<br>
										[registration_id]
									</td>
								</tr>
							</table>
							<p>
								<div class="info-title"><?php _e( 'Ticket Information', 'event_espresso' ); ?></div>
							</p>
							<table width="100%">
								<tr>
									<td width="33%" align="center">
										[qr_code]
									</td>
									<td valign="middle">
										<p>[event_name]<br>
										<?php _e( '# of tickets:', 'event_espresso' ); ?> [ticket_qty]</p>
										<p class="price">[cost]</p>
									</td>
								</tr>
							</table>
							<p>
								<div class="info-title"><?php _e( 'Additional Information', 'event_espresso' ); ?></div>
							</p>
							<p>
								[ticket_content]
							</p>
						</td>
						<td width="55%" align="left" valign="top">
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