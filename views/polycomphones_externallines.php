<h2>External Lines</h2>
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

<form name="polycomphones_externallines" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=externallines_list">
<input type="button" value="Add external line" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=externallines_edit&edit=0'" />

<p></p>

<table id="lines" class="tablesorter" width="50%">
<thead>
<tr>
	<th width="60%">Name</th>
	<th width="40%">Actions</th>
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
</form>