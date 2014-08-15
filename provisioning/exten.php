<?php

if(!isset($_GET['mac']) || preg_match('/^([a-f0-9]{12})$/', $_GET['mac']) != 1)
  die();

$bootstrap_settings['freepbx_auth'] = false;
$bootstrap_settings['skip_astman'] = true;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

$network = polycomphones_get_networks_ip($_SERVER['REMOTE_ADDR']);
polycomphones_check_network($network);

$xml = new SimpleXMLElement(
'<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<!-- Per-phone Configuration File -->
<polycomConfig xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="polycomConfig.xsd">
  <reg>
  </reg>
  <attendant>
    <behaviors>
      <display>
        <spontaneousCallAppearances>
        </spontaneousCallAppearances>
      </display>
    </behaviors>
  </attendant>
  <apps>
    <push apps.push.messageType="5" apps.push.username="Polycom">
    </push>
    <ucdesktop>
    </ucdesktop>
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
    <transfer>
    </transfer>
  </call>
  <dialplan>
    <dialplan.digitmap dialplan.digitmap.timeOut="3|3|3|3|3|3|3|3">
    </dialplan.digitmap>
  </dialplan> 
  <dir>
    <corp>
    </corp>
    <local>
    </local>
  </dir>
  <exchange>
    <server>
    </server>
  </exchange>
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
    <corporateDirectory>
    </corporateDirectory>
    <exchangeCalendar>
    </exchangeCalendar>
  </feature>
  <httpd>
    <cfg>
    </cfg>
  </httpd>
  <mb>
    <main>
    </main>
  </mb>
  <msg>
    <mwi>
    </mwi>
  </msg>
  <nat>
    <keepalive>
    </keepalive>
  </nat>
  <powerSaving>
    <idleTimeout>
    </idleTimeout>
    <officeHours>
      <startHour>
      </startHour>
      <duration>
      </duration>
    </officeHours>
  </powerSaving>
  <se>
    <pat>
      <misc>
        <messageWaiting>
         </messageWaiting>
      </misc>
    </pat>
    <rt>
    </rt>
  </se>
  <softkey 
    softkey.1.label="Xfer VM"
    softkey.1.action="!xfervm"
    softkey.1.use.active="1" softkey.1.use.hold="1" softkey.1.precede="0"
    softkey.2.label="Park Call"
    softkey.2.use.active="1" softkey.2.use.hold="1" softkey.2.precede="0"
    softkey.3.label="Record"
    softkey.3.use.active="1" softkey.3.use.hold="1" softkey.3.precede="0"
    softkey.4.label=""
    softkey.4.use.idle="1" softkey.4.insert="2">
    <feature>
      <basicCallManagement>
      </basicCallManagement>
    </feature>
  </softkey>  
  <tcpIpApp>
    <sntp>
    </sntp>
  </tcpIpApp>
  <up>
    <backlight>
    </backlight>
  </up>
  <voice>
    <codecPref>
    </codecPref>
  </voice>
  <voIpProt>
    <SIP>
      <alertInfo>
      </alertInfo>
    </SIP>
  </voIpProt>
</polycomConfig>');

$id = polycomphones_lookup_mac($_GET['mac']);

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
		
		polycomphones_clear_overrides($_GET['mac']);
		polycomphones_save_phones_directory($_GET['mac'], array());
	}
	else
		sql("UPDATE polycom_devices SET lastconfig = NOW(), lastip = '" . $db->escapeSimple($_SERVER['REMOTE_ADDR']) . "'
			WHERE id = '" . $db->escapeSimple($id) . "'");
}

