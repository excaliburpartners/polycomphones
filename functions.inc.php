<?php 
/* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

function polycomphones_configpageinit($pagename) {
	global $currentcomponent;
	
	if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'devices' && isset($_REQUEST['extdisplay'])) 
	{			
		$currentcomponent->addguifunc('polycomphones_configpageload');
	}
}

function polycomphones_configpageload($pagename) {
	global $currentcomponent;
	global $db;

	$phones = sql("SELECT polycom_devices.id, polycom_devices.name, polycom_devices.mac FROM polycom_devices
		INNER JOIN polycom_device_lines ON polycom_devices.id = polycom_device_lines.id
		WHERE polycom_device_lines.deviceid = '".$db->escapeSimple($_REQUEST['extdisplay'])."'",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($phones as $phone)
	{
		$editURL = $_SERVER['PHP_SELF'].'?display=polycomphones&polycomphones_form=phones_edit&edit='.$phone['id'];
		$tlabel =  sprintf(_("Edit Polycom Phone: %s (%s)"),$phone['name'], $phone['mac']);
		$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/telephone_edit.png"/>&nbsp;'.$tlabel.'</span>';
		$currentcomponent->addguielem('_top', new gui_link('edit_polycomphone', $label, $editURL, true, false), 0);
	}
}

function polycomphones_get_config($engine) {
    global $db;
    global $core_conf;

    switch ($engine) {
        case "asterisk":
            if (isset($core_conf) && is_a($core_conf, "core_conf") && (method_exists($core_conf, 'addSipNotify'))) {
                $core_conf->addSipNotify('polycom-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
            }
			
            break;
    }
}

function polycomphones_checkconfig($id = '')
{
	global $db, $astman;
	
	$results = sql("SELECT deviceid FROM `polycom_device_lines`
		WHERE deviceid IS NOT NULL " . (!empty($id) ? "AND id = '".$db->escapeSimple($id)."'" : "") . "
		ORDER BY lineid LIMIT 1",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
		$astman->send_request('Command', array('Command' => 'sip notify polycom-check-cfg '.$result['deviceid']));
}

function polycomphones_push_checkconfig($id)
{
	global $db;
	
	$ip = sql("SELECT lastip FROM polycom_devices WHERE id = '".$db->escapeSimple($id)."'",'getOne');

	return polycomphones_push($ip, '<PolycomIPPhone><Data priority="Critical">Action:UpdateConfig</Data></PolycomIPPhone>');
}

function polycomphones_push($ip, $xml)
{
	$password = sql("SELECT value FROM polycom_settings WHERE keyword = 'apps_push_password'",'getOne');

	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, 'http://'.$ip.'/push');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERPWD, 'Polycom:' . $password);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-com-polycom-spipx'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	curl_exec($ch);
	
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	if ($status_code != 200)
		return false;	
	
	return true;
}

function polycomphones_multiple_check()
{
	global $db;
	
	$results = sql("SELECT deviceid FROM polycom_device_lines 
		GROUP BY deviceid HAVING COUNT(deviceid) > 1",'getAll',DB_FETCHMODE_ASSOC);
	
	$devices = array();
	foreach($results as $result)
		$devices[] = $result['deviceid'];
	
	$nt =& notifications::create($db);
	
	if(count($devices) > 0)
	{
		$text = sprintf(_("Device assigned to multiple lines in Polycom Phones"));
		$extext = sprintf(_("The devices(s) referenced: %s"), implode(', ', $devices));
		$nt->add_warning('polycomphones', 'multiple', $text, $extext, '', true, false);
	}
	else
		$nt->delete('polycomphones', 'multiple');
		
	unset($nt);
}

function polycomphones_get_phones_list() 
{
	global $db;
	
	$results = sql("SELECT id, name, mac, lastconfig, lastip
		FROM polycom_devices
		ORDER BY mac",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $key=>$result)
		$results[$key]['lines'] = sql("SELECT polycom_device_lines.lineid, devices.id, devices.description, 
				users.extension, users.name, polycom_externallines.name AS external
			FROM polycom_device_lines
			LEFT OUTER JOIN devices ON devices.id = polycom_device_lines.deviceid
			LEFT OUTER JOIN users ON devices.user = users.extension
			LEFT OUTER JOIN polycom_externallines ON polycom_externallines.id = polycom_device_lines.externalid
			WHERE polycom_device_lines.id = \"{$db->escapeSimple($result[id])}\"",'getAll',DB_FETCHMODE_ASSOC);
	
	return $results;
}

function polycomphones_delete_phones_list($id)
{
	global $db;
	
	sql("DELETE FROM polycom_devices WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_settings WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_lines WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_line_settings WHERE id = '".$db->escapeSimple($id)."'");
}

function polycomphones_get_phones_edit($id) 
{
	global $db;
	
	$results = sql("SELECT name, mac FROM polycom_devices
		WHERE id = \"{$db->escapeSimple($id)}\"",'getRow',DB_FETCHMODE_ASSOC);
	
	$device['name'] = $results['name'];
	$device['mac'] = $results['mac'];
	
	$lines = sql("SELECT lineid, deviceid, externalid FROM polycom_device_lines 
		WHERE id = \"{$db->escapeSimple($id)}\"
		ORDER BY lineid",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($lines as $line)
		$device['lines'][$line['lineid']] = $line;
	
	foreach($device['lines'] as $key=>$line)
	{
		$settings = sql("SELECT keyword, value FROM polycom_device_line_settings
			WHERE id = \"{$db->escapeSimple($id)}\" AND lineid = \"{$db->escapeSimple($key)}\"",'getAll',DB_FETCHMODE_ASSOC);

		foreach($settings as $setting)
			$device['lines'][$key]['settings'][$setting['keyword']]=$setting['value'];	
	}
	
	$attendants = sql("SELECT attendantid, keyword, value, label FROM polycom_device_attendants
		WHERE id = \"{$db->escapeSimple($id)}\"
		ORDER BY attendantid",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($attendants as $attendant)
		$device['attendants'][$attendant['attendantid']] = $attendant;

	$settings = sql("SELECT keyword, value FROM polycom_device_settings
		WHERE id = \"{$db->escapeSimple($id)}\"",'getAll',DB_FETCHMODE_ASSOC);

	foreach($settings as $setting)
		$device['settings'][$setting['keyword']]=$setting['value'];

	return $device;
}

function polycomphones_save_phones_edit($id, $device)
{
	global $db;

	if(empty($id))
	{
		sql("INSERT INTO polycom_devices (name, mac) 
			VALUES ('".$db->escapeSimple($device['name'])."','".$db->escapeSimple($device['mac'])."')");
			
		$id = sql("SELECT LAST_INSERT_ID()",'getOne');
	}
	else
		sql("UPDATE polycom_devices SET 
				name = '".$db->escapeSimple($device['name'])."',
				mac = '".$db->escapeSimple($device['mac'])."'
			WHERE id = '".$db->escapeSimple($id)."'");

	sql("DELETE FROM polycom_device_lines WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_line_settings WHERE id = '".$db->escapeSimple($id)."'");
	
	foreach($device['lines'] as $lineid => $line)
	{
		sql("INSERT INTO polycom_device_lines (id, lineid, deviceid, externalid) 
			VALUES ('".$db->escapeSimple($id)."','".$db->escapeSimple($lineid)."',".
				($line['deviceid'] != null ? "'".$db->escapeSimple($line['deviceid'])."'" : 'NULL') .",".
				($line['externalid'] != null ? "'".$db->escapeSimple($line['externalid'])."'" : 'NULL') .")");
		
		$entries = array();
		foreach ($line['settings'] as $key => $val)
			$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($lineid).'\',\''.
				$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

		sql("INSERT INTO polycom_device_line_settings (id, lineid, keyword, value) 
			VALUES (" . implode('),(', $entries) . ")");
	}
	
	sql("DELETE FROM polycom_device_attendants WHERE id = '".$db->escapeSimple($id)."'");
	
	foreach($device['attendants'] as $attendantid => $attendant)
		sql("INSERT INTO polycom_device_attendants (id, attendantid, keyword, value, label) 
			VALUES ('".$db->escapeSimple($id)."','".$db->escapeSimple($attendantid)."','".
				$db->escapeSimple($attendant['keyword'])."','".$db->escapeSimple($attendant['value'])."','".
				$db->escapeSimple($attendant['label'])."')");
	
	$entries = array();
	foreach ($device['settings'] as $key => $val)
		$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	sql("REPLACE INTO polycom_device_settings (id, keyword, value) 
		VALUES (" . implode('),(', $entries) . ")");
}

function polycomphones_get_externallines_list() 
{
	global $db;
	
	$results = sql("SELECT id, name FROM polycom_externallines",'getAll',DB_FETCHMODE_ASSOC);
		
	return $results;
}

function polycomphones_delete_externallines_list($id)
{
	global $db;
	
	sql("DELETE FROM polycom_externallines WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_externalline_settings WHERE id = '".$db->escapeSimple($id)."'");
}

function polycomphones_get_externallines_edit($id) 
{
	global $db;
	
	$results = sql("SELECT name FROM polycom_externallines
		WHERE id = \"{$db->escapeSimple($id)}\"",'getAll',DB_FETCHMODE_ASSOC);
	
	if(count($results) > 0)
	{
		$line['name'] = $results[0]['name'];
	
		$settings = sql("SELECT keyword, value FROM polycom_externalline_settings
			WHERE id = \"{$db->escapeSimple($id)}\"",'getAll',DB_FETCHMODE_ASSOC);

		foreach($settings as $setting)
			$line['settings'][$setting['keyword']]=$setting['value'];
	}
		
	return $line;
}

function polycomphones_save_externallines_edit($id, $line)
{
	global $db;
	
	if(empty($id))
	{
		sql("INSERT INTO polycom_externallines (name) VALUES ('".$db->escapeSimple($line['name'])."')");		
		$results = sql("SELECT LAST_INSERT_ID()",'getAll',DB_FETCHMODE_ASSOC);
		
		if(count($results) > 0)
			$id = $results[0]['LAST_INSERT_ID()'];
		else
			die_freepbx('Unable to determine SQL insert id');
	}
	else
		sql("UPDATE polycom_externallines SET 
			name = '".$db->escapeSimple($line['name'])."'
		WHERE id = '".$db->escapeSimple($id)."'");
	
	foreach ($line['settings'] as $key => $val)
		$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	sql("REPLACE INTO polycom_externalline_settings (id, keyword, value) 
		VALUES (" . implode('),(', $entries) . ")");
}

function polycomphones_get_general_edit() 
{
	global $db;
	
	$results = sql("SELECT keyword, value FROM polycom_settings",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
		$settings[$result['keyword']]=$result['value'];
	
	return $settings;
}

function polycomphones_save_general_edit($settings)
{
	global $db;
	
	foreach ($settings as $key => $val)
		$entries[] = '\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	sql("REPLACE INTO polycom_settings (keyword, value) 
		VALUES (" . implode('),(', $entries) . ")");
}

function polycomphones_dropdown_lines($id)
{
	global $db;
	
	$dropdown = array('' => '');
	
	$results = sql("SELECT DISTINCT deviceid FROM polycom_device_lines
		" . (!empty($id) ? "WHERE id <> '" . $db->escapeSimple($id) . "'" : ""),'getAll',DB_FETCHMODE_ASSOC);
	
	$assigned = array();
	foreach($results as $result)
		$assigned[] = $result['deviceid'];
	
	$results = sql("SELECT devices.id, devices.description, users.extension, users.name FROM devices 
		LEFT OUTER JOIN users on devices.user = users.extension
		WHERE tech = 'sip' ORDER BY devices.id",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
		if(!in_array($result['id'], $assigned))
			$lines['freepbx_' . $result['id']]=$result['id'] . 
				(!empty($result['extension']) ? ': '.$result['name'].' &lt;'.$result['extension'].'&gt;' : '');
	
	if(count($lines) > 0)
		$dropdown['FreePBX'] = $lines;
	
	$results = sql("SELECT id, name FROM polycom_externallines",'getAll',DB_FETCHMODE_ASSOC);	

	foreach($results as $result)
		$externallines['external_' . $result['id']]=$result['name'];
		
	if(count($externallines) > 0)
		$dropdown['External'] = $externallines;
	
	return $dropdown;
}

function polycomphones_dropdown_attendant()
{
	$dropdown = array('' => '');
	
	// My Features
	$myfeatures = array();
	
	if(sql("SELECT id FROM modules WHERE modulename = 'callforward' AND enabled = '1'",'getOne'))
		$myfeatures['callforward_1'] = 'Call Forward';
		
	if(sql("SELECT id FROM modules WHERE modulename = 'donotdisturb' AND enabled = '1'",'getOne'))
		$myfeatures['donotdisturb_1'] = 'DND';
	
	if(sql("SELECT id FROM modules WHERE modulename = 'findmefollow' AND enabled = '1'",'getOne'))
		$myfeatures['followme_1'] = 'Follow Me';
	
		if(count($myfeatures) > 0)
			$dropdown['Line 1 Features'] = $myfeatures;	
	
	// Call Flow
	$callflow_module = sql("SELECT id FROM modules WHERE modulename = 'daynight' AND enabled = '1'",'getOne');
	
	if($callflow_module)
	{
		$results = sql("SELECT ext, dest FROM daynight WHERE dmode = 'fc_description' ORDER BY ext",'getAll',DB_FETCHMODE_ASSOC);

		$callflow = array();
		foreach($results as $result)
			$callflow['callflow_' . $result['ext']] = '<'.$result['ext'].'> '.$result['dest'];
		
		if(count($callflow) > 0)
			$dropdown['Call Flow Control'] = $callflow;	
	}
	
	// Conferences
	$conference_module = sql("SELECT id FROM modules WHERE modulename = 'conferences' AND enabled = '1'",'getOne');
	
	if($conference_module)
	{
		$results = sql("SELECT exten, description FROM meetme ORDER BY exten",'getAll',DB_FETCHMODE_ASSOC);

		$conference = array();
		foreach($results as $result)
			$conference['conference_' . $result['exten']] = '<'.$result['exten'].'> '.$result['description'];
		
		if(count($callflow) > 0)
			$dropdown['Conferences'] = $conference;	
	}	
	
	// Parking
	$parking_module = sql("SELECT id FROM modules WHERE modulename = 'parking' AND enabled = '1'",'getOne');
	
	if($parking_module)
	{
		$results = sql("SELECT parkpos, numslots FROM parkplus ORDER BY id LIMIT 1",'getRow',DB_FETCHMODE_ASSOC);

		$parking = array();
		for($i=$results['parkpos']; $i<$results['parkpos']+$results['numslots']; $i++)
			$parking['parking_' . $i] = 'Park ' . $i;
		
		if(count($parking) > 0)
			$dropdown['Parking'] = $parking;	
	}
	
	// Users
	$results = sql("SELECT extension, name FROM users ORDER BY extension",'getAll',DB_FETCHMODE_ASSOC);
	
	$users = array();
	foreach($results as $result)
		$users['user_' . $result['extension']] = '&lt;'.$result['extension'].'&gt; '.$result['name'];
		
	if(count($users) > 0)
		$dropdown['Users'] = $users;
	
	return $dropdown;
}

function polycomphones_dropdown($id, $default = false, $defaultvalue = 'Use Default')
{
	$dropdowns['tcpIpApp_sntp_gmtOffset'] = array(
		'-28800' => 'GMT -8:00 Pacific Time',
		'-25200' => 'GMT -7:00 Mountain Time',
		'-21600' => 'GMT -6:00 Central Time',
		'-18000' => 'GMT -5:00 Eastern Time',
	);

	$dropdowns['lineKeys'] = array(
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
	);
	
	$dropdowns['ringType'] = array(
		'ringer1' => 'Silent Ring',
		'ringer2' => 'Low Trill',
		'ringer3' => 'Low Double Trill',
		'ringer4' => 'Medium Trill',
		'ringer5' => 'Medium Double Trill',
		'ringer6' => 'High Trill',
		'ringer7' => 'High Double Trill',
		'ringer8' => 'Highest Trill',
		'ringer9' => 'Highest Double Trill',
		'ringer10' => 'Beeble',
		'ringer11' => 'Triplet',
		'ringer12' => 'Ringback-style',
		'ringer13' => 'Low Trill Precedence',
		'ringer14' => 'Ring Splash',
	);
	
	$dropdowns['missedCallTracking'] = array(
		'0' => 'Disabled',
		'1' => 'Enabled',
	);
	
	$dropdowns['callBackMode'] = array(
		'disabled' => 'Disabled',
		'contact' => 'Contact',
	);
	
	$dropdowns['call_callWaiting_ring'] = array(
		'beep' => 'Beep',
		'ring' => 'Ring',
		'silent' => 'Silent',
	);
	
	$dropdowns['call_hold_localReminder_enabled'] = array(
		'0' => 'Disabled',
		'1' => 'Enabled',
	);
	
	$dropdowns['feature_directedCallPickup_enabled'] = array(
		'0' => 'Disabled',
		'1' => 'Enabled',
	);

	$dropdowns['up_backlight_idleIntensity'] = array(
		'0' => 'Off',
		'1' => 'Low',
		'2' => 'Medium',
		'3' => 'High',
	);
	
	$dropdowns['up_backlight_onIntensity'] = array(
		'0' => 'Off',
		'1' => 'Low',
		'2' => 'Medium',
		'3' => 'High',
	);
	
	$dropdowns['nat_keepalive_interval'] = array(
		'0' => 'Disabled',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'45' => '45',
		'60' => '60',
	);
	
	$dropdowns['transport'] = array(
		'UDPOnly' => 'UDP',
		'TCPOnly' => 'TCP',
	);
	
	$dropdowns['register'] = array(
		'1' => 'Yes',
		'0' => 'No',
	);
	
	return $default ? array(''=>$defaultvalue) + $dropdowns[$id] : $dropdowns[$id];
}

?>
