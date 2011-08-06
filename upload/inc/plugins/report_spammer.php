<?php
/**
 * "Report Spammer" v1.2 MyBB plugin
 * 
 * Use various web sites and services to gather information of spamming activity by the user currently edited in Mod CP.
 * Offer the option of reporting the scumbag to StopForumSpam.com.
 * 
 * Copyright 2011 Dan Dascalescu, http://dandascalescu.com
 *
 * Website: https://github.com/dandv/Report-Spammer-MyBB-plugin
 *
 * NOTE: This plugin does not support localization on purpose. For the rationale of this decision, please carefully read
 * http://wiki.dandascalescu.com/essays/english-universal-language
 * Patches that implement localization will only be accepted if acceptable refutation of the essay linked above is provided.
 *
 * License: GPL3. However, redistribution of modifications that include localization is prohibited.
 */

// Disallow direct access to this file for security reasons
if(!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

$plugins->add_hook('modcp_editprofile_end', 'report_spammer_display');
$plugins->add_hook('xmlhttp', 'report_spammer_ajax');

function report_spammer_info()
{
	return array(
		'name'			=> 'Report Spammer',
		'description'	=> 'Show how likely that a given user is a spammer, and allows reporting them to <a href="http://stopforumspam.com">StopForumSpam.com</a>.',
		'website'		=> 'https://github.com/dandv/Report-Spammer-MyBB-plugin',
		'author'		=> 'Dan Dascalescu',
		'authorsite'	=> 'http://dandascalescu.com',
		'version'		=> '1.0',
		'guid' 			=> '0b53630a641f43baeea8bee1428c585a',
		'compatibility' => '16*'
	);
}

/**
 *   Called whenever a plugin is installed by clicking the "Install" button in the plugin manager.
 */
function report_spammer_install()
{
	global $db;
	report_spammer_uninstall();

	// add settings
	$result = $db->simple_select('settinggroups', 'MAX(disporder) AS max_disporder');
	$max_disporder = $db->fetch_field($result, 'max_disporder');

	$settings_group = array(
		'gid'			=> 'NULL',
		'name'			=> 'report_spammer',
		'title'			=> 'Report Spammer',
		'description'	=> 'Settings for the Report Spammer plugin',
		'disporder'		=> $max_disporder + 1,
		'isdefault'		=> '0'
	);
	$db->insert_query('settinggroups', $settings_group);
	$gid = (int) $db->insert_id();

	$db->insert_query('settings', array(
		'sid'			=> 'NULL',
		'name'			=> 'report_spammer_StopForumSpam_key',
		'title'			=> 'StopForumSpam.com key',
		'description'	=> 'To report spammers, StopForumSpam.com requires an API key. Get one <a href="http://www.stopforumspam.com/signup">here</a>.',
		'optionscode'	=> 'text',
		'value'			=> '',
		'disporder'		=> '1',
		'gid'			=> $gid
	));

	// Create a new global template for displaying the Report Spammer information.
	// Other plugins do this in _activate(), but if the user customized the template, when they deactivate the plugin, the customizations will be lost.
	$template_content ='
		<br />
		<style>
		.spammer_yes, .report_spammer_error {
			font-weight: bold;
			color: red;
		}
		.spammer_no, .report_spammer_success {
			font-weight: bold;
			color: green;
		}
		.spammer_unknown {
		}
		</style>
		<fieldset class="trow2">
			<legend><strong>Report Spammer options</strong></legend>
			<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}">
				<tr>
					<td>Username known for spamming:</td>
					<td class="{$username_spammer_class}">{$username_spammer_message}</td>
				</tr>
				<tr>
					<td>Email known for spamming:</td>
					<td class="{$email_spammer_class}">{$email_spammer_message}</td>
				</tr>
				{$ip_addresses_spammer}
			</table>
			<input type="button" id="report_spammer_button" value="{$report_spammer_button_text}" />
			<br/>
			<div id="report_spammer_status" />
		</fieldset>
		<script type="text/javascript">
			var report_spammer_username={$username};
			var report_spammer_email={$email};
			var report_spammer_ip_addresses = {$ip_addresses};
		</script>
		<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/report_spammer.js"></script>
';
	$db->insert_query('templates', array(
		'title' => 'report_spammer_display',
		'template' => $db->escape_string($template_content),
		'sid' => '-1',
		'version' => '1603',
		'dateline' => TIME_NOW
	));

	rebuild_settings();
}
/**
 *   Called on the plugin management page to establish if a plugin is already installed or not.
 *   Returns boolean.
 */
