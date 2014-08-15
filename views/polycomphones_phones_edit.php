<h2>Phones <?php echo empty($_GET['edit']) ? 'Add' : 'Edit'; ?></h2>
<hr />

<?php
	$newline = '
	<tr>
		<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
		<td class="index"></td>
		<td>'.form_dropdown('line[]', polycomphones_dropdown_lines($_GET['edit']), '').'</td>
		<td>'.form_dropdown('lineKeys[]', polycomphones_dropdown('lineKeys', true), '').'</td>	
		<td>'.form_dropdown('ringType[]', polycomphones_dropdown('ringType', true), '').'</td>	
		<td>'.form_dropdown('missedCallTracking[]', polycomphones_dropdown('missedCallTracking', true), '').'</td>
		<td>'.form_dropdown('callBackMode[]', polycomphones_dropdown('callBackMode', true), '').'</td>	
		<td><img src="images/trash.png" class="deleteline" style="cursor:pointer; float:none;" alt="remove" title="Click to delete line"></td>
	</tr>';
	
	$newattendant = '
	<tr>
		<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
		<td class="index"></td>
		<td>'.form_dropdown('attendant[]', polycomphones_dropdown_attendant(), '').'</td>
		<td>'.form_input('label[]', '', 'maxlength="30"').'</td>	
		<td><img src="images/trash.png" class="deleteattendant" style="cursor:pointer; float:none;" alt="remove" title="Click to delete attendent"></td>
	</tr>';
?>

<script type="text/javascript">
$(document).ready(function() {
	var updateIndex = function(e, ui) {
        $('td.index', ui.item.parent()).each(function (i) {
            $(this).html(i + 1);
        });
    };
	
	var tableIndex = function(ui) {
        $('td.index', ui).each(function (i) {
            $(this).html(i + 1);
        });
    };

	$(".addline").on("click",function() {
		$("#lines").append('<?php echo json_encode($newline); ?>')
		tableIndex($("#lines"));
	});
		
    $("#lines").delegate(".deleteline", "click", function() {
        var td = $(this).parent();
        var tr = td.parent();
		var table = tr.parent();
        tr.remove();
		tableIndex(table);
    });
	
	$("#lines tbody").sortable({
		handle: ".sort",
		stop: updateIndex
	});
	
	$(".addattendant").on("click",function() {
		$("#attendants").append('<?php echo json_encode($newattendant); ?>')
		tableIndex($("#attendants"));
	});
		
    $("#attendants").delegate(".deleteattendant", "click", function() {
        var td = $(this).parent();
		var tr = td.parent();
		var table = tr.parent();
        tr.remove();
		tableIndex(table);
    });
	
	$("#attendants tbody").sortable({
		handle: ".sort",
		stop: updateIndex
	});
});
</script>

<form name="polycomphones_phones_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=<?php echo $_GET['edit'];?>">
<table>		
<tbody>
	<tr><td colspan="2"><h5><?php echo _("Phone Details")?><hr/></h5></td></tr>	
	<tr>
		<td width="175"><?php echo _("Phone Name")?></td>
		<td><?php echo form_input('name', $device['name'], 'size="30" maxlength="30"'); ?></td>	
	</tr>	
	<tr>
		<td><?php echo _("MAC Address")?></td>
		<td><?php echo form_input('mac', $device['mac'], 'size="15" maxlength="12"'); ?></td>	
	</tr>	
	<tr><td colspan="3"><h5><?php echo _("Lines")?><hr/></h5></td></tr>	
	<tr>
		<td colspan="2">	
		<table id="lines">
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php echo _("Line")?></td>
				<td><?php echo _("Line Keys")?></td>
				<td><?php echo _("Ring Type")?></td>
				<td><?php echo _("Missed Call")?></td>
				<td><?php echo _("MWI Callback")?></td>
				<td>&nbsp;</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			foreach($device['lines'] as $line) {
			?>
			<tr>
				<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
				<td class="index"><?php echo $i;?></td>
				<td><?php echo form_dropdown('line[]', polycomphones_dropdown_lines($_GET['edit']), $line['line']); ?></td>
				<td><?php echo form_dropdown('lineKeys[]', polycomphones_dropdown('lineKeys', true), $line['settings']['lineKeys']); ?></td>	
				<td><?php echo form_dropdown('ringType[]', polycomphones_dropdown('ringType', true), $line['settings']['ringType']); ?></td>	
				<td><?php echo form_dropdown('missedCallTracking[]', polycomphones_dropdown('missedCallTracking', true), $line['settings']['missedCallTracking']); ?></td>
				<td><?php echo form_dropdown('callBackMode[]', polycomphones_dropdown('callBackMode', true), $line['settings']['callBackMode']); ?></td>	
				<td><img src="images/trash.png" class="deleteline" style="cursor:pointer; float:none;" alt="remove" title="Click to delete line"></td>
			</tr>
			<?php
				$i++;
			}
			?>
		</tbody>
		</table>
		<input type="button" class="addline" value="<?php echo _("Add Line")?>"/>
		</td>
	</tr>
	<tr><td colspan="3"><h5><?php echo _("Attendant Console")?><hr/></h5></td></tr>	
	<tr>
		<td colspan="2">	
		<table id="attendants">
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php echo _("Attendant")?></td>
				<td><?php echo _("Custom Label")?></td>
				<td>&nbsp;</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			foreach($device['attendants'] as $attendant) {
			?>
			<tr>
				<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
				<td class="index"><?php echo $i;?></td>
				<td><?php echo form_dropdown('attendant[]', polycomphones_dropdown_attendant(), $attendant['attendant']); ?></td>
				<td><?php echo form_input('label[]', $attendant['label'], 'maxlength="30"'); ?></td>	
				<td><img src="images/trash.png" class="deleteattendant" style="cursor:pointer; float:none;" alt="remove" title="Click to delete attendent"></td>
			</tr>
			<?php
				$i++;
			}
			?>
		</tbody>
		</table>
		<input type="button" class="addattendant" value="<?php echo _("Add Attendant")?>"/>
		</td>
	</tr>
	<tr><td colspan="2"><h5><?php echo _("Phone Options")?><hr/></h5></td></tr>	
	<tr>
		<td><?php echo _("Call Waiting Ring")?></td>
		<td><?php echo form_dropdown('call_callWaiting_ring', polycomphones_dropdown('call_callWaiting_ring', true), $device['settings']['call_callWaiting_ring']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Call Hold Reminder")?></td>
		<td><?php echo form_dropdown('call_hold_localReminder_enabled', polycomphones_dropdown('call_hold_localReminder_enabled', true), $device['settings']['call_hold_localReminder_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Directed Call Pickup")?></td>
		<td><?php echo form_dropdown('feature_directedCallPickup_enabled', polycomphones_dropdown('feature_directedCallPickup_enabled', true), $general['feature_directedCallPickup_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Backlight Idle Intensity")?></td>
		<td><?php echo form_dropdown('up_backlight_idleIntensity', polycomphones_dropdown('up_backlight_idleIntensity', true), $device['settings']['up_backlight_idleIntensity']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Backlight On Intensity")?></td>
		<td><?php echo form_dropdown('up_backlight_onIntensity', polycomphones_dropdown('up_backlight_onIntensity', true), $device['settings']['up_backlight_onIntensity']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("NAT Keepalive Interval")?></td>
		<td><?php echo form_dropdown('nat_keepalive_interval', polycomphones_dropdown('nat_keepalive_interval', true),  $device['settings']['nat_keepalive_interval']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>