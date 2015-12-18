/*
 * jQuery File Upload Plugin JS Example 8.9.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/* global $, window */
var timer;
var current_page = 1;

function update_vendor(hash_flt_val, vendor_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_update_file'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val, vendor: vendor_flt_val},	
		type: 'post'
	});
}

function update_comment(hash_flt_val, comment_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_update_file'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val, comment: comment_flt_val},	
		type: 'post'
	});
}

function start_virustotal_scan(hash_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_virustotal_scan'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val},	
		type: 'post'
	});
}

function start_cuckoo_scan(hash_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_cuckoo_scan'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val},	
		type: 'post'
	});
}

function get_cuckoo_infos()
{
	$.ajax({
		url: $('#fileupload').fileupload('option', 'url_get_cuckoo'),
		dataType: 'json',
		context: $('#fileupload')[0],	
		type: 'get',				
		success: function(data) { 
			var span_status	= document.getElementById("cuckoo-status");
			var href_status = document.getElementById("cuckoo-status-href");
			if (span_status && href_status) {
				span_status.innerHTML = "Cuckoo " + data["version"] + " [" + data["machines"]["total"] + " machine(s)]";
				href_status.setAttribute('href', data["browse_url"]);
				href_status.setAttribute('target', '_blank');
			}
		},
		cache: false
	});
}

function delayed_get_files()
{
	timer && clearTimeout(timer);
	timer = setTimeout(get_files, 700, current_page);
}

function get_files(page_flt)
{	
	current_page = page_flt;

	// get filers values
	var date_flt_val 	= !document.getElementById("date_filter") 	? "" : document.getElementById("date_filter").value;
	var user_flt_val 	= !document.getElementById("user_filter") 	? "" : document.getElementById("user_filter").value;
	var comment_flt_val = !document.getElementById("comment_filter") ? "" : document.getElementById("comment_filter").value;
	var hash_flt_val 	= !document.getElementById("hash_filter") 	? "" : document.getElementById("hash_filter").value;
	var vendor_flt_val 	= !document.getElementById("vendor_filter") ? "" : document.getElementById("vendor_filter").value;
	var name_flt_val 	= !document.getElementById("name_filter") 	? "" : document.getElementById("name_filter").value;
	var size_flt_val 	= !document.getElementById("size_filter") 	? "" : document.getElementById("size_filter").value;
	var virustotal_flt_val = !document.getElementById("virustotal_filter") 	? "" : document.getElementById("virustotal_filter").value;
	var cuckoo_flt_val = !document.getElementById("cuckoo_filter") 	? "" : document.getElementById("cuckoo_filter").value;

	// get filters row
	var filters_row = $("table tbody.files").find("tr#filters");	
	var cloned_filters = undefined;	
	
	// If filters are already here, we want to restore them after the refresh
	var filters_exist = filters_row.length > 0;	
	if (filters_exist) {
		cloned_filters = filters_row.clone();
	}
	
	// Clear all
	$("table tbody.files").find("tr").remove();	
	
	var data_array = {};
	if (date_flt_val) data_array["date"] = date_flt_val;	
	if (user_flt_val) data_array["user"] = user_flt_val;
	if (comment_flt_val) data_array["comment"] = comment_flt_val;	
	if (hash_flt_val) data_array["hash"] = hash_flt_val;	
	if (vendor_flt_val) data_array["vendor"] = vendor_flt_val;	
	if (name_flt_val) data_array["name"] = name_flt_val;	
	if (size_flt_val) data_array["size"] = size_flt_val;
	if (virustotal_flt_val) data_array["virustotal"] = virustotal_flt_val;
	if (cuckoo_flt_val) data_array["cuckoo"] = cuckoo_flt_val;	
	data_array["page"] = page_flt;
	
	// Load existing files:
	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_get_files'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: data_array,	
		type: 'get'
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
		
		// Create filter inputs
		if (!filters_exist){
			$("table tbody.files").prepend('<tr id="filters"> \
											<td align="left"><input type="text" id="user_filter" style="width:50px;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="date_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="vendor_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="comment_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="hash_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="name_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="size_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="virustotal_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"><input type="text" id="cuckoo_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
											<td align="left"></td> \
										   </tr>');
		} else {
			$("table tbody.files").prepend(cloned_filters);
		}
	});
}

//==========================================================

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url_get_files: 'api.php?action=getfiles',
		url_get_cuckoo: 'api.php?action=getcuckoo',
		url_virustotal_scan: 'api.php?action=virustotalscan',
		url_cuckoo_scan: 'api.php?action=cuckooscan',
		url_delete_file: 'api.php?action=deletefile',
		url_update_file: 'api.php?action=updatefile',
		url_upload_files: 'api.php?action=uploadfiles',
    });

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );
	
	get_files(1);
});
