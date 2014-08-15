<h2>Edit Phone Directory</h2>
<hr />

<?php
	$newcontact = '
	<tr>
		<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
		<td class="index"></td>
		<td>'.form_input('fn[]', '', 'size="10"').'</td>
		<td>'.form_input('ln[]', '', 'size="10"').'</td>
		<td>'.form_input('ct[]', '', 'size="10"').'</td>
		<td>'.form_dropdown('sd[]', polycomphones_dropdown('disabled_enabled'), '').'</td>
		<td>'.form_dropdown('rt[]', polycomphones_dropdown('ringType', true), '').'</td>
		<td>'.form_dropdown('bw[]', polycomphones_dropdown('disabled_enabled'), '').'</td>
		<td><img src="images/trash.png" class="deletecontact" style="cursor:pointer; float:none;" alt="remove" title="Click to delete contact"></td>
	</tr>';
?>

<script type="text/javascript">
$(document).ready(function() {
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

	// Cotnact
	$(".addcontact").on("click",function() {
		$("#directory").append('<?php echo json_encode($newcontact); ?>');
		tableIndex($("#directory"));
	});

	$("#directory").delegate(".deletecontact", "click", function() {
		var td = $(this).parent();
		var tr = td.parent();
		var table = tr.parent();
		tr.remove();
		tableIndex(table);
	});

	$("#directory tbody").sortable({
		handle: ".sort",
		stop: updateIndex
	});
});
</script>

<form name="polycomphones_phones_directory" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=phones_directory&edit=<?php echo $_GET['edit'];?>">
<table>		
<tbody>
	<tr><td><p>Note: Phone must be rebooted for directory changes to load</p></td></tr>
	<tr>
		<td>	
		<table id="directory">
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php echo _("First Name")?></td>
				<td><?php echo _("Last Name")?></td>
				<td><?php echo _("Contact")?></td>
				<td><?php echo _("Favorite")?></td>
				<td><?php echo _("Ring Type")?></td>
				<td><?php echo _("Watch Buddy")?></td>
				<td>&nbsp;</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			foreach($directory as $contact) {
			?>
			<tr>
				<td class="sort"><img src="images/arrow_up_down.png" alt="sort" title="Drag up or down to reposition" /></td>
				<td class="index"><?php echo $i;?></td>
				<td><?php echo form_input('fn[]', $contact['fn'], 'size="10"'); ?></td>
				<td><?php echo form_input('ln[]', $contact['ln'], 'size="10"'); ?></td>
				<td><?php echo form_input('ct[]', $contact['ct'], 'size="10"'); ?></td>
				<td><?php echo form_dropdown('sd[]', polycomphones_dropdown('disabled_enabled'), $contact['sd']); ?></td>
				<td><?php echo form_dropdown('rt[]', polycomphones_dropdown('ringType', true), $contact['rt']); ?></td>
				<td><?php echo form_dropdown('bw[]', polycomphones_dropdown('disabled_enabled'), $contact['bw']); ?></td>
				<td><img src="images/trash.png" class="deletecontact" style="cursor:pointer; float:none;" alt="remove" title="Click to delete contact"></td>
			</tr>
			<?php
				$i++;
			}
			?>
		</tbody>
		</table>
		<input type="button" class="addcontact" value="<?php echo _("Add Contact")?>"/>
		</td>
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>