$device = polycomphones_get_phones_edit($id);
$alerts = polycomphones_get_alertinfo_list();
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
		{
			$primary = $details['id'];
			
			srand($details['id']);
			$xml->voIpProt->addAttribute("voIpProt.SIP.local.port", rand(1024, 65535));
		}
		
		$xml->reg->addAttribute("reg.$i.displayName", $details['name']);
		$xml->reg->addAttribute("reg.$i.address", $details['id']);
		$xml->reg->addAttribute("reg.$i.label", $details['user']);
		$xml->reg->addAttribute("reg.$i.auth.userId", $details['id']);
		$xml->reg->addAttribute("reg.$i.auth.password", $details['pass']);	
		$xml->reg->addAttribute("reg.$i.server.1.address", $network['settings']['address']);
		$xml->reg->addAttribute("reg.$i.server.1.port", $network['settings']['port']);
		$xml->reg->addAttribute("reg.$i.server.1.transport", strtoupper($transports[0]) . 'Only');
		$xml->reg->addAttribute("reg.$i.lineKeys", polycomphones_getvalue('lineKeys', $line, $general));
		$xml->reg->addAttribute("reg.$i.ringType", polycomphones_getvalue('ringType', $line, $general));
		$xml->call->missedCallTracking->addAttribute("call.missedCallTracking.$i.enabled", polycomphones_getvalue('missedCallTracking', $line, $general));	
		$xml->msg->mwi->addAttribute("msg.mwi.$i.subscribe", $details['id']);
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBackMode", polycomphones_getvalue('callBackMode', $line, $general));
		
		$exchangevm = null;
		
		if($exchange_module)
			$exchangevm = sql("SELECT user FROM exchangeum_users 
				WHERE user = '" . $db->escapeSimple($details['user']) . "'
					AND umenabled = 'true'", 'getOne');		
		
		$fcc = new featurecode($exchangevm != null ? 'exchangeum' : 'voicemail', 'myvoicemail');
		$code = $fcc->getCodeActive();
		unset($fcc);
				
		if($code != '')
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
		$xml->reg->addAttribute("reg.$i.lineKeys", polycomphones_getvalue('lineKeys', $line, $general));
		$xml->reg->addAttribute("reg.$i.ringType", polycomphones_getvalue('ringType', $line, $general));
		$xml->call->missedCallTracking->addAttribute("call.missedCallTracking.$i.enabled", polycomphones_getvalue('missedCallTracking', $line, $general));	
		$xml->msg->mwi->addAttribute("msg.mwi.$i.subscribe", $details['settings']['user']);
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBackMode", polycomphones_getvalue('callBackMode', $line, $general));
		$xml->msg->mwi->addAttribute("msg.mwi.$i.callBack", $details['settings']['mwicallback']);
	}
	else
		continue;

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
	
		if($code != '' && $primary != '')
		{
			$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.$primary.'@'.$network['settings']['address']);
			$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
				!empty($attendant['label']) ? $attendant['label'] : 'Call Forward');
		}
	}
	elseif($attendant['keyword'] == 'donotdisturb')
	{
		$fcc = new featurecode('donotdisturb', 'dnd_toggle');
		$code = $fcc->getCodeActive();
		unset($fcc);
	
		if($code != '' && $primary != '')
		{
			$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.$primary.'@'.$network['settings']['address']);
			$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
				!empty($attendant['label']) ? $attendant['label'] : 'DND');
		}
	}
	elseif($attendant['keyword'] == 'followme')
	{
		$fcc = new featurecode('findmefollow', 'fmf_toggle');
		$code = $fcc->getCodeActive();
		unset($fcc);
	
		if($code != '' && $primary != '')
		{
			$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.$primary.'@'.$network['settings']['address']);
			$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
				!empty($attendant['label']) ? $attendant['label'] : 'Follow Me');
		}
	}
	elseif($attendant['keyword'] == 'user')
	{
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$network['settings']['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.type", $attendant['type']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : $attendant['value']);
	}
	elseif($attendant['keyword'] == 'parking')
	{
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$network['settings']['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : 'Park '.$attendant['value']);
	}
	elseif($attendant['keyword'] == 'conference')
	{
		$confname = sql("SELECT description FROM meetme 
			WHERE exten = '" . $db->escapeSimple($attendant['value']) . "'",'getOne');
	
		$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$attendant['value'].'@'.$network['settings']['address']);
		$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
			!empty($attendant['label']) ? $attendant['label'] : $confname);
	}
	elseif($attendant['keyword'] == 'callflow')
	{	
		$fcc = new featurecode('daynight', 'toggle-mode-' . $attendant['value']);
		$code = $fcc->getCodeActive();
		unset($fcc);
		
		if($code != '')
		{
			$flowname = sql("SELECT dest FROM daynight 
				WHERE dmode = 'fc_description' 
				AND ext = '" . $db->escapeSimple($attendant['value']) . "'",'getOne');

			$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.'@'.$network['settings']['address']);	
			$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
				!empty($attendant['label']) ? $attendant['label'] : ($flowname ? $flowname : $code));
		}
	}
	elseif($attendant['keyword'] == 'timecondition')
	{
		$fcc = new featurecode('timeconditions', 'toggle-mode-' . $attendant['value']);
		$code = $fcc->getCodeActive();
		unset($fcc);
		
		if($code != '')
		{
			$timename = sql("SELECT displayname FROM timeconditions 
				WHERE timeconditions_id = '" . $db->escapeSimple($attendant['value']) . "'",'getOne');

			$xml->attendant->addAttribute("attendant.resourceList.$i.address", 'sip:'.$code.'@'.$network['settings']['address']);	
			$xml->attendant->addAttribute("attendant.resourceList.$i.label", 
				!empty($attendant['label']) ? $attendant['label'] : $timename);
		}
	}
		
	$i++;
}