function report_spammer_is_installed()
{
	global $db;
	$query = $db->simple_select('settinggroups', 'name', 'name="report_spammer"');
	return $query->num_rows > 0;
}

/**
 *    Remove ALL traces of the plugin from the installation (templates, settings etc).
 */
function report_spammer_uninstall()
{
	global $db;
	// delete templates
	$db->delete_query('templates', 'title LIKE "report_spammer%"');
	// delete the settings
	$query = $db->simple_select('settinggroups', 'gid', 'name = "report_spammer"');
	if ($gid = $db->fetch_field($query, 'gid'))
	{
		$db->delete_query('settings', 'gid = ' . $gid);
		$db->delete_query('settinggroups', 'gid = ' . $gid);
	}

	rebuild_settings();
}


// Make the plugin "visible" by adding and changing templates
function report_spammer_activate()
{
	report_spammer_deactivate();
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	// add an indication that there are some Report Spammer options in the 'Edit this user is Mod CP' page
	find_replace_templatesets('member_profile_modoptions', '#(lang->edit_in_mcp}</a>)#i', '$1 (+ <i>Report Spammer</i> options)');
	
	find_replace_templatesets('modcp_editprofile', '#(lang->moderation}.*?</tr>\s*</table>\s*</fieldset>)#si', '$1' . "\n" . '{$report_spammer}');
}

function report_spammer_deactivate()
{
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('modcp_editprofile', '#\s{\$report_spammer}#i', '', 0);
	find_replace_templatesets('member_profile_modoptions', '# \(\+ <i>Report Spammer</i> options\)#', '', 0);
}

// After over 100 lines of boilerplate code, we get to do interesting stuff
function report_spammer_display()
{
	// TODO: delayed loading of external HTTP requests
	global $templates, $user, $session;  // imports
	global $mybb, $theme;      // imports used in eval'ing the template
	global $report_spammer;    // exports
	// variables used by the report_spammer_display template
	$username_spammer_message = 'No';
	$username_spammer_class = 'spammer_unknown';
	$email_spammer_message = 'No';
	$email_spammer_class = 'spammer_unknown';
	$ip_addresses_spammer = '';  // not worth creating a template, nothing to customize here
	$report_spammer_button_text = 'Report spammer';  // changed to 'Confirm spammer report' if spammer has already been reported

	$ipaddresses = array();
	$ipaddresses[] = array(
		title => 'Registration IP',
		ip => $user['regip']
	);
	if($user['lastip'] != $user['regip'])
	{
		$ipaddresses[] = array(
			title => 'Last IP',
			ip => $user['lastip']
		);
	}
	// TODO: we only get the lastip and regip, but the user might have used more IPs, which we could find in their posting history
/*	$ipaddresses[] = array(
		title => 'IP used to post <a href="' . get_post_link($post['pid']) . '">{$post['subject']}</a>',
		ip => $post['ipaddress']
	);
*/
	
	$ch = curl_init();
	## curl_setopt_array doesn't work in some configurations (see http://community.mybb.com/thread-100846-post-735575.html#pid735575)
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // follow redirects
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // return a string, don't output it directly

	// see http://stopforumspam.com/usage
	curl_setopt($ch, CURLOPT_URL, 'http://www.stopforumspam.com/api?email=' . urlencode($user['email']) . '&username=' . urlencode($user['username']) . '&f=serial');
	$output = curl_exec($ch);
	$stopforumspam_info = unserialize($output);  // it's fine, this is not an eval
	if($stopforumspam_info['success'])
	{
		if($stopforumspam_info['username']['appears'])
		{
			$report_spammer_button_text = 'Confirm spammer report';
			$username_spammer_class = 'spammer_yes';
			$username_spammer_message = "Yes, reported {$stopforumspam_info['username']['frequency']} time(s)";
		}
		if($stopforumspam_info['email']['appears'])
		{
			$report_spammer_button_text = 'Confirm spammer report';
			$email_spammer_class = 'spammer_yes';
			$email_spammer_message = "Yes, reported {$stopforumspam_info['email']['frequency']} time(s)";
		}
	}
	$ip_addresses_var = array();
    // TODO: we could reduce the number of queries to StopForumSpam by batching them, but SFS supports only 15 items max per query
	foreach($ipaddresses as $ipaddress)
	{
		$ip_addresses_var[] = $ipaddress['ip'];
		$ip_addresses_spammer .= "
			<tbody>
				<tr>
					<td>{$ipaddress['title']}:</b></td><td>{$ipaddress['ip']}</td>
				</tr>";
		// fetch a URL into a string
		curl_setopt($ch, CURLOPT_URL, 'http://whatismyipaddress.com/ip/' . $ipaddress['ip']);
		$output = curl_exec($ch);
		preg_match('#Hostname:.*?<td>(.*?)</td>.*?ISP:.*?<td>(.*?)</td>.*?Organization:.*?<td>(.*?)</td>#', $output, $matches);
		$hostname = preg_replace('/(server)/i',  '<span class="spammer_yes">$1</span>', $matches[1]);
		$isp = preg_replace('/(server)/i',  '<span class="spammer_yes">$1</span>', $matches[2]);
		$organization = preg_replace('/(server)/i',  '<span class="spammer_yes">$1</span>', $matches[3]);
		$ip_addresses_spammer .= "
				<tr>
					<td>Hostname:</td><td>{$hostname}</td>
				</tr>
				<tr>
					<td>ISP:</td><td>{$isp}</td>
				</tr>
				<tr>
					<td>Organization</td><td>{$organization}</td>
				</tr>";		
		curl_setopt($ch, CURLOPT_URL, 'http://www.stopforumspam.com/api?ip=' . $ipaddress['ip'] . '&f=serial');
		$output = curl_exec($ch);
		$stopforumspam_info = unserialize($output);  // it's fine, this is not an eval
		if($stopforumspam_info['ip']['appears'])
		{
			$report_spammer_button_text = 'Confirm spammer report';
			$ip_addresses_spammer .= "
				<tr>
					<td>IP reported as spamming?</td><td class='spammer_yes'>Yes, {$stopforumspam_info['ip']['frequency']} time(s)</td>
				</tr>";
		}

		$ip_addresses_spammer .= "
			</tbody>";
	}

	curl_close($ch);

	$username = "'".$user['username']."'";
	$email = "'".$user['email']."'";
	$ip_addresses = "'".json_encode(array_values($ip_addresses_var))."'";
	eval("\$report_spammer = \"".$templates->get('report_spammer_display')."\";");
}


