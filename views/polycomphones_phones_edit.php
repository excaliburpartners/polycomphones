<h2><?php echo empty($_GET['edit']) ? 'Add' : 'Edit'; ?> Phone</h2>
<hr />

<?php
	$dropdown_lines = polycomphones_dropdown_lines($_GET['edit']);
	$dropdown_attendant = polycomphones_dropdown_attendant();

	$newline = '
	<tr>
		<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
		<td class="index"></td>
		<td>'.form_dropdown('line[]', array(), '', 'id="newline"').'</td>
		<td>'.form_dropdown('lineKeys[]', polycomphones_dropdown_numbers(1, 4, 1, true), '').'</td>	
		<td>'.form_dropdown('ringType[]', polycomphones_dropdown('ringType', true), '').'</td>	
		<td>'.form_dropdown('missedCallTracking[]', polycomphones_dropdown('disabled_enabled', true), '').'</td>
		<td>'.form_dropdown('callBackMode[]', polycomphones_dropdown('callBackMode', true), '').'</td>	
		' . ($features_module ? '
		<td>'.form_dropdown('serverFeatureControl_dnd[]', polycomphones_dropdown('client_server', true), '').'</td>	
		<td>'.form_dropdown('serverFeatureControl_cf[]', polycomphones_dropdown('client_server', true), '').'</td>
		' : '
		<input type="hidden" name="serverFeatureControl_dnd[]" value="">
		<input type="hidden" name="serverFeatureControl_cf[]" value="">
		' ) . '		
		<td><img src="images/trash.png" class="deleteline" style="cursor:pointer; float:none;" alt="remove" title="Click to delete line"></td>
	</tr>';
	
	$newattendant = '
	<tr>
		<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
		<td class="index"></td>
		<td>'.form_dropdown('attendant[]', array(), '', 'id="newattendant"').'</td>
		<td>'.form_input('label[]', '', 'maxlength="30"').'</td>	
		<td class="type">'.form_dropdown('type[]', polycomphones_dropdown('attendantType'), '', 'style="display: none"').'</td>
		<td><img src="images/trash.png" class="deleteattendant" style="cursor:pointer; float:none;" alt="remove" title="Click to delete attendent"></td>
	</tr>';
?>

