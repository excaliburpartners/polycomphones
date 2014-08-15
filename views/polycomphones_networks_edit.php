<h2><?php echo empty($_GET['edit']) ? 'Add' : 'Edit'; ?> Network</h2>
<hr />

<script type="text/javascript">
$(function(){
	$('input[name="cidr"]').keyup(function() {
		$(this).removeClass("duplicate-exten");
		$(this).next("span").css("display", "none");
		var inputVal = $(this).val();
		var characterReg = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$/;
		if(!characterReg.test(inputVal)) {
			$(this).addClass("duplicate-exten");
			$(this).next("span").css("display", "");
		}
	});
	
	$("form").submit(function( event ) {
		if($('input[name="cidr"]').hasClass("duplicate-exten")) {
			event.preventDefault();
		}
	});
});
</script>

<form name="polycomphones_networks_edit" method="post" action="config.php?type=setup&display=polycomphones&polycomphones_form=networks_edit&edit=<?php echo $_GET['edit'];?>">
<table>
<tbody>		
	<tr>
		<td width="175"><?php echo _("Name")?></td>
		<td><?php echo form_input('name', $network['name']); ?></td>
	</tr>
	<tr>
		<td><?php echo _("Network CIDR")?><span class="help">?<span style="display: none;">The CIDR network address to match for connecting phones. Example: 10.0.0.0/8</span></span></td>
		<?php if($_GET['edit'] == '-1') { ?>
		<td><?php echo $network['cidr'] ?><input type="hidden" name="cidr" value="<?php echo $network['cidr'] ?>"></td>
		<?php } else { ?>
		<td>
			<?php echo form_input('cidr', $network['cidr']); ?>
			<span style="display: none"><a href="#" title="Invalid CIDR network address">
				<img src="images/notify_critical.png" />
			</a></span>
		</td>
		<?php } ?>
	</tr>
	<tr>
		<td width="175"><?php echo _("Registration Address")?>*<span class="help">?<span style="display: none;">FreePBX server IP or hostname. Example: voip.domain.com</span></span></td>
		<td><?php echo form_input('address', $network['settings']['address']); ?></td>
	</tr>
	<tr>
		<td><?php echo _("Registration Port")?>*<span class="help">?<span style="display: none;">FreePBX server SIP port. Example: 5060</span></span></td>
		<td><?php echo form_input('port', $network['settings']['port'], 'size="10"'); ?></td>
	</tr>
	<tr>
		<td><?php echo _("NAT Keepalive Interval")?><span class="help">?<span style="display: none;">Sets the interval at which phones will send a keep-alive packet to the gateway/NAT device to keep the communication port open.</span></span></td>
		<td><?php echo form_dropdown('nat_keepalive_interval', polycomphones_dropdown('nat_keepalive_interval'),  $network['settings']['nat_keepalive_interval']); ?></td>
	</tr>
	<tr>
		<td><?php echo _("NTP Server")?><span class="help">?<span style="display: none;">Example: pool.ntp.org</span></span></td>
		<td><?php echo form_input('tcpIpApp_sntp_address', $network['settings']['tcpIpApp_sntp_address']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("NTP Override DHCP")?><span class="help">?<span style="display: none;">If 'Disabled', the DHCP values for the NTP server address will be used. If 'Enabled', the NTP parameters will override the DHCP values.</span></span></td>
		<td><?php echo form_dropdown('tcpIpApp_sntp_address_overrideDHCP', polycomphones_dropdown('disabled_enabled'), $network['settings']['tcpIpApp_sntp_address_overrideDHCP']); ?></td>	
	</tr>
	<tr>
		<td><?php echo _("Time Zone")?></td>
		<td><?php echo form_dropdown('tcpIpApp_sntp_gmtOffset', polycomphones_dropdown('tcpIpApp_sntp_gmtOffset', true, ''), $network['settings']['tcpIpApp_sntp_gmtOffset']); ?></td>	
	</tr>
</tbody>
</table>
<br />

<input type="hidden" name="action" value="edit">
<input type="submit" value="<?php echo _("Submit")?>">
</form>