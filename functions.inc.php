<?php 
/* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

function polycomphones_configpageinit($pagename)
{
	global $currentcomponent;
	
	if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'devices' && isset($_REQUEST['extdisplay'])) 
	{			
		$currentcomponent->addguifunc('polycomphones_configpageload', 8);
	}
}

function polycomphones_configpageload($pagename) 
{
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

function polycomphones_get_config($engine) 
{
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

function polycomphones_array_escape($values)
{
	global $db;
	
	if($values == null)
		return array();
		
	if(!is_array($values))
		$values = array($values);

	$escaped = array();
	foreach($values as $value)
		$escaped[] = $db->escapeSimple($value);

	return $escaped;
}

function polycomphones_checkconfig($id = null)
{
	global $db, $astman;

	$id = polycomphones_array_escape($id);

	$results = sql("SELECT MIN(deviceid) AS deviceid FROM `polycom_device_lines`
		WHERE deviceid IS NOT NULL " . (count($id) > 0 ? "AND id IN ('".implode("','", $id)."')" : "") . "
		GROUP BY id",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
		$astman->send_request('Command', array('Command' => 'sip notify polycom-check-cfg '.$result['deviceid']));
}

function polycomphones_push_checkconfig($id = null)
{
	global $db;
	
	$id = polycomphones_array_escape($id);
	
	$results = sql("SELECT id, lastip FROM polycom_devices
		" . (count($id) > 0 ? "WHERE id IN ('".implode("','", $id)."')" : ""),'getAll',DB_FETCHMODE_ASSOC);

	$failed = array();
	foreach($results as $result)
	{
		if(!polycomphones_push($result['lastip'], '<PolycomIPPhone><Data priority="Critical">Action:UpdateConfig</Data></PolycomIPPhone>'))
			$failed[] = $result['id'];
	}
	
	return count($failed) > 0 ? $failed : true;
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

function polycomphones_clear_overrides($mac = null)
{
	global $db, $amp_conf;
	
	$path = $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/overrides/';
	
	$contents =
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<PHONE_CONFIG>
        <OVERRIDES
        />
</PHONE_CONFIG>';
	
	$results = sql("SELECT mac FROM polycom_devices
		" . ($mac != null ? "WHERE mac = '". $db->escapeSimple($mac) . "'" : ""),'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
	{
		file_put_contents($path . $result['mac'] . '-phone.cfg', $contents);
		file_put_contents($path . $result['mac'] . '-web.cfg', $contents);
	}
}

function polycomphones_lookup_mac($mac)
{
	global $db;
	
	return sql("SELECT id FROM polycom_devices WHERE mac = '" . $db->escapeSimple($mac) . "'",'getOne');
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
	global $db, $amp_conf;
	
	$mac = sql("SELECT mac FROM polycom_devices WHERE id = '" . $db->escapeSimple($id) . "'",'getOne');
	
	sql("DELETE FROM polycom_devices WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_settings WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_lines WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_line_settings WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_device_attendants WHERE id = '".$db->escapeSimple($id)."'");

	if(!empty($mac))
	{
		$path = $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/';
	
		foreach(array('logs', 'overrides', 'contacts') as $folder)
			foreach (glob($path . $folder . '/' . $mac . "*") as $filename)
				unlink($filename);
	}
}

function polycomphones_get_phones_edit($id) 
{
	global $db;
	
	$device = sql("SELECT name, mac FROM polycom_devices
		WHERE id = \"{$db->escapeSimple($id)}\"",'getRow',DB_FETCHMODE_ASSOC);
	
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
	
	$attendants = sql("SELECT attendantid, keyword, value, label, type FROM polycom_device_attendants
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

	$create = empty($id);
	
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

		if(count($entries) > 0)
			sql("INSERT INTO polycom_device_line_settings (id, lineid, keyword, value) 
				VALUES (" . implode('),(', $entries) . ")");
	}
	
	sql("DELETE FROM polycom_device_attendants WHERE id = '".$db->escapeSimple($id)."'");
	
	foreach($device['attendants'] as $attendantid => $attendant)
		sql("INSERT INTO polycom_device_attendants (id, attendantid, keyword, value, label, type) 
			VALUES ('".$db->escapeSimple($id)."','".$db->escapeSimple($attendantid)."','".
				$db->escapeSimple($attendant['keyword'])."','".$db->escapeSimple($attendant['value'])."','".
				$db->escapeSimple($attendant['label'])."','".$db->escapeSimple($attendant['type'])."')");
	
	$entries = array();
	foreach ($device['settings'] as $key => $val)
		$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	if(count($entries) > 0)
		sql("REPLACE INTO polycom_device_settings (id, keyword, value) 
			VALUES (" . implode('),(', $entries) . ")");
			
	if($create)
	{
		polycomphones_clear_overrides($device['mac']);
		polycomphones_save_phones_directory($device['mac'], array());
	}
}

function polycomphones_get_phones_directory($mac)
{
	global $amp_conf;
	
	$file = $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/contacts/'.$mac.'-directory.xml';
	
	$directory = array();
	
	if(!file_exists($file))
		return $directory;
		
	if(!$xml = simplexml_load_file($file))
		return $directory;
	
	$fields = array(
		'fn',
		'ln',
		'ct',
		'sd',
		'rt',
		'bw',
	);
	
	foreach($xml->item_list->children() as $child)
	{
		$contact = array();

		foreach ($fields as $field)
			$contact[$field] = (string)$child->$field;
			
		$directory[] = $contact;
	}
	
	return $directory;
}

function polycomphones_save_phones_directory($mac, $directory)
{
	global $amp_conf;
	
	$file = $amp_conf['AMPWEBROOT'] . '/admin/modules/_polycom_software/contacts/'.$mac.'-directory.xml';
	
	$xml = new SimpleXMLElement(
'<?xml version="1.0" standalone="yes"?>
<directory>
  <item_list>
  </item_list>
</directory>');

	$fields = array(
		'fn',
		'ln',
		'ct',
		'sd',
		'rt',
		'bw',
	);

	foreach($directory as $contact)
	{
		$child = $xml->item_list->addChild(item);
		
		foreach($fields as $field)
			$child->addChild($field, $contact[$field]);
	}
	
	$xml->asXML($file);
}

function polycomphones_get_externallines_list() 
{
	global $db;
	
	$results = sql("SELECT id, name FROM polycom_externallines ORDER BY name",'getAll',DB_FETCHMODE_ASSOC);
		
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
	
	$line = sql("SELECT name FROM polycom_externallines
		WHERE id = \"{$db->escapeSimple($id)}\"",'getRow',DB_FETCHMODE_ASSOC);
	
	$settings = sql("SELECT keyword, value FROM polycom_externalline_settings
		WHERE id = \"{$db->escapeSimple($id)}\"",'getAll',DB_FETCHMODE_ASSOC);

	foreach($settings as $setting)
		$line['settings'][$setting['keyword']]=$setting['value'];
		
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

	$entries = array();
	foreach ($line['settings'] as $key => $val)
		$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	if(count($entries) > 0)
		sql("REPLACE INTO polycom_externalline_settings (id, keyword, value) 
			VALUES (" . implode('),(', $entries) . ")");
}

function polycomphones_get_alertinfo_list() 
{
	global $db;
	
	$results = sql("SELECT id, name, callwait, micmute, ringer, type, alertinfo 
		FROM polycom_alertinfo",'getAll',DB_FETCHMODE_ASSOC);
		
	return $results;
}

function polycomphones_get_alertinfo_edit($id)
{
	global $db;
	
	$alert = sql("SELECT id, name, callwait, micmute, ringer, type, alertinfo FROM polycom_alertinfo
		WHERE id = \"{$db->escapeSimple($id)}\"",'getRow',DB_FETCHMODE_ASSOC);

	return $alert;
}

function polycomphones_save_alertinfo_edit($id, $alert)
{
	global $db;
	
	sql("UPDATE polycom_alertinfo SET 
		name = '".$db->escapeSimple($alert['name'])."',
		callwait = '".$db->escapeSimple($alert['callwait'])."',
		micmute = '".$db->escapeSimple($alert['micmute'])."',
		ringer = '".$db->escapeSimple($alert['ringer'])."',
		type = '".$db->escapeSimple($alert['type'])."',
		alertinfo = '".$db->escapeSimple($alert['alertinfo'])."'
	WHERE id = '".$db->escapeSimple($id)."'");
}

function polycomphones_get_networks_list() 
{
	global $db;
	
	$results = sql("SELECT id, name, cidr FROM polycom_networks ORDER BY cidr",'getAll',DB_FETCHMODE_ASSOC);
		
	return $results;
}

function polycomphones_delete_networks_list($id)
{
	global $db;
	
	sql("DELETE FROM polycom_networks WHERE id = '".$db->escapeSimple($id)."'");
	sql("DELETE FROM polycom_network_settings WHERE id = '".$db->escapeSimple($id)."'");
}

function polycomphones_get_networks_edit($id) 
{
	global $db;
	
	$network = sql("SELECT name, cidr FROM polycom_networks
		WHERE id = \"{$db->escapeSimple($id)}\"",'getRow',DB_FETCHMODE_ASSOC);
	
	$settings = sql("SELECT keyword, value FROM polycom_network_settings
		WHERE id = \"{$db->escapeSimple($id)}\"",'getAll',DB_FETCHMODE_ASSOC);

	foreach($settings as $setting)
		$network['settings'][$setting['keyword']]=$setting['value'];
		
	return $network;
}

function polycomphones_save_networks_edit($id, $network)
{
	global $db;
	
	if(empty($id))
	{
		sql("INSERT INTO polycom_networks (name, cidr) 
			VALUES ('".$db->escapeSimple($network['name'])."', '".$db->escapeSimple($network['cidr'])."')");		
		$results = sql("SELECT LAST_INSERT_ID()",'getAll',DB_FETCHMODE_ASSOC);
		
		if(count($results) > 0)
			$id = $results[0]['LAST_INSERT_ID()'];
		else
			die_freepbx('Unable to determine SQL insert id');
	}
	else
		sql("UPDATE polycom_networks SET 
			name = '".$db->escapeSimple($network['name'])."',
			cidr = '".$db->escapeSimple($network['cidr'])."'
		WHERE id = '".$db->escapeSimple($id)."'");

	$entries = array();
	foreach ($network['settings'] as $key => $val)
		$entries[] = '\''.$db->escapeSimple($id).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';

	if(count($entries) > 0)
		sql("REPLACE INTO polycom_network_settings (id, keyword, value) 
			VALUES (" . implode('),(', $entries) . ")");
}

function polycomphones_cidr_ip_check ($ip, $cidr) 
{
	list ($net, $mask) = split ("/", $cidr);
	
	$ip_net = ip2long ($net);
	$ip_mask = ~((1 << (32 - $mask)) - 1);

	$ip_ip = ip2long ($ip);

	$ip_ip_net = $ip_ip & $ip_mask;

	return ($ip_ip_net == $ip_net);
}

function polycomphones_get_networks_ip($ip)
{
	global $db;
	
	$results = sql("SELECT id, cidr FROM polycom_networks ORDER BY cidr DESC",'getAll',DB_FETCHMODE_ASSOC);
	
	foreach($results as $result)
		if(polycomphones_cidr_ip_check($ip, $result['cidr']))
			return polycomphones_get_networks_edit($result['id']);
}

function polycomphones_check_network($network)
{
	if($network['settings']['prov_ssl'] == '1' && empty($_SERVER['HTTPS']))
	{
		header('HTTP/1.0 403 Forbidden');
		polycomphones_send_error('403 Forbidden', 'SSL is required to view this page.');
	}
	
	if(empty($network['settings']['prov_username']))
		return;

	if (!isset($_SERVER['PHP_AUTH_USER']) || !(
		$network['settings']['prov_username'] == $_SERVER['PHP_AUTH_USER'] &&
		$network['settings']['prov_password'] == $_SERVER['PHP_AUTH_PW']))
	{
		header('WWW-Authenticate: Basic realm="Authentication Required"');
		header('HTTP/1.0 401 Unauthorized');
		polycomphones_send_error('401 Unathorized', 'Authentication is required to view this page.');
	}
}

function polycomphones_send_error($title, $message)
{
	echo '
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>' . $title . '</title>
</head><body>
<h1>' . $title . '</h1>
<p>' . $message . '</p>
</body></html>';
	exit;
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
	
	$entries = array();
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
				(!empty($result['extension']) ? ': '.$result['name'].' <'.$result['extension'].'>' : '');
	
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
	
	// Time Conditions
	$timecondition_module = sql("SELECT id FROM modules WHERE modulename = 'timeconditions' AND enabled = '1'",'getOne');
	
	if($timecondition_module)
	{
		$results = sql("SELECT timeconditions_id, displayname FROM timeconditions 
			WHERE generate_hint = '1' ORDER BY timeconditions_id",'getAll',DB_FETCHMODE_ASSOC);

		$timecondition = array();
		foreach($results as $result)
			$timecondition['timecondition_' . $result['timeconditions_id']] = '<'.$result['timeconditions_id'].'> '.$result['displayname'];
		
		if(count($timecondition) > 0)
			$dropdown['Time Conditions'] = $timecondition;	
	}
	
	// Users
	$results = sql("SELECT extension, name FROM users ORDER BY extension",'getAll',DB_FETCHMODE_ASSOC);
	
	$users = array();
	foreach($results as $result)
		$users['user_' . $result['extension']] = '<'.$result['extension'].'> '.$result['name'];
		
	if(count($users) > 0)
		$dropdown['Users'] = $users;
	
	return $dropdown;
}

function polycomphones_dropdown_numbers($start, $end, $interval = 1, $default = false, $defaultvalue = 'Use Default')
{
	$dropdown = array();
	for($i=$start; $i<=$end; $i = $i+$interval)
		$dropdown[$i] = $i;
		
	return $default ? array(''=>$defaultvalue) + $dropdown : $dropdown;
}

function polycomphones_dropdown($id, $default = false, $defaultvalue = 'Use Default')
{
	$dropdowns['disabled_enabled'] = array(
		'0' => 'Disabled',
		'1' => 'Enabled',
	);
	
	$dropdowns['tcpIpApp_sntp_gmtOffset'] = array(
		'-28800' => 'GMT -8:00 Pacific Time',
		'-25200' => 'GMT -7:00 Mountain Time',
		'-21600' => 'GMT -6:00 Central Time',
		'-18000' => 'GMT -5:00 Eastern Time',
	);
	
	$dropdowns['ringType'] = array(
		'ringer1' => '1 Silent Ring',
		'ringer2' => '2 Low Trill',
		'ringer3' => '3 Low Double Trill',
		'ringer4' => '4 Medium Trill',
		'ringer5' => '5 Medium Double Trill',
		'ringer6' => '6 High Trill',
		'ringer7' => '7 High Double Trill',
		'ringer8' => '8 Highest Trill',
		'ringer9' => '9 Highest Double Trill',
		'ringer10' => '10 Beeble',
		'ringer11' => '11 Triplet',
		'ringer12' => '12 Ringback-style',
		'ringer13' => '13 Low Trill Precedence',
		'ringer14' => '14 Ring Splash',
	);
	
	$dropdowns['attendantType'] = array(
		'normal' => 'Normal',
		'automata' => 'Automata', 
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
	
	$dropdowns['up_analogHeadsetOption'] = array(
		'0' => 'Regular Mode',
		'1' => 'Jabra EHS',
		'2' => 'Plantronics EHS',
		'3' => 'Sennheiser EHS',
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
	
	$dropdowns['alert_callwait'] = array(
		'callWaiting' => 'Call Waiting',
		'callWaitingLong' => 'Call Waiting Long',
		'precedenceCallWaiting' => 'Precedence Call Waiting',
	);
	
	$dropdowns['alert_type'] = array(
		'ring' => 'Ring',
		'visual' => 'Visual',
		'answer' => 'Answer',
		'ring-answer' => 'Ring Answer',
	);
	
	return $default ? array(''=>$defaultvalue) + $dropdowns[$id] : $dropdowns[$id];
}

function polycomphones_getvalue($id, $device, $global)
{
	if(isset($device['settings'][$id]) && $device['settings'][$id] != '')
		return $device['settings'][$id];
	else
		return $global[$id];
}

function polycomphones_get_dialpad($num)
{
	$action = '';
	
	for($i=0; $i<strlen($num); $i++)
	{
		if($num[$i] == '*')
			$action .= '$FDialpadStar$';
		elseif($num[$i] == '#')
			$action .= '$FDialpadPound$';
		else
			$action .= '$FDialpad'.$num[$i].'$';
	}
	
	return $action;
}

?>
