<h2>Alert Info</h2>
<hr />

<table data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped ext-list" id="table-polycom">
<thead>
<tr>
	<th data-sortable="true">Class</th>
	<th data-sortable="true">Name</th>
	<th data-sortable="true">Ringer</th>
	<th data-sortable="true">Alert Info</th>
	<th width="12%">Actions</th>
</tr>
</thead>
<tbody>
<?php
$ringtype = polycomphones_dropdown('ringType');

foreach ($alerts as $alert) {
?>
<tr>
	<td>
		<?php echo $alert['id']; ?>
	</td>
	<td>
		<?php echo $alert['name']; ?>
	</td>
	<td>
		<?php echo $ringtype[$alert['ringer']]; ?>
	</td>
	<td>
		<?php echo $alert['alertinfo']; ?>
	</td>
	<td>
		<a href="config.php?type=setup&display=polycomphones&polycomphones_form=alertinfo_edit&edit=<?php echo $alert['id']?>" title="Click to edit alert info"><img src="images/edit.png" style="cursor:pointer; float:none;" alt="edit" /></a>
	</td>
<?php
}
?>
</tbody>
</table>