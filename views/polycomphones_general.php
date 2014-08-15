<h2>General Settings</h2>
<hr />

<form name="polycomphones_general" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=general_edit">
<table>		
<tbody>
	<tr><td colspan="2"><h5><?php echo _("General Options")?><hr/></h5></td></tr>
	<tr>
		<td><?php echo _("Extension Length")?>*</td>
		<td><?php echo form_dropdown('digits', polycomphones_dropdown_numbers(2, 10), $general['digits']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Web Config Utility")?>*<span class="help">?<span style="display: none;">If 'Disabled', the Web Configuration Utility is disabled. If 'Enabled', the Web Configuration Utility is enabled.</span></span></td>
		<td><?php echo form_dropdown('httpd_cfg_enabled', polycomphones_dropdown('disabled_enabled'), $general['httpd_cfg_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Applications Home URL")?><span class="help">?<span style="display: none;">The URL of the microbrowser's Home page.</span></span></td>
		<td><?php echo form_input('mb_main_home', $general['mb_main_home'], 'size="40"'); ?></td>	
	</tr>
	<tr><td colspan="2"><h5><?php echo _("Line Default Options")?><hr/></h5></td></tr>			
	<tr>
		<td><?php echo _("Line Keys")?><span class="help">?<span style="display: none;">Specify the number of line keys to use for a single registration.</span></span></td>
		<td><?php echo form_dropdown('lineKeys', polycomphones_dropdown_numbers(1, 4), $general['lineKeys']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Ring Type")?></td>
		<td><?php echo form_dropdown('ringType', polycomphones_dropdown('ringType'), $general['ringType']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Missed Call Tracking")?>*</td>
		<td><?php echo form_dropdown('missedCallTracking', polycomphones_dropdown('disabled_enabled'), $general['missedCallTracking']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("MWI Callback Mode")?><span class="help">?<span style="display: none;">If 'Disabled', voice message retrieval and notification are disabled.</span></span></td>
		<td><?php echo form_dropdown('callBackMode', polycomphones_dropdown('callBackMode'), $general['callBackMode']); ?></td>	
	</tr>
	<tr><td colspan="2"><h5><?php echo _("Phone Default Options")?><hr/></h5></td></tr>	
	<?php 
	$phone_options = $general;
	$phone_default = false;
	require 'modules/polycomphones/views/polycomphones_phone_options.php'; 
	?>
	<tr><td colspan="2"><h5><?php echo _("Power Saving Options")?><hr/></h5></td></tr>

	<tr>
	<td colspan="2">
	
	<table>
	<tr>
	<td valign="top">
	
	<table>
		<tr>
			<td><?php echo _("Idle Timeout Office Hours")?><span class="help">?<span style="display: none;">The number of minutes to wait while the phone is idle during office hours before activating power saving.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_idleTimeout_officeHours', polycomphones_dropdown_numbers(30, 600, 30, '1', '1'), $general['powerSaving_idleTimeout_officeHours']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Monday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_monday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_monday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Tuesday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_tuesday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_tuesday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Wednesday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_wednesday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_wednesday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Thursday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_thursday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_thursday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Friday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_friday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_friday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Saturday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_saturday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_saturday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Day Start Sunday")?><span class="help">?<span style="display: none;">The starting hour for the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_startHour_sunday', polycomphones_dropdown_numbers(0, 23), $general['powerSaving_officeHours_startHour_sunday']); ?></td>	
		</tr>
	</table>

	</td>
	<td valign="top" style="padding-left: 20px">
	
	<table>
		<tr>
			<td><?php echo _("Idle Timeout Off Hours")?><span class="help">?<span style="display: none;">The number of minutes to wait while the phone is idle during off hours before activating power saving.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_idleTimeout_offHours', polycomphones_dropdown_numbers(1, 10), $general['powerSaving_idleTimeout_offHours']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Monday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_monday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_monday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Tuesday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_tuesday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_tuesday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Wednesday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_wednesday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_wednesday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Thursday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_thursday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_thursday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Friday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_friday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_friday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Saturday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_saturday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_saturday']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Duration Sunday")?><span class="help">?<span style="display: none;">The duration of the day's office hours.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_officeHours_duration_sunday', polycomphones_dropdown_numbers(0, 24), $general['powerSaving_officeHours_duration_sunday']); ?></td>	
		</tr>
	</table>
	
	</td>
	</tr>
	</table>
	
	</td>
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>