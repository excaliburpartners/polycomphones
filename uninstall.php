<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

sql('DROP TABLE IF EXISTS polycom_settings, polycom_devices, polycom_device_settings, 
polycom_device_lines, polycom_device_line_settings, polycom_device_attendants, 
polycom_externallines, polycom_externalline_settings');

out('Removing symlink to web provisioner');
if(is_link($amp_conf['AMPWEBROOT']."/polycom")) {
    unlink($amp_conf['AMPWEBROOT']."/polycom");
}

?>
