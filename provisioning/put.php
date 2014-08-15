<?php

$bootstrap_settings['freepbx_auth'] = false;
$bootstrap_settings['skip_astman'] = true;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

$network = polycomphones_get_networks_ip($_SERVER['REMOTE_ADDR']);
polycomphones_check_network($network);

if($network['settings']['prov_uploads'] != '1')
{
	header('HTTP/1.0 405 Method Not Allowed');
	polycomphones_send_error('405 Method Not Allowed', 'The requested method PUT is not allowed for the URL.');
}

$file = str_replace('/polycom/', '', $_SERVER['REQUEST_URI']);

$putdata = fopen("php://input", "r");
$fp = fopen($file, "w");

while ($data = fread($putdata, 1024))
  fwrite($fp, $data);

fclose($fp);
fclose($putdata);

?>
