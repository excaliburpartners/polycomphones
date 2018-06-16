<h2>Networks</h2>
<hr />

<div id="toolbar-polycom">
	<input type="button" value="Add network" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=networks_edit&edit=0'" />
</div>

<table data-toolbar="#toolbar-polycom" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped ext-list" id="table-polycom">
<thead>
<tr>
	<th data-sortable="true">Name</th>
	<th data-sortable="true">Network CIDR</th>
	<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach ($networks as $network) {
?>
<tr>
	<td>
		<?php echo $network['name']?>
	</td>
	<td>
		<?php echo $network['cidr']?>
	</td>
	<td>
		<a href="config.php?type=setup&display=polycomphones&polycomphones_form=networks_edit&edit=<?php echo $network['id']?>" title="Click to edit line"><img src="images/edit.png" style="cursor:pointer; float:none;" alt="edit" /></a>
		<?php if($network['id'] != '-1') { ?>
		<img src="images/trash.png" style="cursor:pointer; float:none;" alt="remove" title="Click to delete line" onclick="if(confirm('Are you sure you want to delete this network?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=networks_list&delete=<?php echo $network['id']?>'" />
		<?php } ?>
	</td>
<?php
}
?>
</tbody>
</table>