// called for all AJAX actions, e.g. validate username availability, CAPTCHA...
function report_spammer_ajax()
{
	global $mybb;
    // ... so we must only act on what pertains to our plugin
	if($mybb->input['action'] == 'report_spammer_do' && $mybb->input['ajax'] == 1)
	{
		if (empty($mybb->settings['report_spammer_StopForumSpam_key']))
		{
			echo json_encode(array(
				status => 'error',
				status_text => 'Please configure the StopForumSpam.com key in the <a href="' . $mybb->settings['bburl'] . '/admin/index.php?module=config-settings">plugin settings</a>.' 
			));
			exit;
		}

		if(!verify_post_check($mybb->input['my_post_key'], true))
		{
			echo json_encode(array(
				status => 'error',
				status_text => 'Authorization code mismatch' 
			));
			exit;
		}

		// http://stopforumspam.com/usage doesn't support adding multiple spammer IPs per query
		$ch = curl_init('http://www.stopforumspam.com/add.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // don't output result, but return in a string
		curl_setopt($ch, CURLOPT_POST, true);
		$ipaddresses = json_decode($mybb->input['ipaddresses']);
		$ips_reported = 0;
		$stop_forum_spam_output = '';

		// report each IP
		foreach($ipaddresses as $ipaddress) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, array(
				'username' => $mybb->input['username'],
				'email' => $mybb->input['email'],
				'ip_addr' => $ipaddress,
				'api_key' => $mybb->settings['report_spammer_StopForumSpam_key']
			));
			$stop_forum_spam_output .= curl_exec($ch);
			$info = curl_getinfo($ch);
			$ips_reported += ($info['http_code'] >= 200 && $info['http_code'] < 300);  // the return value is documented to be only 200 for success, but theoretically, any 2xx means success
		}

		curl_close($ch);

		if($ips_reported == 0)
		{
			echo json_encode(array(
				'status' => 'error',
				'status_text' => "Reporting spammer failed: $stop_forum_spam_output",
			));
		}
		else
		{
			$adjectives = array('annoying', 'braindead', 'dimwit', 'dopey', 'insensate', 'moronic', 'retarded', 'silly', 'stupid', 'witless');
			$random = array_rand($adjectives);
			$adjective = $adjectives[$random]; 

			echo json_encode(array(
				'status' => 'success',  // or partial success
				'status_text' => "Reported this $adjective spammer and $ips_reported of their " . count($ipaddresses) . " IP address(es).",
			));
		}
		exit;
	}
}

?>
