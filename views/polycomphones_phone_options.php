	<tr>
	<td colspan="2">
	
	<table>
	<tr>
	<td valign="top">
	
	<table width="280">
		<tr><td colspan="2"><h5><?php echo _("Call Control")?><hr/></h5></td></tr>
		<tr>
			<td width="175"><?php echo _("Redundant Soft Keys")?><span class="help">?<span style="display: none;">If 'Disabled' and the phone has hard keys for Hold, Transfer, and Conference functions, the redundant soft keys will not displayed.</span></span></td>
			<td><?php echo form_dropdown('softkey_feature_basicCallManagement_redundant', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['softkey_feature_basicCallManagement_redundant']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Prefer Blind Transfer")?>*<span class="help">?<span style="display: none;">If 'Enabled', the blind transfer is the default mode. The Normal soft key is available to switch to a consultative transfer. If 'Disabled', the consultative transfer is the default mode. The Blind soft key is available to switch to a blind transfer.</span></span></td>
			<td><?php echo form_dropdown('call_transfer_blindPreferred', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['call_transfer_blindPreferred']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Call Waiting Ring")?>*<span class="help">?<span style="display: none;">Specifies the ringtone of incoming calls when another call is active.</span></span></td>
			<td><?php echo form_dropdown('call_callWaiting_ring', polycomphones_dropdown('call_callWaiting_ring', $phone_default), $phone_options['call_callWaiting_ring']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Call Hold Reminder")?>*<span class="help">?<span style="display: none;">If 'Enabled', users are reminded of calls that have been on hold for an extended period of time.</span></span></td>
			<td><?php echo form_dropdown('call_hold_localReminder_enabled', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['call_hold_localReminder_enabled']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Reject Busy on DND")?>*<span class="help">?<span style="display: none;">If 'Enabled', and DND is turned on, the phone rejects incoming calls with a busy signal. If 'Disabled', and DND is turned on, the phone gives a visual alert of incoming calls and no audio ringtone alert.</span></span></td>
			<td><?php echo form_dropdown('call_rejectBusyOnDnd', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['call_rejectBusyOnDnd']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Missed Calls Add to List")?><span class="help">?<span style="display: none;">If 'Enabled', calls answered from the remote phone are added to the local receive call list.</span></span></td>
			<td><?php echo form_dropdown('call_advancedMissedCalls_addToReceivedList', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['call_advancedMissedCalls_addToReceivedList']); ?></td>	
		</tr>
		
		<tr><td colspan="2"><h5><?php echo _("Directory")?><hr/></h5></td></tr>
		<tr>
			<td><?php echo _("Use Directory Names")?>*<span class="help">?<span style="display: none;">If 'Enabled', the name field in the local contact directory will be used as the caller ID for incoming calls.</span></span></td>
			<td><?php echo form_dropdown('up_useDirectoryNames', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['up_useDirectoryNames']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Directory Read-Only")?>*<span class="help">?<span style="display: none;">If 'Disabled', the local contact directory can be edited. If 'Enabled', the local contact directory is read-only.</span></span></td>
			<td><?php echo form_dropdown('dir_local_readonly', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['dir_local_readonly']); ?></td>	
		</tr>
		
		<tr><td colspan="2"><h5><?php echo _("General")?><hr/></h5></td></tr>
		<tr>
			<td><?php echo _("MWI Audible Alert")?><span class="help">?<span style="display: none;">If 'Enabled', the audible message waiting alert will be played.</span></span></td>
			<td><?php echo form_dropdown('se_pat_misc_messageWaiting_inst', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['se_pat_misc_messageWaiting_inst']); ?></td>	
		</tr>	
		<tr>
			<td><?php echo _("UC Desktop Connector")?>*<span class="help">?<span style="display: none;">If 'Enabled', the Polycom Desktop Connector is enabled on the administrative level.</span></span></td>
			<td><?php echo form_dropdown('apps_ucdesktop_adminEnabled', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['apps_ucdesktop_adminEnabled']); ?></td>	
		</tr>
	</table>

	</td>
	<td valign="top" style="padding-left: 20px">
	
	<table width="360">
		<tr><td colspan="2"><h5><?php echo _("Attendant Console")?><hr/></h5></td></tr>
		<tr>
			<td width="175"><?php echo _("Ring Type")?><span class="help">?<span style="display: none;">The ringtone to play when alerting on a monitored resource.</span></span></td>
			<td><?php echo form_dropdown('attendant_ringType', polycomphones_dropdown('ringType', $phone_default), $phone_options['attendant_ringType']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Directed Call Pickup")?>*<span class="help">?<span style="display: none;">If 'Enabled', the directed call pickup feature is enabled. If 'Disabled', call appearance options below are ignored.</span></span></td>
			<td><?php echo form_dropdown('feature_directedCallPickup_enabled', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['feature_directedCallPickup_enabled']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Call Display Normal")?><span class="help">?<span style="display: none;">If 'Enabled', the normal call appearance is spontaneously presented to the attendant when calls are alerting on a monitored resource.</span></span></td>
			<td><?php echo form_dropdown('attendant_spontaneousCallAppearances_normal', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['attendant_spontaneousCallAppearances_normal']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Call Display Automata")?><span class="help">?<span style="display: none;">If 'Enabled', the automatic call appearance is spontaneously presented to the attendant when calls are alerting on a monitored resource.</span></span></td>
			<td><?php echo form_dropdown('attendant_spontaneousCallAppearances_automata', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['attendant_spontaneousCallAppearances_automata']); ?></td>	
		</tr>
		
		<tr><td colspan="2"><h5><?php echo _("Headset")?><hr/></h5></td></tr>
		<tr>
			<td><?php echo _("Headset Mode")?><span class="help">?<span style="display: none;">If 'Disabled', handsfree mode will be used by default instead of the handset. If 'Enabled', the headset will be used as the preferred audio mode after the headset key is pressed for the first time, until the headset key is pressed again.</span></span></td>
			<td><?php echo form_dropdown('up_headsetMode', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['up_headsetMode']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Headset Type")?><span class="help">?<span style="display: none;">The Electronic Hookswitch mode for the phone's analog headset jack.</span></span></td>
			<td><?php echo form_dropdown('up_analogHeadsetOption', polycomphones_dropdown('up_analogHeadsetOption', $phone_default), $phone_options['up_analogHeadsetOption']); ?></td>	
		</tr>
		
		<tr><td colspan="2"><h5><?php echo _("Display")?><hr/></h5></td></tr>
		<tr>
			<td><?php echo _("Power Saving")?><span class="help">?<span style="display: none;">If 'Enabled', on VVX series phones the LCD display will turn off when not in use.</span></span></td>
			<td><?php echo form_dropdown('powerSaving_enable', polycomphones_dropdown('disabled_enabled', $phone_default), $phone_options['powerSaving_enable']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Backlight Idle Intensity")?><span class="help">?<span style="display: none;">The brightness of the LCD backlight when the phone is idle.</span></span></td>
			<td><?php echo form_dropdown('up_backlight_idleIntensity', polycomphones_dropdown('up_backlight_idleIntensity', $phone_default), $phone_options['up_backlight_idleIntensity']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Backlight On Intensity")?><span class="help">?<span style="display: none;">The brightness of the LCD backlight when the phone is active (in use).</span></span></td>
			<td><?php echo form_dropdown('up_backlight_onIntensity', polycomphones_dropdown('up_backlight_onIntensity', $phone_default), $phone_options['up_backlight_onIntensity']); ?></td>	
		</tr>
	</table>
	
	</td>
	</tr>
	</table>
	
	</td>
	</tr>