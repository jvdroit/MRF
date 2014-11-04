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
function delayed_get_files()
{
	timer && clearTimeout(timer);
	timer = setTimeout(get_files, 700);
}

function get_files()
{	
	var date_flt_val 	= !document.getElementById("date_filter") 	? "" : document.getElementById("date_filter").value;
	var hash_flt_val 	= !document.getElementById("hash_filter") 	? "" : document.getElementById("hash_filter").value;
	var vendor_flt_val 	= !document.getElementById("vendor_filter") ? "" : document.getElementById("vendor_filter").value;
	var name_flt_val 	= !document.getElementById("name_filter") 	? "" : document.getElementById("name_filter").value;

	// Clear table
	$("table tbody.files").find("tr:gt(0)").remove();
	
	// Create filter inputs
	if ($("table tbody.files").find("input").length == 0) {
		$("table tbody.files").append('<tr> \
										<td align="left"><input type="text" id="date_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
										<td align="left"><input type="text" id="vendor_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
										<td align="left"><input type="text" id="hash_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
										<td align="left"><input type="text" id="name_filter" style="width:100%;" onkeyup="delayed_get_files()"></td> \
										<td align="left"></td> \
										<td align="left"></td> \
										<td align="left"></td> \
									   </tr>');
	}
		
	// Load existing files:
	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url'),
		dataType: 'json',
		context: $('#fileupload')[0],		
		data: {date: String(date_flt_val), hash: hash_flt_val, vendor: vendor_flt_val, name: name_flt_val},	
		type: 'get'
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done')
			.call(this, $.Event('done'), {result: result});
	});
}

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: 'server/php/'
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
	
	get_files();
});
