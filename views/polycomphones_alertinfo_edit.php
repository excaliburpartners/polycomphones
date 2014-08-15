<h2>Edit Alert Info</h2>
<hr />

<form name="polycomphones_alertinfo_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=alertinfo_edit&edit=<?php echo $_GET['edit'];?>">
<table>
<tbody>		
	<tr>
		<td width="175"><?php echo _("Class")?></td>
		<td><?php echo $alert['id']; ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Name")?></td>
		<td><?php echo form_input('name', $alert['name']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Call Waiting")?></td>
		<td><?php echo form_dropdown('callwait', polycomphones_dropdown('alert_callwait'), $alert['callwait']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Mic Mute")?></td>
		<td><?php echo form_dropdown('micmute', polycomphones_dropdown('disabled_enabled'), $alert['micmute']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Ring Type")?></td>
		<td><?php echo form_dropdown('ringer', polycomphones_dropdown('ringType'), $alert['ringer']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Type")?></td>
		<td><?php echo form_dropdown('type', polycomphones_dropdown('alert_type'), $alert['type']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Alert Info")?></td>
		<td><?php echo form_input('alertinfo', $alert['alertinfo']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>