/**
 * "Report Spammer" v1.1 MyBB plugin
 * 
 * For documentation and license, see https://github.com/dandv/Report-Spammer-MyBB-plugin
 *
 */

var ReportSpammer = {

	doReportSpammer: function()
	{
		this.spinner = new ActivityIndicator("body", {image: imagepath + "/spinner_big.gif"});
		$('report_spammer_button').disable();
		new Ajax.Request('xmlhttp.php?action=report_spammer_do', {
			method: 'post',
			parameters: {
				ajax: 1,
				my_post_key: my_post_key,  // global set by MyBB
				// globals set by modcp.php via our report_spammer_display template
				ipaddresses: report_spammer_ip_addresses,
				username: report_spammer_username,
				email: report_spammer_email
			},
			onComplete: function(transport) {
				ReportSpammer.replaceStatus(transport);
			}
		});
	},

	replaceStatus: function(transport)
	{
		response = transport.responseText.evalJSON();
		if(response.status === 'success')
		{
			$('report_spammer_status').addClassName('report_spammer_success');
		}
		else
		{
			$('report_spammer_status').addClassName('report_spammer_error');
			$('report_spammer_button').enable();
		}
		$('report_spammer_status').update(response.status_text);
		
		if(this.spinner)
		{
			this.spinner.destroy();
			this.spinner = '';
		}
	}
}

Event.observe(window, 'load', function() {
	$('report_spammer_button').observe('click', function(event) {
		Event.stop(event);
		ReportSpammer.doReportSpammer();
	});
});