<script type="text/javascript">
$(document).ready(function() {
	// MAC Validate
	$('input[name="mac"]').keyup(function() {
		$(this).removeClass("duplicate-exten");
		$(this).next("span").css("display", "none");
		var inputVal = $(this).val();
		var characterReg = /^([a-fA-F0-9]{12})$/;
		if(!characterReg.test(inputVal)) {
			$(this).addClass("duplicate-exten");
			$(this).next("span").css("display", "");
		}
	});
	
	$("form").submit(function( event ) {
		if($('input[name="mac"]').hasClass("duplicate-exten")) {
			event.preventDefault();
		}
	});	

	// Functions
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
	
	var loadDropdown = function(ui, list) {
		$.each(list, function (key, cat) {
			var group = $('<optgroup>',{label:key});
			
			if(cat.length == 0) {
				$("<option/>",{value:key,text:cat}).appendTo(ui);
			} else {
				$.each(cat,function(subkey,item) {
					$("<option/>",{value:subkey,text:item}).appendTo(group);
				});
				group.appendTo(ui);
			}
		});
		ui.removeAttr('id');
	};

	// Lines
	$(".addline").on("click",function() {
		$("#lines").append(<?php echo json_encode($newline); ?>);
		loadDropdown($("#newline"), <?php echo json_encode($dropdown_lines); ?>);
		tableIndex($("#lines"));
	});

	$("#lines").on("click", ".deleteline", function() {
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

	// Attendant Console
	$(".addattendant").on("click",function() {
		$("#attendants").append(<?php echo json_encode($newattendant); ?>);	
		loadDropdown($("#newattendant"), <?php echo json_encode($dropdown_attendant); ?>);
		tableIndex($("#attendants"));
	});

	$("#attendants").on("click", ".deleteattendant", function() {
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
	
	 $("#attendants").on("change", 'select[name="attendant[]"]', function() {
	 	var td = $(this).parent();
		var tr = td.parent();
		if($(this).val().startsWith('user_')) {
			$('td.type select', tr).css("display", "");
		} else {
			$('td.type select', tr).css("display", "none");
			$('td.type select', tr).val('');
		}
	 });
	 
	 // Flexible Assignment Validate
	 var flexibleValidate = function() {
		$(this).removeClass("duplicate-exten");
		$(this).next("span").css("display", "none");
		var inputVal = $(this).val();
		var characterReg = /^([0-9]+(-[0-9]+)?)(,([0-9]+(-[0-9]+)?))*$/;
		if(inputVal !='' && !characterReg.test(inputVal)) {
			$(this).addClass("duplicate-exten");
			$(this).next("span").css("display", "");
		}
	};
	 
	 $('input[name="lineKey_category_line"]').keyup(flexibleValidate);
	 $('input[name="lineKey_category_blf"]').keyup(flexibleValidate);
	 $('input[name="lineKey_category_favorites"]').keyup(flexibleValidate);
	 $('input[name="lineKey_category_unassigned"]').keyup(flexibleValidate);
	
	$("form").submit(function( event ) {
		if($('input[name="lineKey_category_line"]').hasClass("duplicate-exten") ||
			$('input[name="lineKey_category_blf"]').hasClass("duplicate-exten") ||
			$('input[name="lineKey_category_favorites"]').hasClass("duplicate-exten") ||
			$('input[name="lineKey_category_unassigned"]').hasClass("duplicate-exten")) {
			event.preventDefault();
		}
	});	
});
</script>

<form name="polycomphones_phones_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=<?php echo $_GET['edit'];?>">
<?php 
if(!empty($_GET['edit'])) { 
?>
<input type="button" value="Edit directory" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_directory&edit=<?php echo $device['mac'];?>'" />
<input type="button" value="Delete phone" title="Delete this phone" onclick="if(confirm('Are you sure you want to delete this phone?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&delete=<?php echo $_GET['edit'];?>'" />
<input type="button" value="Reboot phone" title="Reboot this phone" onclick="if(confirm('Are you sure you want to reboot this phone?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&checkconfig=<?php echo $_GET['edit'];?>'" />
<input type="button" value="Clear overrides" title="Clear local setting overrides on this phone" onclick="if(confirm('Are you sure you want to clear local setting overrides on this phone?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&clearoverrides=<?php echo $device['mac'];?>'" />
<?php 
} 
?>

<table>		
<tbody>
	<tr><td colspan="2"><h5><?php echo _("Phone Details")?><hr/></h5></td></tr>	
	<tr>
		<td colspan="2">
		<table>
			<tr>
				<td width="175"><?php echo _("Phone Name")?></td>
				<td width="225"><?php echo form_input('name', $device['name'], 'size="30" maxlength="30"'); ?></td>	
			</tr>	
			<tr>
				<td><?php echo _("MAC Address")?></td>
				<?php if(!empty($_GET['edit'])) { ?>
				<td><?php echo $device['mac'] ?><input type="hidden" name="mac" value="<?php echo $device['mac'] ?>"></td>
				<?php } else { ?>
				<td>
					<?php echo form_input('mac', $device['mac'], 'size="15" maxlength="12"'); ?>
					<span style="display: none"><a href="#" title="Invalid MAC address">
						<img src="images/notify_critical.png" />
					</a></span>
				</td>	
				<?php } ?>
				<td width="100"><?php echo _("Last Config")?></td>
				<td><?php echo $device['lastconfig'] ?></td>	
			</tr>
			<tr>
				<td><?php echo _("Model")?></td>
				<td><?php echo $device['model'] ?></td>	
				<td><?php echo _("Last IP")?></td>
				<td><?php echo $device['lastip'] ?></td>	
			</tr>
		</table>
		</td>
	</tr>
	
	<tr><td colspan="3"><h5><?php echo _("Lines")?><hr/></h5></td></tr>	
	<tr>
		<td colspan="2">	
		<table id="lines">
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php echo _("Line")?>*</td>
				<td><?php echo _("Line Keys")?><span class="help">?<span style="display: none;">Specify the number of line keys to use for a single registration.</span></span></td>
				<td><?php echo _("Ring Type")?></td>
				<td><?php echo _("Missed Call")?>*</td>
				<td><?php echo _("MWI")?><span class="help">?<span style="display: none;">If 'Disabled', voice message retrieval and notification are disabled.</span></span></td>
				<?php
				if ($features_module) {
				?>	
				<td><?php echo _("DND")?><span class="help">?<span style="display: none;">If 'Server', do not disturb settings will use server event feature synchronization.</span></span></td>
				<td><?php echo _("CF")?><span class="help">?<span style="display: none;">If 'Server', call forward settings will use server event feature synchronization.</span></span></td>
				<?php
				}
				?>
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
				<td><?php echo form_dropdown('line[]', $dropdown_lines, $line['line']); ?></td>
				<td><?php echo form_dropdown('lineKeys[]', polycomphones_dropdown_numbers(1, 4, 1, true), $line['settings']['lineKeys']); ?></td>	
				<td><?php echo form_dropdown('ringType[]', polycomphones_dropdown('ringType', true), $line['settings']['ringType']); ?></td>	
				<td><?php echo form_dropdown('missedCallTracking[]', polycomphones_dropdown('disabled_enabled', true), $line['settings']['missedCallTracking']); ?></td>
				<td><?php echo form_dropdown('callBackMode[]', polycomphones_dropdown('callBackMode', true), $line['settings']['callBackMode']); ?></td>	
				<?php
				if ($features_module) {
				?>	
				<td><?php echo form_dropdown('serverFeatureControl_dnd[]', polycomphones_dropdown('client_server', true), $line['settings']['serverFeatureControl_dnd']); ?></td>	
				<td><?php echo form_dropdown('serverFeatureControl_cf[]', polycomphones_dropdown('client_server', true), $line['settings']['serverFeatureControl_cf']); ?></td>	
				<?php
				} else { 
				?>
				<input type="hidden" name="serverFeatureControl_dnd[]" value="<?php echo $line['settings']['serverFeatureControl_dnd']; ?>">
				<input type="hidden" name="serverFeatureControl_cf[]" value="<?php echo $line['settings']['serverFeatureControl_cf']; ?>">
				<?php
				}
				?>
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
				<td><?php echo _("Attendant")?>*</td>
				<td><?php echo _("Custom Label")?>*<span class="help">?<span style="display: none;">The text label displays adjacent to the associated line key.</span></span></td>
				<td><?php echo _("Type")?><span class="help">?<span style="display: none;">If 'Normal', the default action is to initiate a call if the user is idle or busy and to perform a directed call pickup if the user is ringing. Any active calls are first placed on hold.<br />If 'Automata', the default action when is to perform a park/blind transfer of any currently active call. If there is no active call and the monitored user is ringing/busy, an attempt to perform a directed call pickup/park retrieval is made</span></span></td>
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
				<td><?php echo form_dropdown('attendant[]', $dropdown_attendant, $attendant['attendant']); ?></td>
				<td><?php echo form_input('label[]', $attendant['label'], 'maxlength="30"'); ?></td>
				<td class="type"><?php echo form_dropdown('type[]', polycomphones_dropdown('attendantType'), $attendant['type'], strpos($attendant['attendant'], 'user_') === 0 ? '' : 'style="display: none"'); ?></td>
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
	
	<tr><td colspan="3"><h5><?php echo _("Flexible Key Assignment")?><hr/></h5></td></tr>
	
	<tr>
	<td colspan="2">
	
	<table>
	<tr>
	<td valign="top">
	
	<table>
		<tr>
			<td><?php echo _("Flexible Assignment")?>*</td>
			<td><?php echo form_dropdown('lineKey_reassignment_enabled', polycomphones_dropdown('disabled_enabled'), $device['settings']['lineKey_reassignment_enabled']); ?></td>	
		</tr>
		<tr>
			<td><?php echo _("Line Keys")?>*</td>
			<td>
				<?php echo form_input('lineKey_category_line', $device['settings']['lineKey_category_line']); ?>
				<span style="display: none"><a href="#" title="Invalid key range">
					<img src="images/notify_critical.png" />
				</a></span>
			</td>	
		</tr>
		<tr>
			<td><?php echo _("Attendant Keys")?>*</td>
			<td>
				<?php echo form_input('lineKey_category_blf', $device['settings']['lineKey_category_blf']); ?>
				<span style="display: none"><a href="#" title="Invalid key range">
					<img src="images/notify_critical.png" />
				</a></span>
			</td>	
		</tr>
		<tr>
			<td><?php echo _("Favorite Keys")?>*</td>
			<td>
				<?php echo form_input('lineKey_category_favorites', $device['settings']['lineKey_category_favorites']); ?>
				<span style="display: none"><a href="#" title="Invalid key range">
					<img src="images/notify_critical.png" />
				</a></span>
			</td>	
		</tr>
		<tr>
			<td><?php echo _("Unassigned Keys")?>*</td>
			<td>
				<?php echo form_input('lineKey_category_unassigned', $device['settings']['lineKey_category_unassigned']); ?>
				<span style="display: none"><a href="#" title="Invalid key range">
					<img src="images/notify_critical.png" />
				</a></span>
			</td>	
		</tr>
	</table>

	</td>
	<td valign="top" style="padding-left: 20px">
	
	<table>
	<?php 
	if(strpos($device['model'], 'SPIP_450') !== false) { 
	?>
		<tr>
			<td>SoundPoint IP 450</td>
			<td>3 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'SPIP_550') !== false || strpos($device['model'], 'SPIP_560') !== false) { 
	?>
		<tr>
			<td>SoundPoint IP 550/560</td>
			<td>4 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'SPIP_650') !== false || strpos($device['model'], 'SPIP_670') !== false) { 
	?>		
		<tr>
			<td>SoundPoint IP 650/670</td>
			<td>6 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'SPIP') !== false && strpos($device['model'], 'SPIP_3') == false) { 
	?>		
		<tr>
			<td>SoundPoint IP Expansion</td>
			<td>14 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'VVX_300') !== false || strpos($device['model'], 'VVX_310') !== false) { 
	?>	
		<tr>
			<td>VVX 300/310</td>
			<td>6 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'VVX_400') !== false || strpos($device['model'], 'VVX_410') !== false) { 
	?>	
		<tr>
			<td>VVX 400/410</td>
			<td>12 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'VVX_500') !== false) { 
	?>	
		<tr>
			<td>VVX 500</td>
			<td>12 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'VVX_600') !== false) { 
	?>	
		<tr>
			<td>VVX 600</td>
			<td>16 keys</td>	
		</tr>
	<?php 
	} 
	if(strpos($device['model'], 'VVX') !== false) { 
	?>	
		<tr>
			<td>VVX Paper Expansion</td>
			<td>40 keys</td>	
		</tr>
		<tr>
			<td>VVX Color Expansion</td>
			<td>28 keys x 3 pages</td>	
		</tr>
	<?php 
	} 
	?>	
	</table>
	
	</td>
	</tr>
	</table>
	
	</td>
	</tr>
	
	<tr><td colspan="2"><h5 style="margin-bottom: 0"><?php echo _("Phone Options")?><hr/></h5></td></tr>
	<?php 
	$phone_options = $device['settings'];
	$phone_default = true;
	require 'modules/polycomphones/views/polycomphones_phone_options.php'; 
	?>
	
	<tr><td colspan="2"><h5><?php echo _("Corporate Options")?><hr/></h5></td></tr>	
	<tr>
		<td width="175"><?php echo _("Corporate Directory")?></td>
		<td><?php echo form_dropdown('feature_corporateDirectory_enabled', polycomphones_dropdown('disabled_enabled', true), $device['settings']['feature_corporateDirectory_enabled']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Exchange Calendar")?>*</td>
		<td><?php echo form_dropdown('feature_exchangeCalendar_enabled', polycomphones_dropdown('disabled_enabled', true), $device['settings']['feature_exchangeCalendar_enabled']); ?></td>	
	</tr>
</tbody>
</table>
<p>* Changing these fields will cause phone to restart</p>

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>