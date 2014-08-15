<h2><?php echo empty($_GET['edit']) ? 'Add' : 'Edit'; ?> External Line</h2>
<hr />

<form name="polycomphones_externallines_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=externallines_edit&edit=<?php echo $_GET['edit'];?>">
<table>
<tbody>		
	<tr>
		<td width="175"><?php echo _("Name")?></td>
		<td><?php echo form_input('name', $line['name']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Line Label")?><span class="help">?<span style="display: none;">The text label that displays next to the line key.</span></span></td>
		<td><?php echo form_input('label', $line['settings']['label']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("User ID")?><span class="help">?<span style="display: none;">User ID to be used for authentication challenges.</span></span></td>
		<td><?php echo form_input('user', $line['settings']['user']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Secret")?><span class="help">?<span style="display: none;">The password to be used for authentication challenges.</span></span></td>
		<td><?php echo form_input('secret', $line['settings']['secret']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Server Address")?><span class="help">?<span style="display: none;">The IP address or host name of the SIP server.</span></span></td>
		<td><?php echo form_input('address', $line['settings']['address']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Server Port")?><span class="help">?<span style="display: none;">The port of the SIP server. Example: 5060</span></span></td>
		<td><?php echo form_input('port', $line['settings']['port']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Transport")?><span class="help">?<span style="display: none;">The transport method the phone uses to communicate with the SIP server.</span></span></td>
		<td><?php echo form_dropdown('transport', polycomphones_dropdown('transport'), $line['settings']['transport']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Register")?><span class="help">?<span style="display: none;">If 'Yes', phone will register with SIP server to receive inbound calls. </span></span></td>
		<td><?php echo form_dropdown('register', polycomphones_dropdown('register'), $line['settings']['register']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("MWI Callback")?><span class="help">?<span style="display: none;">The contact to call when retrieving messages.</span></span></td>
		<td><?php echo form_input('mwicallback', $line['settings']['mwicallback']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>