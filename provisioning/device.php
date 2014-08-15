<?php

if(!isset($_GET['mac']))
  die();

$config = <<<EOD
<?xml version="1.0" standalone="yes"?>
<!-- Default Master SIP Configuration File-->
<!-- For information on configuring Polycom VoIP phones please refer to the -->
<!-- Configuration File Management white paper available from: -->
<!-- http://www.polycom.com/common/documents/whitepapers/configuration_file_management_on_soundpoint_ip_phones.pdf -->
<APPLICATION APP_FILE_PATH="sip.ld" CONFIG_FILES="exten{$_GET['mac']}.cfg, freepbx.cfg" MISC_FILES="" LOG_FILE_DIRECTORY="logs" OVERRIDES_DIRECTORY="overrides" CONTACTS_DIRECTORY="contacts" LICENSE_DIRECTORY="" />
EOD;

header("Content-type: application/xml");
echo $config;

?>
