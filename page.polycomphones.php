<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

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
		
		if(isset($_GET['checkconfig']))
		{
			// Use SIP notify as all phones should have a SIP registration
			polycomphones_checkconfig();
			redirect_standard('polycomphones_form');
		}
		
		$devices = polycomphones_get_phones_list();
		require 'modules/polycomphones/views/polycomphones_phones.php';
		break;
		
	case 'phones_edit':		
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{	
			$device['name'] = $_POST['name'];
			$device['mac'] = $_POST['mac'];
			
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
			}

			$fields = array(
				'call_callWaiting_ring',
				'call_hold_localReminder_enabled',
				'feature_directedCallPickup_enabled',
				'up_backlight_idleIntensity',
				'up_backlight_onIntensity',
				'nat_keepalive_interval',
			);
			
			foreach ($fields as $field)
				$device['settings'][$field] = $_POST[$field];
		
			polycomphones_save_phones_edit($_GET['edit'], $device);
			polycomphones_multiple_check();
			
			// Push config sends HTTP requset to the IP address of the phone
			// Works for internal phones even when they don't have a SIP registration
			if(!polycomphones_push_checkconfig($_GET['edit']))
			{
				// Fallback to SIP notify which will work for external phones with a SIP registration
				polycomphones_checkconfig($_GET['edit']);
			}
			
			if(isset($_POST['add_line']))
				redirect_standard('polycomphones_form','edit');
			else
				redirect('config.php?type=setup&display=polycomphones&polycomphones_form=phones_list');
		}
		
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
		
	case 'general_edit':
		if(isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			$fields = array(
				'address',
				'port',
				'tcpIpApp_sntp_address',
				'tcpIpApp_sntp_gmtOffset',
				'lineKeys',
				'ringType',
				'missedCallTracking',
				'callBackMode',
				'call_callWaiting_ring',
				'call_hold_localReminder_enabled',
				'feature_directedCallPickup_enabled',
				'up_backlight_idleIntensity',
				'up_backlight_onIntensity',
				'nat_keepalive_interval',
			);
			
			foreach ($fields as $field)
				$settings[$field] = $_POST[$field];
		
			polycomphones_save_general_edit($settings);
			redirect_standard('polycomphones_form');
		}
		
		$general = polycomphones_get_general_edit();
		require 'modules/polycomphones/views/polycomphones_general.php';
		break;
		
	default:
		require 'modules/polycomphones/views/polycomphones.php';
		break;	
}

echo '</div>';

?>