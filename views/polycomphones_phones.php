<h2>Phones</h2>
<hr />
<script type="text/javascript" src="modules/polycomphones/assets/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="modules/polycomphones/assets/js/jquery.tablesorter.widgets.min.js"></script>

<script type="text/javascript">
$(function(){
  $("#phones").tablesorter({
    theme : 'jui',
    headerTemplate : '{content} {icon}',
    widgets : ['uitheme', 'zebra'],
    widgetOptions : {
      zebra   : ["even", "odd"],
    }
  });
});
</script>

<form name="polycomphones_phones" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=phones_list">
<input type="button" value="Add phone" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=0'" />
<input type="button" value="Update all" title="Send configuration update to all phones" onclick="if(confirm('Are you sure you want to update all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&pushcheck'" />
<input type="button" value="Reboot all" title="Send reboot to all phones" onclick="if(confirm('Are you sure you want to reboot all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&checkconfig'" />
<input type="button" value="Clear all overrides" title="Clear local setting overrides on all phones" onclick="if(confirm('Are you sure you want to clear local setting overrides on all phones?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&clearoverrides'" />
<p></p>

<table id="phones" class="tablesorter" width="100%">
<thead>
<tr>
	<th width="22%">Name</th>
	<th width="14%">MAC</th>
	<th width="32%">Lines</th>
	<th width="20%">Last Config</th>
	<th width="12%">Actions</th>
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
		<?php echo $device['mac']?>
	</td>
	<td>
		<?php
		foreach($device['lines'] as $line) {
		?>
		Line <?php echo $line['lineid'] ?>: <?php echo $line['id'] . (!empty($line['extension']) ? ': '.$line['name'].' &lt;'.$line['extension'].'&gt;' : '') . $line['external'] ?><br />
		<?php
		}
		?>
	</td>
	<td>
		<?php echo $device['lastconfig']?><br />
		<?php echo $device['lastip']?>
	</td>
	<td>
		<img src="images/edit.png" style="cursor:pointer; float:none;" alt="edit" title="Click to edit phone" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_edit&edit=<?php echo $device['id']?>'">
		<img src="images/user_edit.png" style="cursor:pointer; float:none;" alt="edit" title="Click to edit directory" onclick="location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_directory&edit=<?php echo $device['mac']?>'">
		<img src="images/trash.png" style="cursor:pointer; float:none;" alt="remove" title="Click to delete phone" onclick="if(confirm('Are you sure you want to delete phone \'<?php echo $device['mac']?>\'?')) location.href='config.php?type=setup&display=polycomphones&polycomphones_form=phones_list&delete=<?php echo $device['id']?>'">
	</td>
<?php
}
?>
</tbody>
</table>
</form>