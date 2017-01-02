/* global $, window */
var timer;
var current_page = 1;
var current_file_index = 0;

// ==================================================
// Helpers

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

// ===================================================
// General AJAX functions

function update_vendor(hash_val, vendor_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=updatefile',
		dataType: 'json',	
		data: {hash: hash_val, vendor: vendor_val},	
		type: 'post',
		success: function() { 
            if (onSuccess) onSuccess();
		},
        error: function(xhr, textStatus, errorThrown){
            if (onFailure) onFailure();
        }
	});
}

function update_user(hash_val, user_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=updatefile',
		dataType: 'json',	
		data: {hash: hash_val, new_user: user_val},	
		type: 'post',
		success: function() { 
            if (onSuccess) onSuccess();
		},
        error: function(xhr, textStatus, errorThrown){
            if (onFailure) onFailure();
        }
	});
}

function update_comment(hash_val, comment_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=updatefile',
		dataType: 'json',	
		data: {hash: hash_val, comment: comment_val},	
		type: 'post',
		success: function() { 
            if (onSuccess) onSuccess();
		},
        error: function(xhr, textStatus, errorThrown){
            if (onFailure) onFailure();
        }
	});
}

function update_urls(hash_val, urls_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=updatefile',
		dataType: 'json',	
		data: {hash: hash_val, urls: urls_val},	
		type: 'post',
		success: function() { 
            if (onSuccess) onSuccess();
		},
        error: function(xhr, textStatus, errorThrown){
            if (onFailure) onFailure();
        }
	});
}

function start_virustotal_scan(hash_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=virustotalscan',
		data: {hash: hash_val},	
		type: 'post',				
		success: function() { 
			if (onSuccess) onSuccess();
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();            
        }
	});
}

