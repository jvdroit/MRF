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
	var date_flt_val 	= !document.getElementById("date-descr-input") 	? "" : document.getElementById("date-descr-input").value;
	var user_flt_val 	= !document.getElementById("uploader-descr-input") 	? "" : document.getElementById("uploader-descr-input").value;
	var comment_flt_val = !document.getElementById("comment-descr-input") ? "" : document.getElementById("comment-descr-input").value;
	var hash_flt_val 	= !document.getElementById("hash-descr-input") 	? "" : document.getElementById("hash-descr-input").value;
	var vendor_flt_val 	= !document.getElementById("vendor-descr-input") ? "" : document.getElementById("vendor-descr-input").value;
	var name_flt_val 	= !document.getElementById("name-descr-input") 	? "" : document.getElementById("name-descr-input").value;
	var size_flt_val 	= !document.getElementById("size-descr-input") 	? "" : document.getElementById("size-descr-input").value;
	var virustotal_flt_val = !document.getElementById("vt-descr-input") 	? "" : document.getElementById("vt-descr-input").value;
	var cuckoo_flt_val = !document.getElementById("cuckoo-descr-input") 	? "" : document.getElementById("cuckoo-descr-input").value;

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
		// old filters, DEPRECATED.
		/*if (!filters_exist){
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
		}*/
		
		// Refresh tooltips
		$('[data-toggle="tooltip"]').tooltip();
	});
}

//==========================================================

function UpdateBadge() {
    if (($('.template-upload').length) == 0)
			$("#btn-upload-all-badge").html('');
	else
		$("#btn-upload-all-badge").html(($('.template-upload').length).toString());	// we need to count the current element
}

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
	
	// display badge
	$('#fileupload').bind('fileuploadadded', function (e, data) {
		$("#alert").html('');
		UpdateBadge();
	});	
	$('#fileupload').bind('fileuploadfinished', function (e, data) {
		$("#alert").html('');
		UpdateBadge();
	});	
	$('#fileupload').bind('fileuploadfailed', function (e, data) {
		$("#alert").html('');		
		UpdateBadge();
	});
	
	// display error message
	$('#fileupload').bind('fileuploaddestroyfailed', function (e, data) {
		$("#alert").html('<div class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to remove the item, check your rights.</div>');
	});
	
	// remove error message
	$('#fileupload').bind('fileuploaddestroyed', function (e, data) {
		$("#alert").html('');
	});
	
	get_files(1);
});