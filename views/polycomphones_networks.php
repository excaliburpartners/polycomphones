<h2>Networks</h2>
<hr />
<script type="text/javascript" src="modules/polycomphones/assets/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="modules/polycomphones/assets/js/jquery.tablesorter.widgets.min.js"></script>

<script type="text/javascript">
$(function(){
  $("#lines").tablesorter({
    theme : 'jui',
    headerTemplate : '{content} {icon}',
    widgets : ['uitheme', 'zebra'],
    widgetOptions : {
      zebra   : ["even", "odd"],
    }
  });
});
</script>

<form name="polycomphones_networks" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=networks_list">
<input type="button" value="Add network" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=networks_edit&edit=0'" />

<p></p>

<table id="lines" class="tablesorter" width="60%">
<thead>
<tr>
	<th width="50%">Name</th>
	<th width="30%">Network CIDR</th>
	<th width="20%">Actions</th>
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
</form>