function start_cuckoo_scan(hash_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=cuckooscan',	
		data: {hash: hash_val},	
		type: 'post',				
		success: function() { 
			if (onSuccess) onSuccess();            
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_cuckoo_infos(onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=getcuckoo',
		dataType: 'json',
		type: 'get',				
		success: function(data) { 
			if (onSuccess) onSuccess(data); 			
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function send_vt_comment(hash_val, comment_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=virustotalcomment',
		dataType: 'json',	
		data: {hash: hash_val, comment: comment_val},	
		type: 'post',				
		success: function() { 
			if (onSuccess) onSuccess(); 			
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_storage_info(onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=getstorageinfo',
		dataType: 'json',
		type: 'get',				
		success: function(data) { 
			if (onSuccess) onSuccess(data); 				
		},
		error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function add_favorite(hash_val, fav_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=updatefile',
		dataType: 'json',		
		data: {hash: hash_val, favorite: fav_val},	
		type: 'post',				
		success: function() { 
			if (onSuccess) onSuccess(); 			
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_file(hash_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=getfile',
		dataType: 'json',		
		data: {hash: hash_val},	
		type: 'get',		
		success: function(data) { 
			if (!data.file) {
				if (onFailure) onFailure();
			}
			else if (onSuccess) 
				onSuccess(data); 			
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function delete_file(url, method, onSuccess, onFailure)
{
	$.ajax({
		url: url,
		dataType: 'json',		
		type: method,		
		success: function(data) { 
			if (onSuccess) onSuccess(data); 			
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_users(onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=getusers',
		dataType: 'json',
		type: 'get',				
		success: function(data) { 
			if (onSuccess) onSuccess(data); 				
		},
		error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function start_pedata_scan(hash_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=pedatascan',	
		data: {hash: hash_val},	
		type: 'post',				
		success: function() { 
			if (onSuccess) onSuccess();            
		},
        error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_pedata(hash_val, onSuccess, onFailure)
{
	$.ajax({
		url: 'api.php?action=getpedata',
		dataType: 'json',
		data: {hash: hash_val},	
		type: 'get',				
		success: function(data) { 
			if (onSuccess) onSuccess(data); 				
		},
		error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});
}

function get_bulk_download(hashes, use_password, onSuccess, onFailure)
{
	// Ajax isn't able to trigger downloads
	/*$.ajax({
		url: 'api.php?action=bulkdownload',
		data: {files: hashes},	
		type: 'get',				
		success: function(data) { 
			if (onSuccess) onSuccess(data); 				
		},
		error: function(xhr, textStatus, errorThrown){
        	if (onFailure) onFailure();             
        }
	});*/
	var hashes_request = "";
	for (var i = 0 ; i < hashes.length; i++) {
		hashes_request += "&files[]=" + hashes[i];
	}
	window.location.assign('api.php?action=bulkdownload&use_password=' + (use_password ? "true" : "false") + hashes_request);
	onSuccess();
}

// ================================================
// Index Functions 

function bulk_download(use_password)
{
	var hashes = [];
	var selected = $('input[id^="select_"]:checkbox:checked');
	for (var i = 0 ; i < selected.length; i++) {			
		var checkbox = selected[i];
		var id = checkbox.id;
		var hash = id.substring("select_".length);	
		hashes.push(hash);
	}
    if (hashes.length) {
    	get_bulk_download(hashes, use_password,
			function() { /* On success */
				// PHP should trigger the download
			},
			function() { /* On failure */
				$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to download files.</div>');
			}
		);
    }
}

function delete_sample(hash_val, url, type, withCreds)
{
	$.ajax({
        url: url,
        type: type,
        success: function() { 
        	$("#row_"+hash_val).remove();
		},
		error: function(xhr, textStatus, errorThrown){
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to delete file.</div>'); 
        }
    });
}

// Calls a refresh 700 ms later
function delayed_get_files()
{
	timer && clearTimeout(timer);
	timer = setTimeout(get_files, 700, current_page);
}

// Called when files are gotten
function on_files_gotten(result)
{
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
				AjaxPush: 'api.php?action=updatefile',
				AjaxPushAllTags: true,
				AjaxPushParameters: {hash: name},	
			});
			
			var tags = result.files[i].tags.split(",");
			for (var j=0, tag; tag=tags[j]; j++)				
				tags_input.tagsManager('pushTag', tag, true);	// ignore events so we don't call AJAX while pushing here
				
			tags_input.hide();
		}
	}
	
	// Prevents top scrolling with clicking the buttons in dropdown-menu
    $('a[class^="menu-button-"').click(function(e) {
    	e.preventDefault();
    });		
    
    // Prevents dropdown menus to stay stuck behind the table                                     
    var dropdownMenu;                            
    $(window).on('show.bs.dropdown', function (e) 
    {
        // grab the menu        
        dropdownMenu = $(e.target).find('#dropdown-item-actions');
        var is_up = $(e.target).hasClass("dropup");

        // detach it and append it to the body
        $('body').append(dropdownMenu.detach());

        // grab the new offset position
        var eOffset = $(e.target).offset();

        // make sure to place it where it would normally go (this could be improved)
        dropdownMenu.css({
            'display': 'block',
            'top': is_up 
	            	? (eOffset.top - dropdownMenu.outerHeight()) 
	                : (eOffset.top + $(e.target).outerHeight()),
	        'left': eOffset.left + $(e.target).outerWidth() - dropdownMenu.outerWidth(),
	        'right': eOffset.left + $(e.target).outerWidth()
        });
    });

    // and when you hide it, reattach the drop down, and hide it normally                                                   
    $(window).on('hide.bs.dropdown', function (e) {
        $(e.target).append(dropdownMenu.detach());
        dropdownMenu.hide();
    });
}

function get_files(page_flt)
{	
	current_page = page_flt;

	// get filers values
	var fav_obj         = document.getElementById("fav-descr-input"); 
	var cuckoo_obj      = document.getElementById("cuckoo-descr-input"); 
	var date_flt_val 	= !document.getElementById("date-descr-input") 		? "" : document.getElementById("date-descr-input").value;
	var user_flt_val 	= !document.getElementById("uploader-descr-input") 	? "" : document.getElementById("uploader-descr-input").value;
	var comment_flt_val = !document.getElementById("comment-descr-input") 	? "" : document.getElementById("comment-descr-input").value;
	var hash_flt_val 	= !document.getElementById("hash-descr-input") 		? "" : document.getElementById("hash-descr-input").value;
	var vendor_flt_val 	= !document.getElementById("vendor-descr-input") 	? "" : document.getElementById("vendor-descr-input").value;
	var name_flt_val 	= !document.getElementById("name-descr-input") 		? "" : document.getElementById("name-descr-input").value;
	var size_flt_val 	= !document.getElementById("size-descr-input") 		? "" : document.getElementById("size-descr-input").value;
	var virustotal_flt_val = !document.getElementById("vt-descr-input") 	? "" : document.getElementById("vt-descr-input").value;
	var fav_flt_val     = !fav_obj 											? "" : fav_obj.options[fav_obj.selectedIndex].value;	
	var cuckoo_flt_val  = !cuckoo_obj 										? "" : cuckoo_obj.options[cuckoo_obj.selectedIndex].value;	
	var tags_flt_val 	= !document.getElementById("tags-descr-input") 		? "" : document.getElementById("tags-descr-input").value;
    var urls_flt_val 	= !document.getElementById("urls-descr-input") 		? "" : document.getElementById("urls-descr-input").value;
    
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
	if (date_flt_val) data_array["date"] 				= date_flt_val;	
	if (user_flt_val) data_array["user"] 				= user_flt_val;
	if (comment_flt_val) data_array["comment"] 			= comment_flt_val;	
	if (hash_flt_val) data_array["hash"] 				= hash_flt_val;	
	if (vendor_flt_val) data_array["vendor"] 			= vendor_flt_val;	
	if (name_flt_val) data_array["name"] 				= name_flt_val;	
	if (size_flt_val) data_array["size"] 				= size_flt_val;
	if (virustotal_flt_val) data_array["virustotal"] 	= virustotal_flt_val;
	if (cuckoo_flt_val) data_array["cuckoo"] 			= cuckoo_flt_val;
	if (fav_flt_val) data_array["favorite"] 			= fav_flt_val;
	if (tags_flt_val) data_array["tags"] 				= tags_flt_val;
    if (urls_flt_val) data_array["urls"] 				= urls_flt_val;	
	data_array["page"] 									= page_flt;
	
	// Load existing files:
	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: 'api.php?action=getfiles',
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: data_array,	
		type: 'get'
	}).fail(function () {
		// Log the error
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
		on_files_gotten(result);
	});
}

function clear_search() 
{
    if(document.getElementById("date-descr-input")) document.getElementById("date-descr-input").value 				= "";
	if(document.getElementById("uploader-descr-input")) document.getElementById("uploader-descr-input").value 		= "";
	if(document.getElementById("comment-descr-input")) document.getElementById("comment-descr-input").value 		= "";
	if(document.getElementById("hash-descr-input")) document.getElementById("hash-descr-input").value 				= "";
	if(document.getElementById("vendor-descr-input")) document.getElementById("vendor-descr-input").value 			= "";
	if(document.getElementById("name-descr-input")) document.getElementById("name-descr-input").value 				= "";
	if(document.getElementById("size-descr-input")) document.getElementById("size-descr-input").value 				= "";
	if(document.getElementById("vt-descr-input")) document.getElementById("vt-descr-input").value 					= "";
	if(document.getElementById("cuckoo-descr-input")) document.getElementById("cuckoo-descr-input").value 			= "none";
	if(document.getElementById("fav-descr-input")) document.getElementById("fav-descr-input").value 				= "none";
	if(document.getElementById("tags-descr-input")) document.getElementById("tags-descr-input").value 				= "";
    if(document.getElementById("urls-descr-input")) document.getElementById("urls-descr-input").value 				= "";
    get_files(current_page);
}

function update_upload_count() 
{
    if ($('.template-upload').length == 0)
		$("#btn-upload-all-badge").html('');
	else
		$("#btn-upload-all-badge").html($('.template-upload').length.toString());	// we need to count the current element
}

function add_file_upload_tags() 
{
	// https://maxfavilli.com/jquery-tag-manager
	$(".tm-input").tagsManager({
		hiddenTagListName: $(".tm-input").attr('id') + '_hidden',
		tagClass: 'myTag',
	});
}

//=========================================================
// Edit

function save_data(this_id)
{
	var but 			= $("#edit_" + this_id);		
	var img 			= $("#edit_img_" + this_id);
	var txt 			= $("#edit_text_" + this_id);	
	
	// turn save button back into edit
	but.removeClass("menu-button-save");		
	but.addClass("menu-button-edit");	
		
	// Turn edit button image		
	img.removeClass("glyphicon glyphicon-ok-circle");	
	img.addClass("glyphicon glyphicon-edit");
	
	// Turn edit button text
	txt.text("Edit");
	
	save_row_data(this_id, 'vendor');				
	save_row_data(this_id, 'comment');			
	
	var tags_input = $("#tags_" + this_id);
	if (tags_input) tags_input.hide();
}

function save_row_data(this_id, prefix)
{
	var span 			= $("span#" + prefix + "_" + this_id);	
	var area_edit 		= $("textarea#" + prefix + "_" + this_id);
	var staticText 		= $("<span id='" + prefix + "_" + this_id + "' class='label label-default'/>");	
	
	// set vendor field in ui
	var new_value = area_edit.val();	
	staticText.text(new_value);
	area_edit.replaceWith(staticText);	
	
	// Update database
	if (prefix == 'vendor') 		update_vendor(this_id, new_value);
	else if (prefix == 'comment')	update_comment(this_id, new_value);	
}

function edit_data(this_id)
{	
	var but 			= $("#edit_" + this_id);		
	var img 			= $("#edit_img_" + this_id);
	var txt 			= $("#edit_text_" + this_id);	
	
	// Save action	
	if (but.hasClass("menu-button-save")){
		return save_data(this_id);
	}	
	
	// Turn edit button into save	
	but.removeClass("menu-button-edit");
	but.addClass("menu-button-save");	
	
	// Turn edit button image
	img.removeClass("glyphicon glyphicon-edit");
	img.addClass("glyphicon glyphicon-ok-circle");
	
	// Turn edit button text
	txt.text("Save");	
	
	// Switch to edit mode
	edit_row_data(this_id, "vendor");	
	
	var tags_input = $("#tags_" + this_id);
	if (tags_input) tags_input.show();
	
	// Give focus
	var area_edit = $("textarea#vendor_" + this_id);
	area_edit.focus();
};

function edit_row_data(this_id, prefix)
{	
	var span 			= $("span#" + prefix + "_" + this_id);				
	var remaining 		= span.parent().width() - span.width() - 40;			
	var editableText 	= $("<textarea id='" + prefix + "_" + this_id + "' style='width: " + remaining.toString() + "px; height: 25px;'/>");	
	
	// change field into a textbox		
	var text = span.html();							
	editableText.val(text);
	editableText.attr('rows', '1');
	span.replaceWith(editableText);			
};

//=========================================================
// VT Comment

function vt_comment()
{
    var this_id         = $("#body_vt_comment").attr("data-id");
	var area_edit 		= $("#t_commentvt");
	var modal			= $("#commentVTModal");				
	var comment 		= area_edit.val();				
	if (comment != '') {
		send_vt_comment(this_id, comment);
	}
	modal.modal('hide');
}

//=========================================================
// Scans

function vt_scan(this_id)
{
	start_virustotal_scan(this_id, 
		function() { /* On success */
			// TODO, change vt link to open
	        var span = $("#vt_score_" + this_id);
	        var link = $("#vt_score_link_" + this_id);
	        span.removeClass("label label-success label-danger label-primary");
	        span.addClass("label label-primary");	
	        span.text("Scanning");
	        if(link) {
	            link.attr("href", "#");
	            link.attr("title", "VirusTotal score: Currently scanning...");
	        }
		},
		function() { /* On failure */
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to submit virustotal analysis.</div>');
		}
	);	
};

function cuckoo_scan(this_id)
{	
	start_cuckoo_scan(this_id, 
		function () { /* On success */
			// TODO, change cuckoo link to open
			var span = $("#ck_" + this_id);
	        var link = $("#ck_link" + this_id);
	        span.removeClass("label label-success label-danger label-primary");
	        span.addClass("label label-warning");	
	        span.text("Scanning");
	        if(link) {
	            link.attr("href", "#");
	        }
		},
		function () { /* On failure */
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to submit cuckoo scan.</div>');
		}
	);	
};

//=========================================================
// Favorite

function favorite(this_id) 
{
	var star = $("#fav_star_" + this_id);
	if (star.hasClass("glyphicon-star-empty")) {
		// add fav
		star.removeClass("glyphicon-star-empty");
		star.addClass("glyphicon-star");					
		add_favorite(this_id, true);	
	} else {
		// remove fav
		star.removeClass("glyphicon-star");
		star.addClass("glyphicon-star-empty");
		add_favorite(this_id, false);		
	}	
}

//=========================================================
// Urls

function modal_add_url_area() 
{
	var template = $('#urltemplate');
		clone    = template
                    .clone()
                    .removeClass('hide')
                    .removeAttr('id')
                    .attr('id', 'url_node')
                    .insertBefore(template);
}

function modal_remove_url_area(this_obj) 
{
	var row    = this_obj.parents("[id^=url_node]");
	
	// Remove element containing the option
	row.remove();
}

function send_urls(hash) 
{
	// Get all URLs in form of url1,url2,...
    
	var urls 		= '';
	var urls_obj 	= $("div[id^=url_node]");				
	for ( var i = 0, l = urls_obj.length; i < l; i++ ) 
	{
		var input_name 	= $(urls_obj[i]).find('input[id^=name_]');
		var name 		= input_name.val();		
		var input_url 	= $(urls_obj[i]).find('input[id^=url_]');
		var url 		= input_url.val();		
		if (url != '') {
			if (urls != '') urls +=',';
			urls += name + '|'+ url;
		}				
	}			
	// Update database
	update_urls(hash, urls);
}

function modal_send_urls()
{
	var this_id = $("#body_urls").attr("data-id");	
	send_urls(this_id);	
	$("#urls_" + this_id).attr("data-urls-value", urls);
	
	// Close modal
	var modal			= $("div#urlModal");
	modal.modal('hide');  
}

//=========================================================
// Sample initialization

function sample_delete()
{
	url 	= $("#sample-remove").attr("data-delete-url");
	type 	= $("#sample-remove").attr("data-delete-type");
	
	delete_file(url, type, 
		function() {
			$("#alert").html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-info-sign"></span> Sample Deleted.</div>');
		},
		function() {
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to delete sample.</div>');
		}		
	);
}

function sample_update(hash)
{
	var error = false;
	update_comment(hash, tinymce.get('t_comment').getContent(), null, function() { error = true; });
	update_vendor(hash, $("#sample-vendor").val(), null, function() { error = true; });
	update_user(hash, $("#sample-uploader").val(), null, function() { error = true; });
	send_urls(hash);
	
	if(!error) {
		$("#alert").html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-info-sign"></span> Information Updated.</div>');
	} else {
		$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to update information.</div>');
	}
}

function toggle_favorite(hash)
{
	favorite = $("#sample-fav").hasClass("fa-star-o");
	add_favorite(hash, favorite, function() {
		if(favorite === true) {
			$("#sample-fav").removeClass("fa-star-o");
			$("#sample-fav").addClass("fa-star");
		} else {
			$("#sample-fav").removeClass("fa-star");
			$("#sample-fav").addClass("fa-star-o");
		}
	});
}

function sample_vt_scan(hash)
{
	start_virustotal_scan(hash, 
		function() { /* On success */
			$("#alert").html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-info-sign"></span> Sample sent for analysis.</div>');
			sample_reload(hash, false);
		},
		function() { /* On failure */
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to send sample for analysis.</div>');
		}
	);	
}

function sample_cuckoo_scan(hash)
{
	start_cuckoo_scan(hash, 
		function() { /* On success */
			$("#alert").html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-info-sign"></span> Sample sent for analysis.</div>');
			sample_reload(hash, false);
		},
		function() { /* On failure */
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to send sample for analysis.</div>');
		}
	);	
}

function sample_pedata_scan(hash)
{
	start_pedata_scan(hash, 
		function() { /* On success */
			$("#alert").html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-info-sign"></span> Sample sent for PE data scan.</div>');
		},
		function() { /* On failure */
			$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to send sample for pe data analysis.</div>');
		}
	);	
}

function pedata_reload(hash)
{
	get_pedata(hash, function(data) {
		if (!data.valid) {
			$("#div-warnings").hide();
			$("#div-headers").hide();
			$("#alert").html('<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> PE Exception: ' + data.error + '</div>');
		} else {
			$("#pedata_content").val(JSON.stringify(data.data));
			
			//========================= Warnings ===============================
			if (!data.data["Parsing Warnings"] || data.data["Parsing Warnings"].length == 0)
				$("#div-warnings").hide();
			else {
				var arrayLength = data.data["Parsing Warnings"].length;
				$("#div-warnings-content").empty();
				
				var warnings_count = arrayLength;				
				$("#div-warnings-content").append('<li class="list-group-item list-group-item-header list-group-item-warning"><h4><span class="glyphicon glyphicon-exclamation-sign"></span> Warning(s) <span id="warnings-badge" class="badge pull-right badge-warning">0</span></h4></li>');
				for (var i = 0; i < arrayLength; i++) {
				    var warn_message = data.data["Parsing Warnings"][i];
				    var warnings_content = '<li class="list-group-item list-group-item-group">' + warn_message + '</li>';
				    $("#div-warnings-content").append(warnings_content);
				}			
				
				// Digisig warnings
				if (data.data["digisig"] && data.data["digisig"]["warnings"] && data.data["digisig"]["warnings"].length > 0) {
					arrayLength = data.data["digisig"]["warnings"].length;
					warnings_count += arrayLength;
					for (var i = 0; i < arrayLength; i++) {
					    var warn_message = data.data["digisig"]["warnings"][i];
					    var warnings_content = '<li class="list-group-item list-group-item-group">' + warn_message + '</li>';
					    $("#div-warnings-content").append(warnings_content);
					}
				}
				
				$("#warnings-badge").html(warnings_count.toString());
				$("#div-warnings").show();
			}
			
			//========================= DOS Header ===============================
			$("#dosheader tbody").remove();
			if (data.data["DOS_HEADER"]) {
				$.each(data.data["DOS_HEADER"], function(key, value){
					$("#dosheader").append("<tr><td>"+key+"</td><td>"+value.Value+"</td></tr>");
			    });
			}
			
			//========================= File Header ===============================
			$("#fileheader tbody").remove();
			if (data.data["FILE_HEADER"]) {
				$.each(data.data["FILE_HEADER"], function(key, value){
					$("#fileheader").append("<tr><td>"+key+"</td><td>"+value.Value+"</td></tr>");
			    });
			}
			
			//========================= Optional Header ===============================
			$("#optionalheader tbody").remove();
			if (data.data["OPTIONAL_HEADER"]) {
				$.each(data.data["OPTIONAL_HEADER"], function(key, value){
					$("#optionalheader").append("<tr><td>"+key+"</td><td>"+value.Value+"</td></tr>");
			    });
			}
			
			//========================= Sections ===============================
			$("#sections tbody").remove();
			if (data.data["PE Sections"]) {
				$("#sections").append("<tr><th>Index</th><th>Name</th><th>Virtual Address</th>Virtual Size<th>Raw Size</th><th>Entropy</th><th>MD5</th></tr>");
				$("#sections-badge").html(data.data["PE Sections"].length.toString());
				$.each(data.data["PE Sections"], function(index, value){
					var name = [];
					$.each(value.Name.Value, function(index2,value2) {   
						name.push(value2);
					});
					$("#sections").append("<tr><td>"+index+"</td><td>"
							+name.join("")+"</td>"
							+value.VirtualAddress.Value+"</td><td>"
							+value.Misc_VirtualSize.Value+"</td><td>"
							+value.SizeOfRawData.Value+"</td><td>"
							+value.Entropy+"</td><td>"
							+value.MD5+"</td></tr>");
			    });
			}
			
			//========================= Resources ===============================
			$("#resources tbody").remove();
			if (data.data["Resource directory"]) {
				$("#resources").append("<tr><th>Type</th><th>Id</th><th>Lang</th><th>Sublang</th><th>Size</th></tr>");
				$("#resources-badge").html(data.data["Resource directory"].length.toString());
				
				function iterateResourceDir(dir, parent_parent_id, parent_id) {
					var local_id = -1;
					$.each(dir, function(index, value){												
						if(value.Structure == "IMAGE_RESOURCE_DIRECTORY") {
							// Nothing to store
						} else if(value.Structure == "IMAGE_RESOURCE_DIRECTORY_ENTRY") {
							local_id = value.Id;
						} else if(value.Structure == "IMAGE_RESOURCE_DATA_ENTRY") {
							$("#resources").append("<tr><td>"+parent_parent_id+"</td><td>"
									+parent_id+"</td><td>"
									+value.LANG_NAME+"</td><td>"
									+value.SUBLANG_NAME+"</td><td>"
									+value.Size.Value+"</td></tr>");
						}
						if(value.constructor === Array) {
							iterateResourceDir(value, parent_id, local_id);
						}
				    });	
				}	
				iterateResourceDir(data.data["Resource directory"], -1, -1);
			}
			
			//========================= Strings ===============================
			$("#strings tbody").remove();
			if (data.data["strings"]) {
				$("#strings-badge").html(data.data["strings"].length.toString());
				$.each(data.data["strings"], function(idx, value){
					$("#strings").append("<tr><td>"+value+"</td></tr>");
			    });
			}
			
			//========================= Digisig ===============================
			if (data.data["digisig"]) {
				$("#digisig-badge").html(data.data["digisig"]["certificates"].length.toString());
				if (data.data["digisig"]["signed"]) {
					$("#digisig").append('<tr><td><strong>Signed: </strong></td><td colspan="3"><span class="label label-success">Signed</span></td></tr>');
				} else {
					$("#digisig").append('<tr><td><strong>Signed: </strong></td><td colspan="3"><span class="label label-danger">Not Signed</span></td></tr>');
				}
				if (data.data["digisig"]["verified"]) {
					$("#digisig").append('<tr><td><strong>Verified: </strong></td><td colspan="3"><span class="label label-success">Verified</span></td></tr>');
				} else {
					$("#digisig").append('<tr><td><strong>Verified: </strong></td><td colspan="3"><span class="label label-danger">Not Verified</span></td></tr>');
				}
				$.each(data.data["digisig"]["certificates"], function(idx, value){
					$("#digisig").append("<tr><td><strong>Certificate #"+idx+": </strong></td><td>"
							+value.OrganizationName+"<td><strong>From: </strong>"
							+value.StartValidity+"</td><td><strong>To: </strong>"
							+value.EndValidity+"</td></tr>");
			    });
			}
			
		}
	},
	function() {
		$("#div-warnings").hide();
		$("#div-headers").hide();
		$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to load data.</div>');
	});	
}

function sample_reload(hash, first_load)
{
	get_file(hash, function(data) {
		var sample_uploader = $("#sample-uploader");
		if (first_load) {
			get_users(function(users) {
				$.each(users, function() {
					sample_uploader.append($("<option/>").val(this.id).text(this.user_name));
				}, null);
				$("#sample-uploader").val(data.file.user);
			});	
		} else {
			$("#sample-uploader").val(data.file.user);
		}
		$("#sample-uploader-img").attr("src","data:image/png;base64," + data.file.user_avatar);
		if (data.file.icon.length > 0) {
			$("#sample-img").show();
			$("#sample-img").attr("src","data:image/png;base64," + data.file.icon);
		}
		else {
			$("#sample-img").hide();
		}
		$("#sample-name").val(data.file.filename);
		$("#sample-mime").val(data.file.mime);
		$("#sample-md5").val(data.file.name);
		$("#sample-sha256").val(data.file.sha256);
		$("#sample-ssdeep").val(data.file.ssdeep);
		$("#sample-date").val(data.file.timestamp);
		$("#sample-size").val(formatFileSize(data.file.size));
		$("#sample-download").attr("href", data.file.url);
		$("#sample-remove").attr("data-delete-url", data.file.deleteUrl);
		$("#sample-remove").attr("data-delete-type", data.file.deleteType);
		$("#sample-vendor").val(data.file.vendor);
		
		// VirusTotal
		if(data.file.scanned == 1) {
			$("#sample-vt-text").text('Score: ' + data.file.vtscore.toString() + '/55');
			$("#sample-vt").attr("href", data.file.vtlink);		
			$("#sample-vt-text").removeClass("label-success");
			if (data.file.vtscore < 10) {
				$("#sample-vt-text").addClass("label-success");
			} else if (data.file.vtscore >= 10 && data.file.vtscore < 20) {
				$("#sample-vt-text").addClass("label-warning");
			} else{
				$("#sample-vt-text").addClass("label-danger");
			}
		}
		else if(data.file.scanned == 0)	{
			$("#sample-vt-text").text('File Unknown');
			$("#sample-vt-text").removeClass("label-success");
			$("#sample-vt-text").addClass("label-warning");
			$("#sample-vt").removeAttr('href');
		}
		else if(data.file.scanned == -6)	{
			$("#sample-vt-text").text('Not Checked');
			$("#sample-vt-text").removeClass("label-success");
			$("#sample-vt-text").addClass("label-default");
			$("#sample-vt").removeAttr('href');
		}
		else if(data.file.scanned == -5)	$("#sample-vt-text").text('File Too Big');
		else if(data.file.scanned == -3)	$("#sample-vt-text").text('API Error');
		else if(data.file.scanned == -2)	$("#sample-vt-text").text('Scanning...');
		else 								$("#sample-vt-text").text('Error');
		
		if (data.file.scanned < 0 && data.file.scanned != -6) {
			$("#sample-vt-text").removeClass("label-success");
			$("#sample-vt-text").addClass("label-primary");
			$("#sample-vt").removeAttr('href');
		}
		
		// Cuckoo
		if(data.file.ck_scanned == 0) {
			$("#sample-cuckoo-text").text('Results');
			$("#sample-cuckoo").attr("href", data.file.cklink);		
			$("#sample-cuckoo-text").addClass("label-success");
		}
		else if(data.file.ck_scanned == -1)	{
			$("#sample-cuckoo-text").text('Scanning...');
			$("#sample-cuckoo-text").removeClass("label-success");
			$("#sample-cuckoo-text").addClass("label-warning");
			$("#sample-cuckoo").removeAttr('href');
		}
		else {
			$("#sample-cuckoo-text").text('None');
			$("#sample-cuckoo").removeAttr('href');
		}
		
		if (data.file.ck_scanned < -1) {
			$("#sample-cuckoo-text").removeClass("label-success");
			$("#sample-cuckoo-text").addClass("label-primary");
		}
		
		// Favorite
		if(data.file.favorite === true) {
			$("#sample-fav").removeClass("fa-star-o");
			$("#sample-fav").addClass("fa-star");
		}
		
		// Tags
		// https://maxfavilli.com/jquery-tag-manager
		if(first_load) {
			$(".tm-input").tagsManager({
				hiddenTagListName: 'hiddenTagList',
				tagClass: 'myTag',
				AjaxPush: 'api.php?action=updatefile',
				AjaxPushAllTags: true,
				AjaxPushParameters: {hash: hash},	
			});
		} else {
			$(".tm-input").tagsManager('empty');
		}		
		var tags = data.file.tags.split(",");
		for (var j=0, tag; tag=tags[j]; j++)				
			$(".tm-input").tagsManager('pushTag', tag, true);	// ignore events so we don't call AJAX while pushing here
		
		// Editor
		if (first_load) {
			tinymce.init({
				selector: '#t_comment',		
				plugins: [
				          'advlist autolink lists link image charmap print preview anchor',
				          'searchreplace visualblocks code fullscreen',
				          'insertdatetime media table contextmenu paste'
		        ],
				toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
				setup: function (ed) {
			        ed.on('init', function(args) {
			        	tinymce.get('t_comment').setContent(data.file.comment);
			        });
			    }
			});			
		} else {
			tinymce.get('t_comment').setContent(data.file.comment);
		}
		
		// URLs
		var first_url_value = $("input#url_first");
        var first_url_name  = $("input#name_first");
        
        // Cleanup first
        var rows = $("[id=url_node]");
        rows.remove();
        
        if (first_url_value) {
            var urls_array 	= data.file.urls.split(',');
            var first_url 	= urls_array[0];
            var key_val  	= first_url.split('|');
            var name_val    = key_val.length == 2 ? key_val[0] : '';
            var url_val     = key_val.length == 2 ? key_val[1] : first_url;
            
            if (first_url_name) first_url_name.val(name_val);
            first_url_value.val(url_val);
            
            var html = "";
            for (var url_count=1, url; url=urls_array[url_count]; url_count++) 
            {		
            	var key_val  	= url.split('|');
                var name_val    = key_val.length == 2 ? key_val[0] : '';
                var url_val     = key_val.length == 2 ? key_val[1] : first_url;
            	
                html += "\
                <div class='form-group' id='url_node'>\
                    <div class='col-xs-offset-2 col-xs-3'> \
                        <input class='form-control' type='text' id='name_next' placeholder='MyUrl' value='" + name_val + "' /> \
                    </div>\
					<div class='col-xs-5'>\
						<input class='form-control' type='text' id='url_next' placeholder='http://domain.tld' value='" + url_val + "' />\
					</div>\
                    <div class='col-xs-2'>\
                        <button type='button' class='btn btn-default' OnClick='modal_remove_url_area($(this));'>\
                            <i class='glyphicon glyphicon-minus'></i>\
                        </button>\
                    </div>\
                </div>\
                ";
            }
            $("#url_node_first").after( html );
            $("#body_urls").attr('data-id', hash);
        }
	},
	function() {
		$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> File not found.</div>');
	});
}

function initSample(hash)
{
	$('a[data-toggle="tab"]').on("shown.bs.tab", function(e) {
		$("#alert").html('');	// reset alert		
		var target = $(e.target).attr("href") // activated tab
		if(target == '#pedata-tab') {
			pedata_reload(hash);
		}
	});
	
	sample_reload(hash, true);
}

//=========================================================
// Repo initialization

function initRepo() 
{
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
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
		update_upload_count();	// update badge
		add_file_upload_tags();	// add tag to this item	
	});	
	// triggered when a file is uploaded
	$('#fileupload').bind('fileuploadfinished', function (e, data) {
		$("#alert").html('');
		update_upload_count();
	});	
	// triggered when a file failed to upload
	$('#fileupload').bind('fileuploadfailed', function (e, data) {
		$("#alert").html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to upload the file.</div>');
		update_upload_count();
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
    
    // Fill comment modal
    $("#commentModal").on("show.bs.modal", function(e) {  
    	var hash = $(e.relatedTarget).attr("data-id");
    	$("#body_comment").attr("data-id", hash);			// Save current ID    	
    	get_file(hash, function(data) {
    		var comment = data.file.comment;				// Fill with ID's comment
    		$(e.relatedTarget).attr("data-comment-value", comment); 
    		tinymce.get('p_comment').setContent(comment);
        }); 
    });	
    tinymce.init({
		selector: '#p_comment',		
		inline: true,
		plugins: [
		          'advlist autolink lists link image charmap print preview anchor',
		          'searchreplace visualblocks code fullscreen',
		          'insertdatetime media table contextmenu paste save'
        ],
		toolbar: 'save insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
		save_enablewhendirty: true,
	    save_onsavecallback: function() {
	    	var new_value = tinymce.get('p_comment').getContent();	// Get current comment
	    	var this_id = $("#body_comment").attr("data-id");		// Get current ID
	    	update_comment(this_id, new_value);						// Save ID's comment
        }
	});
    
    // Fill urls modal
    $("#urlModal").on("show.bs.modal", function(e) {
    	var hash = $(e.relatedTarget).attr("data-id");
    	get_file(hash, function(data) {
    		var urls = data.file.urls;
    		$(e.relatedTarget).attr("data-urls-value", urls);
    		
    		var first_url_value = $("input#url_first");
            var first_url_name  = $("input#name_first");
            
            // Cleanup first
            var rows = $("[id=url_node]");
            rows.remove();
            
            if (first_url_value) {
                var urls_array 	= urls.split(',');
                var first_url 	= urls_array[0];
                var key_val  	= first_url.split('|');
                var name_val    = key_val.length == 2 ? key_val[0] : '';
                var url_val     = key_val.length == 2 ? key_val[1] : first_url;
                
                if (first_url_name) first_url_name.val(name_val);
                first_url_value.val(url_val);
                
                var html = "";
                for (var url_count=1, url; url=urls_array[url_count]; url_count++) 
                {		
                	var key_val  	= url.split('|');
                    var name_val    = key_val.length == 2 ? key_val[0] : '';
                    var url_val     = key_val.length == 2 ? key_val[1] : first_url;
                	
                    html += "\
                    <div class='form-group' id='url_node'>\
                        <div class='col-xs-offset-2 col-xs-3'> \
                            <input class='form-control' type='text' id='name_next' placeholder='MyUrl' value='" + name_val + "' /> \
                        </div>\
    					<div class='col-xs-5'>\
    						<input class='form-control' type='text' id='url_next' placeholder='http://domain.tld' value='" + url_val + "' />\
    					</div>\
                        <div class='col-xs-2'>\
                            <button type='button' class='btn btn-default' OnClick='modal_remove_url_area($(this));'>\
                                <i class='glyphicon glyphicon-minus'></i>\
                            </button>\
                        </div>\
                    </div>\
                    ";
                }
                $("#url_node_first").after( html );
                $("#body_urls").attr('data-id', hash);
            }
        });  
    });
    
    // Fill comment vt modal
    $("#commentVTModal").on("show.bs.modal", function(e) {
        var hash        = $(e.relatedTarget).attr("data-id");
        $("#body_vt_comment").attr('data-id', hash);
    });	
    
    $('.pagination').jqPagination({
		link_string	: '/?page={page_number}',
		max_page	: 1,
		paged		: function(page) {
			get_files(page);
		}
	});
	
	get_cuckoo_infos(function(data) {
		var span_status	= document.getElementById("cuckoo-status");
		var href_status = document.getElementById("cuckoo-status-href");
		if (span_status && href_status) {
			span_status.innerHTML = "Cuckoo " + data["version"] + " [" + data["machines"]["total"] + " machine(s)]";
			href_status.setAttribute('href', data["browse_url"]);
			href_status.setAttribute('target', '_blank');
		}
	});
	get_storage_info(function(data) {
		var span_count	= document.getElementById("files-count");
		if (span_count) {
			span_count.innerHTML = data["count"] + " (" + formatFileSize(parseInt(data["total"])) + ")";
		}			
		$('.pagination').jqPagination('option', 'max_page', data['max_page']);
	});
}