// Alert Info
$i=1;
foreach($alerts as $alert)
{
	$child = $xml->se->rt->addChild($alert['id'], ' ');

	$child->addAttribute("se.rt.".$alert['id'].".name", $alert['name']);
	$child->addAttribute("se.rt.".$alert['id'].".callwait", $alert['callwait']);
	$child->addAttribute("se.rt.".$alert['id'].".micmute", $alert['micmute']);
	$child->addAttribute("se.rt.".$alert['id'].".ringer", $alert['ringer']);
	$child->addAttribute("se.rt.".$alert['id'].".type", $alert['type']);
	
	if(!empty($alert['alertinfo']))
	{
		$xml->voIpProt->SIP->alertInfo->addAttribute("voIpProt.SIP.alertInfo.$i.class", $alert['id']);
		$xml->voIpProt->SIP->alertInfo->addAttribute("voIpProt.SIP.alertInfo.$i.value", $alert['alertinfo']);
		
		$i++;
	}
}

// Network Settings
if(!empty($network['settings']['tcpIpApp_sntp_address']))
	$xml->tcpIpApp->sntp->addAttribute("tcpIpApp.sntp.address", $network['settings']['tcpIpApp_sntp_address']);

$xml->tcpIpApp->sntp->addAttribute("tcpIpApp.sntp.address.overrideDHCP", $network['settings']['tcpIpApp_sntp_address_overrideDHCP']);
	
if(!empty($network['settings']['tcpIpApp_sntp_gmtOffset']))
	$xml->tcpIpApp->sntp->addAttribute("tcpIpApp.sntp.gmtOffset", $network['settings']['tcpIpApp_sntp_gmtOffset']);

$xml->nat->keepalive->addAttribute("nat.keepalive.interval", $network['settings']['nat_keepalive_interval']);
$xml->voice->codecPref->addAttribute("voice.codecPref.G711_Mu", $network['settings']['voice_codecPref_G711_Mu']);
$xml->voice->codecPref->addAttribute("voice.codecPref.G711_A", $network['settings']['voice_codecPref_G711_A']);
$xml->voice->codecPref->addAttribute("voice.codecPref.G722", $network['settings']['voice_codecPref_G722']);
$xml->voice->codecPref->addAttribute("voice.codecPref.G729_AB", $network['settings']['voice_codecPref_G729_AB']);

// General Settings
$xml->apps->push->addAttribute("apps.push.password", $general['apps_push_password']);
$xml->httpd->cfg->addAttribute("httpd.cfg.enabled", $general['httpd_cfg_enabled']);

$digits = '';
for($i=1; $i<$general['digits']; $i++)
	$digits .= 'x';

$xml->dialplan->addAttribute("dialplan.digitmap", "*x.T|**[1-9]".$digits."|[2-9]11|0T|011xxx.T|[0-1][2-9]xxxxxxxxx|[2-9]xxxxxxxxx|[1-9]".$digits."T");

if(!empty($general['mb_main_home']))
	$xml->mb->main->addAttribute("mb.main.home", $general['mb_main_home']);

