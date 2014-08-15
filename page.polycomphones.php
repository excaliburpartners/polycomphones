<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/*
 * Copyright (C) 2013 Excalibur Partners, LLC (info@excalibur-partners.com)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

include('views/rnav.php');
echo '<div id="content">';

switch($_GET['polycomphones_form'])
{
	case 'phones_list':
		if(isset($_GET['delete']))
		{
			polycomphones_delete_phones_list($_GET['delete']);
			polycomphones_multiple_check();
			
			redirect_standard('polycomphones_form');
		}
		
		if(isset($_GET['pushcheck']))
		{
			if(($failed = polycomphones_push_checkconfig()) !== true)
				polycomphones_checkconfig($failed);
			
			redirect_standard('polycomphones_form');
		}
		
		if(isset($_GET['checkconfig']))
		{
			polycomphones_checkconfig(!empty($_GET['checkconfig']) ? $_GET['checkconfig'] : null);
		
			redirect_standard('polycomphones_form');
		}
		
		if(isset($_GET['clearoverrides']))
		{
			$mac = !empty($_GET['clearoverrides']) ? $_GET['clearoverrides'] : null;
			$id = !empty($_GET['clearoverrides']) ? polycomphones_lookup_mac($mac) : null;

			polycomphones_clear_overrides($mac);
			
			if(($failed = polycomphones_push_checkconfig($id)) !== true)
				polycomphones_checkconfig($failed);
			
			redirect_standard('polycomphones_form');
		}
		
		$devices = polycomphones_get_phones_list();
		require 'modules/polycomphones/views/polycomphones_phones.php';
		break;
		
	case 'phones_edit':		
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{	
			$device['name'] = $_POST['name'];
			$device['mac'] = strtolower($_POST['mac']);
			
			foreach($_POST['line'] as $key=>$value)
			{	
				$key++;
				$device['lines'][$key]['deviceid'] = null;
				$device['lines'][$key]['externalid'] = null;
			
				if(strpos($value, 'freepbx_') === 0)
					$device['lines'][$key]['deviceid'] = substr($value, 8);
				elseif(strpos($value, 'external_') === 0)
					$device['lines'][$key]['externalid'] = substr($value, 9);
			}

			$fields = array(
				'lineKeys',
				'ringType',
				'missedCallTracking',
				'callBackMode',
				'serverFeatureControl_dnd',
				'serverFeatureControl_cf',
			);
			
			foreach ($fields as $field)
				foreach($_POST[$field] as $key=>$value)
				{
					$key++;
					if($device['lines'][$key])
						$device['lines'][$key]['settings'][$field] = $value;
				}
				
			foreach($_POST['attendant'] as $key=>$value)
			{	
				$key++;
			
				if(($pos = strpos($value, '_')) !== false)
				{
					$device['attendants'][$key]['keyword'] = substr($value, 0, $pos);
					$device['attendants'][$key]['value'] = substr($value, $pos + 1);
				}
				
				$device['attendants'][$key]['label'] = $_POST['label'][$key-1];
				$device['attendants'][$key]['type'] = $_POST['type'][$key-1];
			}

			$fields = array(
				'lineKey_reassignment_enabled',
				'lineKey_category_line',
				'lineKey_category_blf',
				'lineKey_category_favorites',
				'lineKey_category_unassigned',
				'softkey_feature_basicCallManagement_redundant',
				'call_transfer_blindPreferred',
				'call_callWaiting_ring',
				'call_hold_localReminder_enabled',
				'call_rejectBusyOnDnd',
				'up_useDirectoryNames',
				'dir_local_readonly',
				'se_pat_misc_messageWaiting_inst',
				'apps_ucdesktop_adminEnabled',
				'up_headsetMode',
				'up_analogHeadsetOption',
				'attendant_ringType',
				'feature_directedCallPickup_enabled',
				'attendant_spontaneousCallAppearances_normal',
				'attendant_spontaneousCallAppearances_automata',
				'powerSaving_enable',
				'up_backlight_idleIntensity',
				'up_backlight_onIntensity',
				'feature_corporateDirectory_enabled',
				'feature_exchangeCalendar_enabled',
			);
			
			foreach ($fields as $field)
				$device['settings'][$field] = $_POST[$field];
		
			polycomphones_save_phones_edit($_GET['edit'], $device);
			polycomphones_multiple_check();
			
			// Push config sends HTTP request to the IP address of the phone
			// Works for internal phones even when they don't have a SIP registration
			if(polycomphones_push_checkconfig($_GET['edit']) !== true)
			{
				// Fallback to SIP notify which will work for external phones with a SIP registration	
				polycomphones_checkconfig($_GET['edit']);
			}
			
			redirect('config.php?type=setup&display=polycomphones&polycomphones_form=phones_list');
		}
		
		$features_module = polycomphones_check_module('phonefeatures');
		$device = polycomphones_get_phones_edit($_GET['edit']);
		
		foreach($device['lines'] as $key=>$line)
		{	
			if($line['deviceid'] != null)
				$device['lines'][$key]['line'] = 'freepbx_' . $line['deviceid'];
			elseif($line['externalid'] != null)
				$device['lines'][$key]['line'] = 'external_' . $line['externalid'];	
		}
		
		foreach($device['attendants'] as $key=>$attendant)
			$device['attendants'][$key]['attendant'] = $attendant['keyword'].'_'.$attendant['value'];
			
		require 'modules/polycomphones/views/polycomphones_phones_edit.php';
		break;
		
	case 'phones_directory':
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{		
			$fields = array(
				'fn',
				'ln',
				'ct',
				'sd',
				'rt',
				'bw',
			);
			
			$directory = array();
			foreach ($fields as $field)
				foreach($_POST[$field] as $key=>$value)
				{
					$key++;
					
					if($field == 'sd')
						$directory[$key][$field] = $value == '1' ? $key : '';
					elseif($field =='rt' && $value == '')
						$directory[$key][$field] = 'default';
					else
						$directory[$key][$field] = $value;
				}
		
			polycomphones_save_phones_directory($_GET['edit'], $directory);
			
			// Use SIP notify to reboot phone
			polycomphones_checkconfig(polycomphones_lookup_mac($_GET['edit']));
			
			redirect('config.php?type=setup&display=polycomphones&polycomphones_form=phones_list');	
		}
		
		$directory = polycomphones_get_phones_directory($_GET['edit']);
		
		foreach($directory as $key => $contact)
		{
			if($contact['sd'] != '')
				$directory[$key]['sd'] = '1';
				
			if($contact['rt'] == 'default')
				$directory[$key]['rt'] = '';
		}
		
		require 'modules/polycomphones/views/polycomphones_phones_directory.php';
		break;
		
	case 'externallines_list':
		if(isset($_GET['delete']))
		{
			polycomphones_delete_externallines_list($_GET['delete']);
			redirect_standard('polycomphones_form');
		}
	
		$lines = polycomphones_get_externallines_list();
		require 'modules/polycomphones/views/polycomphones_externallines.php';
		break;
		
	case 'externallines_edit':
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$line['name'] = $_POST['name'];
		
			$fields = array(
				'label',
				'user',
				'secret',
				'address',
				'port',
				'transport',
				'register',
				'mwicallback',
			);
			
			foreach ($fields as $field)
				$line['settings'][$field] = $_POST[$field];

			polycomphones_save_externallines_edit($_GET['edit'], $line);
			redirect('config.php?type=setup&display=polycomphones&polycomphones_form=externallines_list');
		}
		
		$line = polycomphones_get_externallines_edit($_GET['edit']);
		require 'modules/polycomphones/views/polycomphones_externallines_edit.php';
		break;
	
	case 'alertinfo_list':
		$alerts = polycomphones_get_alertinfo_list();
		require 'modules/polycomphones/views/polycomphones_alertinfo.php';
		break;
		
	case 'alertinfo_edit';
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$fields = array(
				'name',
				'callwait',
				'micmute',
				'ringer',
				'type',
				'alertinfo',
			);
			
			foreach ($fields as $field)
				$alert[$field] = $_POST[$field];
		
			polycomphones_save_alertinfo_edit($_GET['edit'], $alert);
			redirect('config.php?type=setup&display=polycomphones&polycomphones_form=alertinfo_list');
		}
		
		$alert = polycomphones_get_alertinfo_edit($_GET['edit']);
		require 'modules/polycomphones/views/polycomphones_alertinfo_edit.php';
		break;
		
	case 'networks_list':
		if(isset($_GET['delete']))
		{
			polycomphones_delete_networks_list($_GET['delete']);
			redirect_standard('polycomphones_form');
		}
	
		$networks = polycomphones_get_networks_list();
		require 'modules/polycomphones/views/polycomphones_networks.php';
		break;
		
	case 'networks_edit':
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$network['name'] = $_POST['name'];
			$network['cidr'] = $_POST['cidr'];
			
			$fields = array(
				'prov_ssl',
				'prov_username',
				'prov_password',
				'prov_uploads',
				'address',
				'port',
				'nat_keepalive_interval',
				'tcpIpApp_sntp_address',
				'tcpIpApp_sntp_address_overrideDHCP',
				'tcpIpApp_sntp_gmtOffset',
				'voice_codecPref_G711_Mu',
				'voice_codecPref_G711_A',
				'voice_codecPref_G722',
				'voice_codecPref_G729_AB',
			);
			
			foreach ($fields as $field)
				$network['settings'][$field] = $_POST[$field];

			polycomphones_save_networks_edit($_GET['edit'], $network);
			redirect('config.php?type=setup&display=polycomphones&polycomphones_form=networks_list');
		}
		
		$network = polycomphones_get_networks_edit($_GET['edit']);

		if(empty($_GET['edit']))
		{
			$network['settings']['prov_uploads'] = '1';
			$network['settings']['port'] = '5060';
			$network['settings']['voice_codecPref_G711_Mu'] = '6';
			$network['settings']['voice_codecPref_G711_A'] = '7';
			$network['settings']['voice_codecPref_G722'] = '4';
			$network['settings']['voice_codecPref_G729_AB'] = '8';
		}		
		
		require 'modules/polycomphones/views/polycomphones_networks_edit.php';
		break;
	
	case 'corporate_edit';
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$fields = array(
				'dir_corp_address',
				'dir_corp_port',
				'dir_corp_baseDN',
				'dir_corp_user',
				'dir_corp_password',
				'exchange_server_url',
				'feature_corporateDirectory_enabled',
				'feature_exchangeCalendar_enabled',
			);
			
			foreach ($fields as $field)
				$settings[$field] = $_POST[$field];
				
			if($settings['dir_corp_password'] == '******')
				unset($settings['dir_corp_password']);
			
			polycomphones_save_general_edit($settings);
			redirect_standard('polycomphones_form');
		}
				
		$general = polycomphones_get_general_edit();
		
		if(!empty($general['dir_corp_password']))
			$general['dir_corp_password'] = '******';
		
		require 'modules/polycomphones/views/polycomphones_corporate.php';
		break;
		
	case 'general_edit':
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$fields = array(
				'digits',
				'httpd_cfg_enabled',
				'mb_main_home',
				'lineKeys',
				'ringType',
				'missedCallTracking',
				'callBackMode',
				'serverFeatureControl_dnd',
				'serverFeatureControl_cf',
				'softkey_feature_basicCallManagement_redundant',
				'call_transfer_blindPreferred',
				'call_callWaiting_ring',
				'call_hold_localReminder_enabled',
				'call_rejectBusyOnDnd',
				'up_useDirectoryNames',
				'dir_local_readonly',
				'se_pat_misc_messageWaiting_inst',
				'apps_ucdesktop_adminEnabled',
				'up_headsetMode',
				'up_analogHeadsetOption',
				'attendant_ringType',
				'feature_directedCallPickup_enabled',
				'attendant_spontaneousCallAppearances_normal',
				'attendant_spontaneousCallAppearances_automata',
				'powerSaving_enable',
				'up_backlight_idleIntensity',
				'up_backlight_onIntensity',
				'powerSaving_idleTimeout_officeHours',
				'powerSaving_idleTimeout_offHours',
				'powerSaving_officeHours_startHour_monday',
				'powerSaving_officeHours_startHour_tuesday',
				'powerSaving_officeHours_startHour_wednesday',
				'powerSaving_officeHours_startHour_thursday',
				'powerSaving_officeHours_startHour_friday',
				'powerSaving_officeHours_startHour_saturday',
				'powerSaving_officeHours_startHour_sunday',
				'powerSaving_officeHours_duration_monday',
				'powerSaving_officeHours_duration_tuesday',
				'powerSaving_officeHours_duration_wednesday',
				'powerSaving_officeHours_duration_thursday',
				'powerSaving_officeHours_duration_friday',
				'powerSaving_officeHours_duration_saturday',
				'powerSaving_officeHours_duration_sunday',
			);
			
			foreach ($fields as $field)
				$settings[$field] = $_POST[$field];
		
			polycomphones_save_general_edit($settings);
			redirect_standard('polycomphones_form');
		}
		
		$features_module = polycomphones_check_module('phonefeatures');
		$general = polycomphones_get_general_edit();
		require 'modules/polycomphones/views/polycomphones_general.php';
		break;
		
	default:
		require 'modules/polycomphones/views/polycomphones.php';
		break;	
}

echo '</div>';

?>