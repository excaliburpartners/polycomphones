<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

sql('DROP TABLE IF EXISTS polycom_settings, polycom_networks, polycom_network_settings,
polycom_devices, polycom_device_settings, polycom_device_lines, polycom_device_line_settings, polycom_device_attendants, 
polycom_externallines, polycom_externalline_settings, polycom_alertinfo');

define("SOFTWARE_PATH", $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/');
define("PROVISIONING_PATH", $amp_conf['AMPWEBROOT'] . "/polycom");

out('Removing symlink to web provisioner');
if(is_link(PROVISIONING_PATH)) {
    unlink(PROVISIONING_PATH);
}

foreach(scandir(SOFTWARE_PATH) as $item)
{
	if(is_file(SOFTWARE_PATH . $item) && is_link(SOFTWARE_PATH . $item)) {
		unlink(SOFTWARE_PATH . $item);
	}
}

?>
