<h2>Alert Info</h2>
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

<form name="polycomphones_alertinfo" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=alertinfo_list">

<p></p>

<table id="lines" class="tablesorter" width="100%">
<thead>
<tr>
	<th width="22%">Class</th>
	<th width="22%">Name</th>
	<th width="22%">Ringer</th>
	<th width="22%">Alert Info</th>
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
</form>