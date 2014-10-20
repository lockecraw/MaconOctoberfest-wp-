<style>
<?php include('css/red.css');?>
</style>
<div class="ticket">
	<table width="100%" border="0">
	  <tr>
		<td width="55%" rowspan="2" valign="top">Race Type: <strong>[ticket_type]</strong><br>
		<span class="top_event_title">[event_name]</span><br>
			[start_date] - [start_time] <br>
			Location: Central City Park <br>
			Willie Smokie Glover Drive - Macon, Georgia 31201		  
		  <div class="logo"><img src="<?php bloginfo('template_url'); ?>/images/CycloCross.png"/></div></td>
		<td width="23%" align="right" valign="top"><div class="qr_code">[qr_code]</div></td>
	  </tr>
	  <tr>
		<td colspan="2" align="right" valign="top">
		<span class="price">Ticket Qty: [ticket_qty]</span><br>
		Price: [cost]<br>
		  [fname] [lname] (ID: [att_id])<br>
		  [registration_id]
		  </td>
	  </tr>
	</table>
</div>