$xml->softkey->feature->basicCallManagement->addAttribute("softkey.feature.basicCallManagement.redundant", polycomphones_getvalue('softkey_feature_basicCallManagement_redundant', $device, $general));
$xml->call->callWaiting->addAttribute("call.transfer.blindPreferred", polycomphones_getvalue('call_transfer_blindPreferred', $device, $general));
$xml->call->callWaiting->addAttribute("call.callWaiting.ring", polycomphones_getvalue('call_callWaiting_ring', $device, $general));
$xml->call->hold->localReminder->addAttribute("call.hold.localReminder.enabled", polycomphones_getvalue('call_hold_localReminder_enabled', $device, $general));
$xml->call->addAttribute("call.rejectBusyOnDnd", polycomphones_getvalue('call_rejectBusyOnDnd', $device, $general));
$xml->up->addAttribute("up.headsetMode", polycomphones_getvalue('up_headsetMode', $device, $general));
$xml->up->addAttribute("up.analogHeadsetOption", polycomphones_getvalue('up_analogHeadsetOption', $device, $general));
$xml->up->addAttribute("up.useDirectoryNames", polycomphones_getvalue('up_useDirectoryNames', $device, $general));
$xml->dir->local->addAttribute("dir.local.readonly", polycomphones_getvalue('dir_local_readonly', $device, $general));
$xml->feature->directedCallPickup->addAttribute("feature.directedCallPickup.enabled", polycomphones_getvalue('feature_directedCallPickup_enabled', $device, $general));
$xml->attendant->ringType->addAttribute("attendant.ringType", polycomphones_getvalue('attendant_ringType', $device, $general));
$xml->powerSaving->addAttribute("powerSaving.enable", polycomphones_getvalue('powerSaving_enable', $device, $general));
$xml->up->backlight->addAttribute("up.backlight.idleIntensity", polycomphones_getvalue('up_backlight_idleIntensity', $device, $general));
$xml->up->backlight->addAttribute("up.backlight.onIntensity", polycomphones_getvalue('up_backlight_onIntensity', $device, $general));
$xml->apps->ucdesktop->addAttribute("apps.ucdesktop.adminEnabled", polycomphones_getvalue('apps_ucdesktop_adminEnabled', $device, $general));

