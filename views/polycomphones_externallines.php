<h2>External Lines</h2>
<hr />

<div id="toolbar-polycom">
	<input type="button" value="Add external line" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=externallines_edit&edit=0'" />
</div>

<table data-toolbar="#toolbar-polycom" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped ext-list" id="table-polycom">
<thead>
<tr>
	<th data-sortable="true">Name</th>
	<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach ($lines as $line) {
?>
<tr>
	<td>
		<?php echo $line['name']?>
	</td>
	<td>
		<a href="config.php?type=setup&display=polycomphones&polycomphones_form=externallines_edit&edit=<?php echo $line['id']?>" title="Click to edit line"><img src="images/edit.png" style="cursor:pointer; float:none;" alt="edit" /></a>
		<img src="images/trash.png" style="cursor:pointer; float:none;" alt="remove" title="Click to delete line" onclick="if(confirm('Are you sure you want to delete this line?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=externallines_list&delete=<?php echo $line['id']?>'" />
	</td>
<?php
}
?>
</tbody>
</table>