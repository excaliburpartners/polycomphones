<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_settings` (
  `keyword` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]="INSERT IGNORE INTO `polycom_settings` (`keyword`, `value`) VALUES
('apps_push_password', '" . substr(hash('sha512',rand()),0,12) . "'),
('digits', '4'),
('lineKeys', '1'),
('ringType', 'ringer2'),
('missedCallTracking', '1'),
('callBackMode', 'contact'),
('softkey_feature_basicCallManagement_redundant', '1'),
('up_useDirectoryNames', '1'),
('call_transfer_blindPreferred', '0'),
('call_callWaiting_ring', 'beep'),
('call_hold_localReminder_enabled', '0'),
('call_rejectBusyOnDnd', '1'),
('feature_directedCallPickup_enabled', '0'),
('powerSaving_enable', '0'),
('up_backlight_idleIntensity', '1'),
('up_backlight_onIntensity', '3'),
('nat_keepalive_interval', '0'),
('apps_ucdesktop_adminEnabled', '0');";

$sql[]="INSERT IGNORE INTO `polycom_settings` (`keyword`, `value`) VALUES
('powerSaving_idleTimeout_officeHours', '480'),
('powerSaving_idleTimeout_offHours', '1'),
('powerSaving_officeHours_startHour_monday', '7'),
('powerSaving_officeHours_startHour_tuesday', '7'),
('powerSaving_officeHours_startHour_wednesday', '7'),
('powerSaving_officeHours_startHour_thursday', '7'),
('powerSaving_officeHours_startHour_friday', '7'),
('powerSaving_officeHours_startHour_saturday', '7'),
('powerSaving_officeHours_startHour_sunday', '7'),
('powerSaving_officeHours_duration_monday', '12'),
('powerSaving_officeHours_duration_tuesday', '12'),
('powerSaving_officeHours_duration_wednesday', '12'),
('powerSaving_officeHours_duration_thursday', '12'),
('powerSaving_officeHours_duration_friday', '12'),
('powerSaving_officeHours_duration_saturday', '0'),
('powerSaving_officeHours_duration_sunday', '0');";

$sql[]="INSERT IGNORE INTO `polycom_settings` (`keyword`, `value`) VALUES
('feature_corporateDirectory_enabled', '0'),
('feature_exchangeCalendar_enabled', '0');";

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `mac` varchar(12) NOT NULL,
  `lastconfig` datetime NOT NULL,
  `lastip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_device_settings` (
  `id` int(11) NOT NULL,
  `keyword` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_device_lines` (
  `id` int(11) NOT NULL,
  `lineid` int(11) NOT NULL,
  `deviceid` int(11) NULL,
  `externalid` int(11) NULL,
  PRIMARY KEY (`id`,`lineid`),
  KEY `deviceid` (`deviceid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_device_line_settings` (
  `id` int(11) NOT NULL,
  `lineid` int(11) NOT NULL,
  `keyword` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`lineid`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_device_attendants` (
  `id` int(11) NOT NULL,
  `attendantid` int(11) NOT NULL,
  `keyword` varchar(30) NOT NULL,
  `value` varchar(30) NOT NULL,
  `label` varchar(30) NOT NULL,
  PRIMARY KEY (`id`,`attendantid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_externallines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_externalline_settings` (
  `id` int(11) NOT NULL,
  `keyword` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]='CREATE TABLE IF NOT EXISTS `polycom_alertinfo` (
  `id` varchar(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `callwait` varchar(25) NOT NULL,
  `micmute` tinyint(1) NOT NULL,
  `ringer` varchar(10) NOT NULL,
  `type` varchar(15) NOT NULL,
  `alertinfo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[]="INSERT IGNORE INTO `polycom_alertinfo` (`id`, `name`, `callwait`, `micmute`, `ringer`, `type`, `alertinfo`) VALUES
('default', 'Default', 'callWaiting', '0', 'ringer2', 'ring', ''),
('visual', 'Visual', 'callWaiting', '0', 'ringer1', 'visual', ''),
('answerMute', 'Answer Mute', 'callWaiting', '1', 'ringer2', 'answer', ''),
('autoAnswer', 'Auto Answer', 'callWaiting', '0', 'ringer2', 'answer', ''),
('ringAnswerMute', 'Ring Auto Mute', 'callWaiting', '1', 'ringer2', 'ring-answer', ''),
('ringAutoAnswer', 'Ring Auto Answer', 'callWaiting', '0', 'ringer2', 'ring-answer', 'Ring Answer'),
('internal', 'Internal', 'callWaiting', '0', 'ringer2', 'ring', ''),
('external', 'External', 'callWaiting', '0', 'ringer2', 'ring', ''),
('emergency', 'Emergency', 'callWaiting', '0', 'ringer2', 'ring', ''),
('precedence', 'Precedence', 'precedenceCallWaiting', '0', 'ringer13', 'ring', ''),
('splash', 'Default', 'callWaiting', '0', 'ringer14', 'ring', ''),
('custom1', 'Custom 1', 'callWaitingLong', '0', 'ringer5', 'ring', ''),
('custom2', 'Custom 2', 'callWaitingLong', '0', 'ringer7', 'ring', ''),
('custom3', 'Custom 3', 'callWaitingLong', '0', 'ringer9', 'ring', ''),
('custom4', 'Custom 4', 'callWaitingLong', '0', 'ringer11', 'ring', ''),
('custom5', 'Custom 5', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom6', 'Custom 6', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom7', 'Custom 7', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom8', 'Custom 8', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom9', 'Custom 9', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom10', 'Custom 10', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom11', 'Custom 11', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom12', 'Custom 12', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom13', 'Custom 13', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom14', 'Custom 14', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom15', 'Custom 15', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom16', 'Custom 16', 'callWaiting', '0', 'ringer2', 'ring', ''),
('custom17', 'Custom 17', 'callWaiting', '0', 'ringer2', 'ring', '');";

foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

define("LOCAL_PATH", $amp_conf['AMPWEBROOT'] . '/admin/modules/polycomphones/');
define("SOFTWARE_PATH", $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/');
define("PROVISIONING_PATH", $amp_conf['AMPWEBROOT'] . '/polycom');

// Link module assets to FreePBX assets folder
if(!is_link($amp_conf['AMPWEBROOT'] . "/admin/assets/polycomphones"))
{
	out('Creating symlink to assets');
	if (!symlink(LOCAL_PATH . "assets", $amp_conf['AMPWEBROOT'] . "/admin/assets/polycomphones")) {
		out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", web assets link not created!</strong>");
	}
}

// Create directory for phone software
if(!file_exists(SOFTWARE_PATH)) 
{
    out("Creating phone software directory");
	if(!mkdir(SOFTWARE_PATH, 0775)) {
		out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", phone software directory not created!</strong>");
	}
	if(!mkdir(SOFTWARE_PATH."logs", 0775)) {
		out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", phone software directory not created!</strong>");
	}
	if(!mkdir(SOFTWARE_PATH."contacts", 0775)) {
		out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", phone software directory not created!</strong>");
	}
}

// Remove link from previous module version
if(is_link(PROVISIONING_PATH))
{
	if(readlink(PROVISIONING_PATH) != SOFTWARE_PATH) {
		out("Removing old symlink to web provisioner");
		if(!unlink(PROVISIONING_PATH)) {
			out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", unable to remove previous web provisioning link!</strong>");
		}
	}
}

// Remove all links for phone software folder
foreach(scandir(SOFTWARE_PATH) as $item)
{
	if(is_file(SOFTWARE_PATH . $item) && is_link(SOFTWARE_PATH . $item)) {
		if(!unlink(SOFTWARE_PATH . $item)) {
			out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", unable to remove web provisioning file link!</strong>");
		}
	}
}

// Link provisioning files to software folder
foreach(scandir(LOCAL_PATH . "provisioning/") as $item) 
{
	if(is_file(LOCAL_PATH . "provisioning/" . $item)) {
		if (!symlink(LOCAL_PATH . "provisioning/" . $item, SOFTWARE_PATH . $item)) {
			out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", web provisioning file link not created!</strong>");
		}
	}
}

// Link software folder to provisioning path
if(!is_link(PROVISIONING_PATH))
{
	out('Creating symlink to web provisioner');
	if (!symlink(SOFTWARE_PATH, PROVISIONING_PATH)) {
		out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", web provisioning link not created!</strong>");
	}
}

?>
