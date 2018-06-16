<h2>Phones</h2>
<hr />

<div id="toolbar-polycom">
	<input type="button" value="Add phone" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=0'" />
	<input type="button" value="Update all" title="Send configuration update to all phones" onclick="if(confirm('Are you sure you want to update all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&pushcheck'" />
	<input type="button" value="Reboot all" title="Send reboot to all phones" onclick="if(confirm('Are you sure you want to reboot all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&checkconfig'" />
	<input type="button" value="Clear all overrides" title="Clear local setting overrides on all phones" onclick="if(confirm('Are you sure you want to clear local setting overrides on all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&clearoverrides'" />
</div>

<table data-toolbar="#toolbar-polycom" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped ext-list" id="table-polycom">
<thead>
<tr>
	<th data-sortable="true">Name</th>
	<th data-sortable="true">Model</th>
	<th data-sortable="true">Firmware</th>
	<th data-sortable="true">MAC</th>
	<th data-sortable="true">Lines</th>
	<th data-sortable="true">Last Config</th>
	<th data-sortable="true">IP Address</th>
	<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach ($devices as $device) {
?>
<tr>
	<td>
		<?php echo $device['name'] ?>
	</td>
	<td>
		<?php echo $device['model']?>
	</td>
	<td>
		<?php echo $device['version'] ?>
	</td>
	<td>
		<?php echo $device['mac'] ?>
	</td>
	<td>
		<?php
		$shownum = count($device['lines']) == 1 && $device['lines'][0]['lineid'] == "1" ? false : true;
		foreach($device['lines'] as $line) {
			if($shownum)
				echo 'Line '.$line['lineid'].': ';
			
			if($line['id'] != $line['deviceid'])
				echo 'Orphaned ' . $line['deviceid'];
			else
				echo $line['id'] . (!empty($line['extension']) ? ': '.$line['name'].' &lt;'.$line['extension'].'&gt;' : '') . $line['external'];
			
			echo '<br />';
		}
		?>
	</td>
	<td>
		<?php echo $device['lastconfig']?><br />
	</td>
	<td>
		<?php echo $device['lastip']?>
	</td>
	<td><span class="text-nowrap">
		<a href="config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=<?php echo $device['id']?>" title="Click to edit phone"><img src="images/edit.png" style="cursor:pointer; float:none;" alt="edit" /></a>
		<a href="config.php?type=setup&display=polycomphones&polycomphones_form=phones_directory&edit=<?php echo $device['mac']?>" title="Click to edit directory"><img src="images/user_edit.png" style="cursor:pointer; float:none;" alt="edit" /></a>
		<img src="images/trash.png" style="cursor:pointer; float:none;" alt="remove" title="Click to delete phone" onclick="if(confirm('Are you sure you want to delete phone \'<?php echo $device['mac']?>\'?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&delete=<?php echo $device['id']?>'" />
	</span></td>
<?php
}
?>
</tbody>
</table>