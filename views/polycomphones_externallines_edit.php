<h2>External Lines <?php echo empty($_GET['edit']) ? 'Add' : 'Edit'; ?></h2>
<hr />

<form name="polycomphones_externallines_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=externallines_edit&edit=<?php echo $_GET['edit'];?>">
<table>
<tbody>		
	<tr>
		<td width="175"><?php echo _("Name")?></td>
		<td><?php echo form_input('name', $line['name']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Line Label")?></td>
		<td><?php echo form_input('label', $line['settings']['label']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("User ID")?></td>
		<td><?php echo form_input('user', $line['settings']['user']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Secret")?></td>
		<td><?php echo form_input('secret', $line['settings']['secret']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Server Address")?></td>
		<td><?php echo form_input('address', $line['settings']['address']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Server Port")?></td>
		<td><?php echo form_input('port', $line['settings']['port']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Transport")?></td>
		<td><?php echo form_dropdown('transport', polycomphones_dropdown('transport'), $line['settings']['transport']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Register")?></td>
		<td><?php echo form_dropdown('register', polycomphones_dropdown('register'), $line['settings']['register']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("MWI Callback")?></td>
		<td><?php echo form_input('mwicallback', $line['settings']['mwicallback']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>