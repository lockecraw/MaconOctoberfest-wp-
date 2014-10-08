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
<link rel="stylesheet" type="text/css" href="<?php echo $base_dir.$data->event->css_file; ?>" />

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
  <div class="instructions"><?php _e('Print and bring this ticket with you to the event', 'event_espresso'); ?></div>
  <div class="ticket">
    <table width="100%" border="0">
      <tr>
        <td width="55%" rowspan="2" valign="top"><span class="top_event_title">[event_name]</span><br>
            [start_date] [start_time] <br>
            [ticket_type]<br>
            [venue_title]<br>

          <div class="logo">[ticket_logo_image]</div></td>
        <td width="22%" align="right" valign="top"><div class="gravatar">[gravatar]</div></td>
        <td width="23%" align="right" valign="top"><div class="qr_code">[qr_code]</div></td>
      </tr>
      <tr>
        <td colspan="2" align="right" valign="top"><span class="price">[cost]</span><br>
          [fname] [lname] (<?php _e('ID:', 'event_espresso'); ?> [att_id])<br>
          [registration_id]<br>
		  <?php _e('Qty.', 'event_espresso'); ?> [ticket_qty]</td>
      </tr>
    </table>
  </div>
  <div class="extra_info">
    <div class="divider"></div>
    <table width="100%" border="0">
      <tr>
        <td width="45%" height="35" align="left" valign="top"><p><strong><?php _e('Location:', 'event_espresso'); ?></strong><br>
            [venue_title]<br>
            [venue_address]<br>
			[venue_address2]<br>
            [venue_city], [venue_state]<br>
            [venue_phone]</p>
          <p><strong><?php _e('More Information:', 'event_espresso'); ?></strong></p>
          <p>[venue_description]</p>
          <p><strong><?php _e('Ticket Instructions:', 'event_espresso'); ?></strong></p>
          <p>[ticket_content]</p></td>
        <td width="55%" valign="top">[google_map_image]</td>
      </tr>
    </table>
  </div>
  <div class="footer"><?php echo sprintf( __( 'Powered by the <a href="%s" target="_blank">Event Espresso Ticketing System</a> for WordPress', 'event_espresso' ) , 'http://eventespresso.com' ); ?></div>
</div>
</body>
</html>