<h2>General Settings</h2>
<hr />

<form name="polycomphones_general" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=general_edit">
<table>		
<tbody>
	<tr><td colspan="2"><h5><?php echo _("Network Options")?><hr/></h5></td></tr>			
	<tr>
		<td width="175"><?php echo _("Registration Address")?></td>
		<td><?php echo form_input('address', $general['address']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Registration Port")?></td>
		<td><?php echo form_input('port', $general['port']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("NTP Server")?></td>
		<td><?php echo form_input('tcpIpApp_sntp_address', $general['tcpIpApp_sntp_address']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Time Zone")?></td>
		<td><?php echo form_dropdown('tcpIpApp_sntp_gmtOffset', polycomphones_dropdown('tcpIpApp_sntp_gmtOffset', true, ''), $general['tcpIpApp_sntp_gmtOffset']); ?></td>	
	</tr>
	<tr><td colspan="2"><h5><?php echo _("Line Default Options")?><hr/></h5></td></tr>			
	<tr>
		<td><?php echo _("Line Keys")?></td>
		<td><?php echo form_dropdown('lineKeys', polycomphones_dropdown('lineKeys'), $general['lineKeys']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Ring Type")?></td>
		<td><?php echo form_dropdown('ringType', polycomphones_dropdown('ringType'), $general['ringType']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Missed Call Tracking")?></td>
		<td><?php echo form_dropdown('missedCallTracking', polycomphones_dropdown('missedCallTracking'), $general['missedCallTracking']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("MWI Callback Mode")?></td>
		<td><?php echo form_dropdown('callBackMode', polycomphones_dropdown('callBackMode'), $general['callBackMode']); ?></td>	
	</tr>
	<tr><td colspan="2"><h5><?php echo _("Phone Default Options")?><hr/></h5></td></tr>	
	<tr>
		<td><?php echo _("Call Waiting Ring")?></td>
		<td><?php echo form_dropdown('call_callWaiting_ring', polycomphones_dropdown('call_callWaiting_ring'), $general['call_callWaiting_ring']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Call Hold Reminder")?></td>
		<td><?php echo form_dropdown('call_hold_localReminder_enabled', polycomphones_dropdown('call_hold_localReminder_enabled'), $general['call_hold_localReminder_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Directed Call Pickup")?></td>
		<td><?php echo form_dropdown('feature_directedCallPickup_enabled', polycomphones_dropdown('feature_directedCallPickup_enabled'), $general['feature_directedCallPickup_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Backlight Idle Intensity")?></td>
		<td><?php echo form_dropdown('up_backlight_idleIntensity', polycomphones_dropdown('up_backlight_idleIntensity'), $general['up_backlight_idleIntensity']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Backlight On Intensity")?></td>
		<td><?php echo form_dropdown('up_backlight_onIntensity', polycomphones_dropdown('up_backlight_onIntensity'), $general['up_backlight_onIntensity']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("NAT Keepalive Interval")?></td>
		<td><?php echo form_dropdown('nat_keepalive_interval', polycomphones_dropdown('nat_keepalive_interval'), $general['nat_keepalive_interval']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>