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
('lineKeys', '1'),
('ringType', 'ringer2'),
('missedCallTracking', '1'),
('callBackMode', 'contact'),
('call_callWaiting_ring', 'beep'),
('call_hold_localReminder_enabled', '0'),
('feature_directedCallPickup_enabled', '0'),
('up_backlight_idleIntensity', '1'),
('up_backlight_onIntensity', '3'),
('nat_keepalive_interval', '0');";

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

foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

define("LOCAL_PATH", $amp_conf['AMPWEBROOT'] . '/admin/modules/polycomphones/');

out('Creating symlink to assets');
if (!symlink(LOCAL_PATH . "assets", $amp_conf['AMPWEBROOT'] . "/admin/assets/polycomphones")) {
	out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", web assets link not created!</strong>");
}

out('Creating symlink to web provisioner');
if (!symlink(LOCAL_PATH . "provisioning", $amp_conf['AMPWEBROOT'] . "/polycom")) {
	out("<strong>Your permissions are wrong on " . $amp_conf['AMPWEBROOT'] . ", web provisioning link not created!</strong>");
}

?>
