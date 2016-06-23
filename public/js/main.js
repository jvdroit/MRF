/* global $, window */
var timer;
var current_page = 1;
var current_file_index = 0;

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

function update_urls(hash_flt_val, urls_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_update_file'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val, urls: urls_flt_val},	
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

function send_vt_comment(hash_flt_val, comment_flt_val)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_virustotal_comment'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val, comment: comment_flt_val},	
		type: 'post'
	});
}

function formatFileSize(bytes) 
{
	if (typeof bytes !== 'number') {
		return '';
	}
	if (bytes >= 1000000000) {
		return (bytes / 1000000000).toFixed(2) + ' GB';
	}
	if (bytes >= 1000000) {
		return (bytes / 1000000).toFixed(2) + ' MB';
	}
	return (bytes / 1000).toFixed(2) + ' KB';
};

function get_storage_info()
{
	$.ajax({
		url: $('#fileupload').fileupload('option', 'url_get_storage_info'),
		dataType: 'json',
		context: $('#fileupload')[0],	
		type: 'get',				
		success: function(data) { 
			var span_count	= document.getElementById("files-count");
			if (span_count) {
				span_count.innerHTML = data["count"] + " (" + formatFileSize(parseInt(data["total"])) + ")";
			}			
			$('.pagination').jqPagination('option', 'max_page', data['max_page']);
		},
		cache: false
	});
}

function add_favorite(hash_flt_val, fav)
{
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url_update_file'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {hash: hash_flt_val, favorite: fav},	
		type: 'post'
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
	var fav_flt_val 	= !document.getElementById("fav-descr-input") 	? "" : document.getElementById("fav-descr-input").value;
	var tags_flt_val 	= !document.getElementById("tags-descr-input") 	? "" : document.getElementById("tags-descr-input").value;

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
	if (fav_flt_val) data_array["favorite"] = fav_flt_val;
	if (tags_flt_val) data_array["tags"] = tags_flt_val;	
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
				
		// Refresh tooltips
		$('[data-toggle="tooltip"]').tooltip();
		
		// Refresh tags, add Ajax call for real time modification
		for (var i = 0 ; i < result.files.length; i++) {			
			var name = result.files[i].name;
			var tags_input = $("#tags_" + name);
			if (tags_input) {				
				tags_input.tagsManager({
					hiddenTagListName: tags_input.attr('id') + '_hidden',
					tagClass: 'myTag',
					AjaxPush: $('#fileupload').fileupload('option', 'url_update_file'),
					AjaxPushAllTags: true,
					AjaxPushParameters: {hash: name},	
				});
				
				var tags = result.files[i].tags.split(",");
				for (var j=0, tag; tag=tags[j]; j++)				
					tags_input.tagsManager('pushTag', tag, true);	// ignore events so we don't call AJAX while pushing here
					
				tags_input.hide();
			}
		}
	});
}

//==========================================================

function UpdateBadge() {
    if (($('.template-upload').length) == 0)
			$("#btn-upload-all-badge").html('');
	else
		$("#btn-upload-all-badge").html(($('.template-upload').length).toString());	// we need to count the current element
}

function AddTags() {
	//https://maxfavilli.com/jquery-tag-manager
	$(".tm-input").tagsManager({
		hiddenTagListName: $(".tm-input").attr('id') + '_hidden',
		tagClass: 'myTag',
	});
}

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url_get_files: 'api.php?action=getfiles',
		url_get_cuckoo: 'api.php?action=getcuckoo',
		url_get_storage_info: 'api.php?action=getstorageinfo',
		url_virustotal_scan: 'api.php?action=virustotalscan',
		url_cuckoo_scan: 'api.php?action=cuckooscan',
		url_delete_file: 'api.php?action=deletefile',
		url_update_file: 'api.php?action=updatefile',
		url_upload_files: 'api.php?action=uploadfiles',
		url_virustotal_comment: 'api.php?action=virustotalcomment',
    });
	
	// triggered before the file is added to the UI
	$('#fileupload').bind('fileuploadadd', function (e, data) {	
		for (var i = 0 ; i < data.files.length; i++) {
			data.files[i].index = current_file_index++;	// tag the file with a new index
		}
	});	
	// triggered when a file is added to the UI
	$('#fileupload').bind('fileuploadadded', function (e, data) {
		$("#alert").html('');	// reset alert		
		UpdateBadge();			// update badge
		AddTags();				// add tag to this item	
	});	
	// triggered when a file is uploaded
	$('#fileupload').bind('fileuploadfinished', function (e, data) {
		$("#alert").html('');
		UpdateBadge();
	});	
	// triggered when a file failed to upload
	$('#fileupload').bind('fileuploadfailed', function (e, data) {
		$("#alert").html('<div class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to the file.</div>');
		UpdateBadge();
	});	
	// triggered before a file is submited
	$('#fileupload').bind('fileuploadsubmit', function (e, data) {
		for (var i = 0 ; i < data.files.length; i++) {
			var index = data.files[i].index;
			
			// VirusTotal checkbox
			var vt_submit_input 	= $("#vtsubmit_" + index.toString());	
			if (vt_submit_input && vt_submit_input.is(':checked') == true) {
				data.files[i].vtsubmit = true;
			} else {
				data.files[i].vtsubmit = false;
			}
			
			// Cuckoo checkbox
			var ck_submit_input 	= $("#cuckoosubmit_" + index.toString());	
			if (ck_submit_input && ck_submit_input.is(':checked') == true) {
				data.files[i].cksubmit = true;
			} else {
				data.files[i].cksubmit = false;
			}
			
			// Tags
			var tags_input = $("input[name=tags_upload_" + index.toString() + "_hidden]");	
			if (tags_input) {
				data.files[i].tags = tags_input.val();
			}
			
			// URLs (empty for now)
			data.files[i].urls = '';
			
			// modify index to reflect the current form
			data.files[i].index = i;
			
			// attach form data
			data.formData = {
				files_data: JSON.stringify(data.files)
			}
		}
	});	
	// display error message
	$('#fileupload').bind('fileuploaddestroyfailed', function (e, data) {
		$("#alert").html('<div class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to remove the item, check your rights.</div>');
	});
	// remove error message
	$('#fileupload').bind('fileuploaddestroyed', function (e, data) {
		$("#alert").html('');
		
		// remove more data row
		var btn_id = data.context.prevObject.attr('id');
		$("tr#more_" + btn_id.substring(7)).remove();
	});
});

