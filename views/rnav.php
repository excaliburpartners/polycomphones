<?php
$show['Phones'] = '<li><a ' 
	. ($_REQUEST['polycomphones_form'] == 'phones_list' || $_REQUEST['polycomphones_form'] == 'phones_edit'
	? 'class="current ui-state-highlight" ' : '') 
	. 'href="config.php?type=setup&display=polycomphones&polycomphones_form=phones_list">' . _("Phones") . '</a></li>';			

$show['External Lines'] = '<li><a ' 
	. ($_REQUEST['polycomphones_form'] == 'externallines_list' || $_REQUEST['polycomphones_form'] == 'externallines_edit'
	? 'class="current ui-state-highlight" ' : '') 
	. 'href="config.php?type=setup&display=polycomphones&polycomphones_form=externallines_list">' . _("External Lines") . '</a></li>';

$show['General Settings'] = '<li><a ' 
	. ($_REQUEST['polycomphones_form'] == 'general_edit' ? 'class="current ui-state-highlight" ' : '') 
	. 'href="config.php?type=setup&display=polycomphones&polycomphones_form=general_edit">' . _("General Settings") . '</a></li>';

echo '
<div class="rnav"><ul>';
foreach ($show as $s) {
	echo $s;
}
echo '
</ul></div>';
?>