$xml->powerSaving->idleTimeout->addAttribute("powerSaving.idleTimeout.officeHours", $general['powerSaving_idleTimeout_officeHours']);
$xml->powerSaving->idleTimeout->addAttribute("powerSaving.idleTimeout.offHours", $general['powerSaving_idleTimeout_offHours']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.monday", $general['powerSaving_officeHours_startHour_monday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.tuesday", $general['powerSaving_officeHours_startHour_tuesday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.wednesday", $general['powerSaving_officeHours_startHour_wednesday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.thursday", $general['powerSaving_officeHours_startHour_thursday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.friday", $general['powerSaving_officeHours_startHour_friday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.saturday", $general['powerSaving_officeHours_startHour_saturday']);
$xml->powerSaving->officeHours->startHour->addAttribute("powerSaving.officeHours.startHour.sunday", $general['powerSaving_officeHours_startHour_sunday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.monday", $general['powerSaving_officeHours_duration_monday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.tuesday", $general['powerSaving_officeHours_duration_tuesday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.wednesday", $general['powerSaving_officeHours_duration_wednesday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.thursday", $general['powerSaving_officeHours_duration_thursday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.friday", $general['powerSaving_officeHours_duration_friday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.saturday", $general['powerSaving_officeHours_duration_saturday']);
$xml->powerSaving->officeHours->duration->addAttribute("powerSaving.officeHours.duration.sunday", $general['powerSaving_officeHours_duration_sunday']);
				
// Directed Call Pickup
if(polycomphones_getvalue('feature_directedCallPickup_enabled', $device, $general) == '1')
{
	$xml->call->addAttribute("call.directedCallPickupMethod", "native");
	$xml->call->addAttribute("call.directedCallPickupString", "");
	$xml->attendant->behaviors->display->spontaneousCallAppearances->addAttribute("attendant.behaviors.display.spontaneousCallAppearances.normal", polycomphones_getvalue('attendant_spontaneousCallAppearances_normal', $device, $general));
	$xml->attendant->behaviors->display->spontaneousCallAppearances->addAttribute("attendant.behaviors.display.spontaneousCallAppearances.automata", polycomphones_getvalue('attendant_spontaneousCallAppearances_automata', $device, $general));
}
else
{
	$xml->attendant->behaviors->display->spontaneousCallAppearances->addAttribute("attendant.behaviors.display.spontaneousCallAppearances.normal", "0");
	$xml->attendant->behaviors->display->spontaneousCallAppearances->addAttribute("attendant.behaviors.display.spontaneousCallAppearances.automata", "0");
}

// MWI Audible Alert
if(polycomphones_getvalue('se_pat_misc_messageWaiting_inst', $device, $general) == '0')
{
	$xml->se->pat->misc->messageWaiting->addAttribute("se.pat.misc.messageWaiting.inst.1.type", "silenced");
	$xml->se->pat->misc->messageWaiting->addAttribute("se.pat.misc.messageWaiting.inst.2.type", "silenced");
	$xml->se->pat->misc->messageWaiting->addAttribute("se.pat.misc.messageWaiting.inst.3.type", "silenced");
}

// Corporate Settings
if(!empty($general['dir_corp_address']))
	$xml->dir->corp->addAttribute("dir.corp.address", $general['dir_corp_address']);

if(!empty($general['dir_corp_port']))
	$xml->dir->corp->addAttribute("dir.corp.port", $general['dir_corp_port']);

if(!empty($general['dir_corp_baseDN']))
	$xml->dir->corp->addAttribute("dir.corp.baseDN", $general['dir_corp_baseDN']);

if(!empty($general['dir_corp_user']))
	$xml->dir->corp->addAttribute("dir.corp.user", $general['dir_corp_user']);
	
if(!empty($general['dir_corp_password']))
	$xml->dir->corp->addAttribute("dir.corp.password", $general['dir_corp_password']);

if(!empty($general['exchange_server_url']))
	$xml->exchange->server->addAttribute("exchange.server.url", $general['exchange_server_url']);

$xml->feature->corporateDirectory->addAttribute("feature.corporateDirectory.enabled", polycomphones_getvalue('feature_corporateDirectory_enabled', $device, $general));
$xml->feature->exchangeCalendar->addAttribute("feature.exchangeCalendar.enabled", polycomphones_getvalue('feature_exchangeCalendar_enabled', $device, $general));
 
// Xfer VM Button
$fcc = new featurecode('voicemail', 'directdialvoicemail');
$code = $fcc->getCodeActive();
unset($fcc);

if($code != '')
{
	$xml->efk->efklist->addAttribute("efk.efklist.1.action.string", $code.'$P1N4$$Trefer$');
	$xml->softkey->addAttribute("softkey.1.enable", '1');
}
else
	$xml->softkey->addAttribute("softkey.1.enable", '0');

// Park Call Button
if($parking_module)
{
	$fcc = new featurecode('core', 'blindxfer');
	$code = $fcc->getCodeActive();
	unset($fcc);

	$parkext = sql("SELECT parkext FROM parkplus ORDER BY id LIMIT 1",'getOne');
	
	// Blind transfer feature code is preferred as using phone transfer will hang up call if slots are filled
	if($code != '')
		$xml->softkey->addAttribute("softkey.2.action", polycomphones_get_dialpad($code).'$Cp1$'.$parkext.'$Tdtmf$$FDialpadPound$');
	// VVX series does not send digits on # so use send call soft key
	else if(strpos($_SERVER['HTTP_USER_AGENT'], 'PolycomVVX') !== false)
		$xml->softkey->addAttribute("softkey.2.action", '$FTransfer$'.polycomphones_get_dialpad($parkext).'$FSoftKey1$$Cp3$$Chu$');
	// SoundPoint series will send digits on #
	else
		$xml->softkey->addAttribute("softkey.2.action", '$FTransfer$'.polycomphones_get_dialpad($parkext).'$FDialpadPound$$Cp3$$Chu$');
	
	$xml->softkey->addAttribute("softkey.2.enable", '1');
}
else
	$xml->softkey->addAttribute("softkey.2.enable", '0');

// Record Call Button
$fcc = new featurecode('core', 'automon');
$code = $fcc->getCodeActive();
unset($fcc);

if($code != '')
{
	$xml->softkey->addAttribute("softkey.3.action", $code);
	$xml->softkey->addAttribute("softkey.3.enable", '1');
}
else
	$xml->softkey->addAttribute("softkey.3.enable", '0');
	
// VVX series move DND button to next page
if(strpos($_SERVER['HTTP_USER_AGENT'], 'PolycomVVX') !== false)
	$xml->softkey->addAttribute("softkey.4.enable", '1');
else
	$xml->softkey->addAttribute("softkey.4.enable", '0');

header("Content-type: application/xml");
echo $xml->asXML();

?>
