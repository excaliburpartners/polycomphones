<?php

if(!isset($_GET['mac']))
  die();

$bootstrap_settings['freepbx_auth'] = false;
$bootstrap_settings['skip_astman'] = true;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

$xml = new SimpleXMLElement(
'<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<!-- Per-phone Configuration File -->
<polycomConfig xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="polycomConfig.xsd">
  <reg>
  </reg>
  <attendant>
  </attendant>
  <apps>
	<push apps.push.messageType="5" apps.push.username="Polycom">
	</push>
  </apps>
  <call>
    <missedCallTracking>
    </missedCallTracking>
	<callWaiting>
	</callWaiting>	    
	<hold>
      <localReminder>
      </localReminder>
    </hold>
  </call>
   <efk efk.version="2">
    <efklist
      efk.efklist.1.mname="xfervm"
      efk.efklist.1.status="1"
      efk.efklist.1.label="Transfer to Voicemail">
    </efklist>
    <efkprompt
      efk.efkprompt.1.status="1"
      efk.efkprompt.1.label="Extension: "
      efk.efkprompt.1.userfeedback="visible"
      efk.efkprompt.1.type="numeric">
    </efkprompt>  
  </efk>
  <feature>
    <directedCallPickup>
    </directedCallPickup>
  </feature>
  <msg>
    <mwi>
    </mwi>
  </msg>
  <nat>
    <keepalive>
    </keepalive>
  </nat>
  <softkey 
    softkey.1.label="Xfer VM"
    softkey.1.action="!xfervm"
    softkey.1.enable="1" softkey.1.use.active="1" softkey.1.use.hold="1" softkey.1.precede="0"
    softkey.2.label="Park Call"
    softkey.2.use.active="1" softkey.2.use.hold="1" softkey.2.precede="0"
	softkey.3.label="" 
	softkey.3.use.idle="1" softkey.3.insert="2">
  </softkey>  
  <tcpIpApp>
    <sntp>
    </sntp>
  </tcpIpApp>
  <up>
    <backlight>
    </backlight>
  </up>
  <voIpProt>
  </voIpProt>
</polycomConfig>');

$id = sql("
	SELECT id FROM polycom_devices
	WHERE mac = '" . $db->escapeSimple($_GET['mac']) . "'",'getOne');

$polycom_request = strpos($_SERVER['HTTP_USER_AGENT'], 'Polycom') !== false;

if($id == null && !$polycom_request)
	die();

if($polycom_request)
{
	if($id == null)
	{
		$matches = array();
		preg_match('/FileTransport Polycom([^\/.]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
		
		sql("INSERT INTO polycom_devices (name, mac, lastconfig, lastip) 
			VALUES ('" . $db->escapeSimple($matches[1]) . "','" . $db->escapeSimple($_GET['mac']) . 
			"',NOW(),'" . $db->escapeSimple($_SERVER['REMOTE_ADDR']) . "')");
			
		$id = sql("SELECT LAST_INSERT_ID()",'getOne');
	}
	else
		sql("UPDATE polycom_devices SET lastconfig = NOW(), lastip = '" . $db->escapeSimple($_SERVER['REMOTE_ADDR']) . "'
			WHERE id = '" . $db->escapeSimple($id) . "'");
}

$device = polycomphones_get_phones_edit($id);
$general = polycomphones_get_general_edit();
$exchange_module = sql("SELECT id FROM modules WHERE modulename = 'exchangeum' AND enabled = '1'",'getOne');
$parking_module = sql("SELECT id FROM modules WHERE modulename = 'parking' AND enabled = '1'",'getOne');

// Lines
$primary = '';

$i=1;
foreach($device['lines'] as $line)
{
	if($line['deviceid'] != null)
	{
		$details = sql('
		  SELECT d.id, d.user, u.name,
		    ssecret.data AS pass, stransport.data AS transport
		  FROM devices AS d
		  INNER JOIN users AS u
			ON d.user = u.extension
		  INNER JOIN sip AS ssecret
			ON d.id = ssecret.id AND ssecret.keyword = "secret"
		  INNER JOIN sip AS stransport
			ON d.id = stransport.id AND stransport.keyword = "transport"
		  WHERE d.id = "' . $db->escapeSimple($line['deviceid']) . '"','getRow',DB_FETCHMODE_ASSOC);
		
		$transports = explode(',', $details['transport']);		
		
		if($i==1)
			$primary = $details['id'];
		
		$xml->reg->addAttribute("reg.$i.displayName", $details['name']);
		$xml->reg->addAttribute("reg.$i.address", $details['id']);
		$xml->reg->addAttribute("reg.$i.label", $details['user']);
		$xml->reg->addAttribute("reg.$i.auth.userId", $details['id']);
		$xml->reg->addAttribute("reg.$i.auth.password", $details['pass']);	
		$xml->reg->addAttribute("reg.$i.server.1.address", $general['address']);
		$xml->reg->addAttribute("reg.$i.server.1.port", $general['port']);
		$xml->reg->addAttribute("reg.$i.server.1.transport", strtoupper($transports[0]) . 'Only');
		$xml->reg->addAttribute("reg.$i.lineKeys", getvalue('lineKeys', $line, $general));
		$xml->reg->addAttribute("reg.$i.ringType", getvalue('ringType', $line, $general));
		$xml->call->missedCallTracking->addAttribute("call.missedCallTracking.$i.enabled", getvalue('missedCallTracking', $line, $general));	
		$xml->msg->mwi->addAttribute("msg.mwi.$i.subscribe", $details['id']);
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBackMode", getvalue('callBackMode', $line, $general));
		
		$exchangevm = null;
		
		if($exchange_module)
			$exchangevm = sql("SELECT user FROM exchangeum_users 
				WHERE user = '" . $db->escapeSimple($details['user']) . "'
					AND umenabled = 'true'", 'getOne');		
		
		$fcc = new featurecode($exchangevm != null ? 'exchangeum' : 'voicemail', 'myvoicemail');
		$code = $fcc->getCodeActive();
		unset($fcc);
				
		if(!empty($code))
			$xml->msg->mwi->addAttribute("msg.mwi.$i.callBack", $code);
		
	}
	elseif($line['externalid'] != null)
	{
		$details = polycomphones_get_externallines_edit($line['externalid']);
		$xml->reg->addAttribute("reg.$i.displayName", $details['name']);
		$xml->reg->addAttribute("reg.$i.address", $details['settings']['user']);
		$xml->reg->addAttribute("reg.$i.label", $details['settings']['label']);
		$xml->reg->addAttribute("reg.$i.auth.userId", $details['settings']['user']);
		$xml->reg->addAttribute("reg.$i.auth.password", $details['settings']['secret']);
		$xml->reg->addAttribute("reg.$i.server.1.address", $details['settings']['address']);
		$xml->reg->addAttribute("reg.$i.server.1.port", $details['settings']['port']);
		$xml->reg->addAttribute("reg.$i.server.1.transport", $details['settings']['transport']);
		$xml->reg->addAttribute("reg.$i.server.1.register", $details['settings']['register']);
		$xml->reg->addAttribute("reg.$i.lineKeys", getvalue('lineKeys', $line, $general));
		$xml->reg->addAttribute("reg.$i.ringType", getvalue('ringType', $line, $general));
		$xml->call->missedCallTracking->addAttribute("call.missedCallTracking.$i.enabled", getvalue('missedCallTracking', $line, $general));	
		$xml->msg->mwi->addAttribute("msg.mwi.$i.subscribe", $details['settings']['user']);
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBackMode", getvalue('callBackMode', $line, $general));
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBack", $details['settings']['mwicallback']);
	}
	else
		continue;
	
	if($i==1)
	{
		srand($row['id']);
		$xml->voIpProt->addAttribute("voIpProt.SIP.local.port", rand(1024, 65535));
	}

	$i++;
}

// Attendant Console
$i=1;
foreach($device['attendants'] as $attendant)
{
	if($attendant['keyword'] == 'callforward')
	{
		$fcc = new featurecode('callforward', 'cf_toggle');
		$code = $fcc->getCodeActive();
		unset($fcc);
	
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", $code . $primary);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 'Call Forward');
	}
	elseif($attendant['keyword'] == 'donotdisturb')
	{
		$fcc = new featurecode('donotdisturb', 'dnd_toggle');
		$code = $fcc->getCodeActive();
		unset($fcc);
	
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", $code . $primary);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 'DND');
	}
	elseif($attendant['keyword'] == 'followme')
	{
		$fcc = new featurecode('findmefollow', 'fmf_toggle');
		$code = $fcc->getCodeActive();
		unset($fcc);
	
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", $code . $primary);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 'Follow Me');
	}
	elseif($attendant['keyword'] == 'user')
	{
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$general['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : $attendant['value']);
	}
	elseif($attendant['keyword'] == 'parking')
	{
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$general['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : 'Park '.$attendant['value']);
	}
	elseif($attendant['keyword'] == 'conference')
	{
		$confname = sql("SELECT description FROM meetme 
			WHERE exten = '" . $db->escapeSimple($attendant['value']) . "'",'getOne');
	
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$general['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : $confname);
	}
	elseif($attendant['keyword'] == 'callflow')
	{	
		$fcc = new featurecode('daynight', 'toggle-mode-' . $attendant['value']);
		$code = $fcc->getCodeActive();
		unset($fcc);
			
		$flowname = sql("SELECT dest FROM daynight 
			WHERE dmode = 'fc_description' 
			AND ext = '" . $db->escapeSimple($attendant['value']) . "'",'getOne');

		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.'@'.$general['address']);	
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : ($flowname ? $flowname : $code));
	}
		
	$i++;
}

// General Settings
$xml->apps->push->addAttribute("apps.push.password", $general['apps_push_password']);

if(!empty($general['tcpIpApp_sntp_address']))
	$xml->tcpIpApp->sntp->addAttribute("tcpIpApp.sntp.address", $general['tcpIpApp_sntp_address']);
	
if(!empty($general['tcpIpApp_sntp_gmtOffset']))
	$xml->tcpIpApp->sntp->addAttribute("tcpIpApp.sntp.gmtOffset", $general['tcpIpApp_sntp_gmtOffset']);

$xml->call->callWaiting->addAttribute("call.callWaiting.ring", getvalue('call_callWaiting_ring', $device, $general));
$xml->call->hold->localReminder->addAttribute("call.hold.localReminder.enabled", getvalue('call_hold_localReminder_enabled', $device, $general));
$xml->feature->directedCallPickup->addAttribute("feature.directedCallPickup.enabled", getvalue('feature_directedCallPickup_enabled', $device, $general));
$xml->up->backlight->addAttribute("up.backlight.idleIntensity", getvalue('up_backlight_idleIntensity', $device, $general));
$xml->up->backlight->addAttribute("up.backlight.onIntensity", getvalue('up_backlight_onIntensity', $device, $general));
$xml->nat->keepalive->addAttribute("nat.keepalive.interval", getvalue('nat_keepalive_interval', $device, $general));

// Xfer VM Button
$fcc = new featurecode('voicemail', 'directdialvoicemail');
$code = $fcc->getCodeActive();
unset($fcc);
	
$xml->efk->ekflist->addAttribute("efk.efklist.1.action.string", $code.'$P1N4$$Trefer$');

// Park Call Button
if($parking_module)
{
	$parkext = sql("SELECT parkext FROM parkplus ORDER BY id LIMIT 1",'getOne');
	
	$xml->softkey->addAttribute("softkey.2.action", $parkext.'$Trefer$');
	$xml->softkey->addAttribute("softkey.2.enable", '1');
}
else
	$xml->softkey->addAttribute("softkey.2.enable", '0');

// VVX DND Button
if(strpos($_SERVER['HTTP_USER_AGENT'], 'PolycomVVX') !== false)
	$xml->softkey->addAttribute("softkey.3.enable", '1');
else
	$xml->softkey->addAttribute("softkey.3.enable", '0');

function getvalue($id, $device, $global)
{
	if(!empty($device['settings'][$id]))
		return $device['settings'][$id];
	else
		return $global[$id];
}

header("Content-type: application/xml");
echo $xml->asXML();

?>
