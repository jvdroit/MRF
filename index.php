<?php 
require_once(__DIR__."/src/config.php");
require_once(__DIR__."/src/lib/usercake/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
?> 

<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Malware Repository</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap styles -->
	<link rel="stylesheet" href="public/css/bootstrap.min.css">
	<!-- Generic page styles -->
	<link rel="stylesheet" href="public/css/style.css">
	<!-- blueimp Gallery styles -->
	<link rel="stylesheet" href="public/css/blueimp-gallery.min.css">
	<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
	<link rel="stylesheet" href="public/css/jquery.fileupload.css">
	<link rel="stylesheet" href="public/css/jquery.fileupload-ui.css">
	<!-- CSS adjustments for browsers with JavaScript disabled -->
	<noscript><link rel="stylesheet" href="public/css/jquery.fileupload-noscript.css"></noscript>
	<noscript><link rel="stylesheet" href="public/css/jquery.fileupload-ui-noscript.css"></noscript>
	<!-- jqPagination styles -->
	<link rel="stylesheet" href="public/css/jqpagination.css" />	
	<!-- tags -->
	<link rel="stylesheet" type="text/css" href="public/css/tagmanager.css" />	
</head>
<body>	
	<div class="container">		
		<br>
		<div id='nav'><?php include(__DIR__."/src/navbar.php"); ?></div>
		<div class="panel panel-info">
			<div class="panel-heading">Repository Information</div>
			<div class="panel-body">
				<?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) { ?>
				<span>Powered by Cuckoo Sandbox: </span>
				<a id="cuckoo-status-href" href="#" title="Cuckoo">
					<span id="cuckoo-status" style="font-weight: bold; color: black;"> Not available</span>
				</a>	
				<?php } ?>	
				<br>
				<span>Samples: </span>
				<span id="files-count" style="font-weight: bold; color: black;"> Not available</span>
			</div>
		</div>
		<!-- The file upload form used as target for the file upload widget -->
		<form id="fileupload" action="api.php?action=uploadfiles" method="POST" enctype="multipart/form-data">			
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#upload"><span class="glyphicon glyphicon-upload"></span> Upload</a></li>
				<li><a data-toggle="tab" href="#search"><span class="glyphicon glyphicon-search"></span> Search</a></li>
			</ul>
			<br>
			<div class="tab-content">
				<div id="upload" class="tab-pane fade in active">
					<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
					<div class="row fileupload-buttonbar">
						<div class="col-lg-7">
							<!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn btn-success fileinput-button">
								<i class="glyphicon glyphicon-plus"></i>
								<span>Add files...</span>
								<input type="file" name="files[]" multiple>
							</span>
							<button type="submit" class="btn btn-primary start">
								<i class="glyphicon glyphicon-upload"></i>
								<span>Start upload</span>
								<span class="badge" id="btn-upload-all-badge"></span>
							</button>
							<button type="reset" class="btn btn-warning cancel">
								<i class="glyphicon glyphicon-ban-circle"></i>
								<span>Cancel upload</span>
							</button>
							<button type="button" class="btn btn-danger delete">
								<i class="glyphicon glyphicon-trash"></i>
								<span>Delete</span>
							</button>
							<input type="checkbox" class="toggle">
							<!-- The global file processing state -->
							<span class="fileupload-process"></span>					
						</div>
						<!-- The global progress state -->
						<div class="col-lg-5 fileupload-progress fade">
							<!-- The global progress bar -->
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar progress-bar-info" style="width:0%;"></div>
							</div>
							<!-- The extended global progress state -->
							<div class="progress-extended">&nbsp;</div>
						</div>
					</div>
				</div>				
				<div id="search" class="tab-pane fade">	
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="uploader-descr"><span class="glyphicon glyphicon-user"></span> Uploader</span>
								<input type="text" id="uploader-descr-input" class="form-control" placeholder="some user" aria-describedby="uploader-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="date-descr"><span class="glyphicon glyphicon-time"></span> Date</span>
								<input type="text" id="date-descr-input" class="form-control" placeholder="2015-12-21" aria-describedby="date-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="vendor-descr"><span class="glyphicon glyphicon-glass"></span> Vendor</span>
								<input type="text" id="vendor-descr-input" class="form-control" placeholder="Tr.Zeus" aria-describedby="vendor-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="comment-descr"><span class="glyphicon glyphicon-pencil"></span> Comment</span>
								<input type="text" id="comment-descr-input" class="form-control" placeholder="some comment" aria-describedby="comment-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="hash-descr"><span class="glyphicon glyphicon-map-marker"></span> MD5</span>
								<input type="text" id="hash-descr-input" class="form-control" placeholder="ba35799770abde5da0315e60694ce42e" aria-describedby="hash-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="name-descr"><span class="glyphicon glyphicon-file"></span> Filename</span>
								<input type="text" id="name-descr-input" class="form-control" placeholder="filename.exe" aria-describedby="name-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>	
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="vt-descr"><span class="glyphicon glyphicon-eye-open"></span> VirusTotal</span>
								<input type="text" id="vt-descr-input" class="form-control" placeholder=">10" aria-describedby="vt-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="cuckoo-descr"><span class="glyphicon glyphicon-fire"></span> Cuckoo</span>
								<input type="text" id="cuckoo-descr-input" class="form-control" placeholder="Results" aria-describedby="cuckoo-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>	
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="size-descr"><span class="glyphicon glyphicon-signal"></span> Size</span>
								<input type="text" id="size-descr-input" class="form-control" placeholder=">1000" aria-describedby="size-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="fav-descr"><span class="glyphicon glyphicon-star"></span> Favorite</span>
    							<input type="text" id="fav-descr-input" class="form-control" placeholder="any value to select favorite files" aria-describedby="fav-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>	
					<div class="row" style="padding-bottom: 10px">
						<div class="control-group col col-lg-4">
							<div class="input-group">
								<span class="input-group-addon" id="tags-descr"><span class="glyphicon glyphicon-tags"></span> Tags</span>
								<input type="text" id="tags-descr-input" class="form-control" placeholder="exploit" aria-describedby="tags-descr" onkeyup="delayed_get_files()">
							</div>
						</div>
					</div>					
				</div>
			</div>	
			<div id='alert'></div>			
			<div class="pagination">
				<a href="#" class="first" data-action="first">&laquo;</a>
				<a href="#" class="previous" data-action="previous">&lsaquo;</a>
				<input type="text" readonly="readonly" data-max-page="40" />
				<a href="#" class="next" data-action="next">&rsaquo;</a>
				<a href="#" class="last" data-action="last">&raquo;</a>
			</div>			
			<!-- The table listing the files available for upload/download -->
			<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
		</form>
	</div>
	
	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="name">{%=file.name%}</span>
			</td>
			<!--<td>
				<strong class="error text-danger"></strong>
			</td>-->
			<td>
				<span class="size">Processing...</span>
			</td>
			<td colspan=2>
				<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="min-width: 50px;">
					<div class="progress-bar progress-bar-info"></div>
				</div>
			</td>
			<td>
				<div class="checkbox">
					<label class="checkbox-inline">
						<input type="checkbox" id="vtsubmit_{%=file.index%}" value="" <?php if ($GLOBALS["config"]["virustotal"]["automatic_upload"]) { ?> checked <?php } ?> >
						<span class="label label-warning">VirusTotal</span>
					</label>
					<?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) { ?>
					<label class="checkbox-inline">
						<input type="checkbox" id="cuckoosubmit_{%=file.index%}" value="">
						<span class="label label-info">Cuckoo</span>
					</label>
					<?php } ?>
				</div>
			</td>
			<td>
				<input type="text" id="tags_upload_{%=file.index%}" placeholder="add tag..." class="tm-input tm-input-success tm-input-small"/>
			</td>
			<td>
				{% if (!i && !o.options.autoUpload) { %}
					<button class="btn btn-primary btn-sm start" disabled>
						<i class="glyphicon glyphicon-upload"></i>
						<span>Start</span>
					</button>
				{% } %}
				{% if (!i) { %}
					<button class="btn btn-warning btn-sm cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>Cancel</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
	</script>
	
	<script type="text/javascript">	
		function Save(this_id){
			var but 			= $("#edit_" + this_id);		
			var img 			= $("i#edit_img_" + this_id);
			var txt 			= $("span#edit_text_" + this_id);	
			
			// turn save button back into edit
			but.removeClass("btn btn-success edit");		
			but.addClass("btn btn-warning edit");	
				
			// Turn edit button image		
			img.removeClass("glyphicon glyphicon-ok-circle");	
			img.addClass("glyphicon glyphicon-edit");
			
			// Turn edit button text
			txt.text("Edit");
			
			SaveRow(this_id, 'vendor');				
			SaveRow(this_id, 'comment');			
			
			var tags_input = $("#tags_" + this_id);
			if (tags_input) tags_input.hide();
		}
		
		function SaveRow(this_id, prefix){
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
		
		function Edit(this_id){	
			var but 			= $("#edit_" + this_id);		
			var img 			= $("i#edit_img_" + this_id);
			var txt 			= $("span#edit_text_" + this_id);	
			
			// Save action	
			if (but.hasClass("btn btn-success edit")){
				return Save(this_id);
			}	
			
			// Turn edit button into save	
			but.removeClass("btn btn-warning edit");
			but.addClass("btn btn-success edit");	
			
			// Turn edit button image
			img.removeClass("glyphicon glyphicon-edit");
			img.addClass("glyphicon glyphicon-ok-circle");
			
			// Turn edit button text
			txt.text("Save");	
			
			// Switch to edit mode
			EditRow(this_id, "vendor");	
			EditRow(this_id, "comment");
			
			var tags_input = $("#tags_" + this_id);
			if (tags_input) tags_input.show();
		};
		
		function EditRow(this_id, prefix){	
			var span 			= $("span#" + prefix + "_" + this_id);				
			var remaining 		= span.parent().width() - span.width() - 40;			
			var editableText 	= $("<textarea id='" + prefix + "_" + this_id + "' style='width: " + remaining.toString() + "px; height: 25px;'/>");	
			
			// change field into a textbox		
			var text = span.html();							
			editableText.val(text);
			editableText.attr('rows', '1');
			span.replaceWith(editableText);			
		};
		
		function VTScan(this_id){			
			start_virustotal_scan(this_id);	
			
			var but = $("#vt_scan_" + this_id);		
			but.removeClass("btn btn-info btn-xs vtscan");
			but.addClass("btn btn-success btn-xs vtscan");	
			
			var txt = $("span#vt_scan_text_" + this_id);	
			txt.text("Sent");	
		};
		
		function CuckooScan(this_id){			
			start_cuckoo_scan(this_id);	
			
			var but = $("#ck_scan_" + this_id);		
			but.removeClass("btn btn-info btn-xs vtscan");
			but.addClass("btn btn-success btn-xs vtscan");	
			
			var txt = $("span#ck_scan_text_" + this_id);	
			txt.text("Sent");	
		};
		
		function Favorite(this_id) {
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
	</script>
	
	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
	<!--<tr>
		<th>Uploader</th>
		<th>Date</th>
		<th>Vendor</th>
		<th>Comment</th>
		<th>Hash</th>
		<th>Filename</th>
		<th>Size</th>
		<th>VirusTotal</th>
		<?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) { ?>
		<th>Cuckoo</th>
		<?php } ?>
		<th>Delete</th>
  	</tr>-->			
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
			<td>
				{% if (file.deleteUrl) { %}
					<input type="checkbox" name="delete" value="1" class="toggle" style="vertical-align: middle;">
				{% } %}
				<a href="#more_{%=file.name%}" data-toggle="collapse"><span class="glyphicon glyphicon-plus" style="font-size: 1em; vertical-align: middle;"></span></a>
				{% if (file.favorite) { %}
					<a href="#fav_{%=file.name%}" OnClick="Favorite('{%=file.name%}')" data-toggle="tooltip" title="Favorite"><span id="fav_star_{%=file.name%}" class="glyphicon glyphicon-star" style="font-size: 1.5em; vertical-align: middle;"></span></a>
				{% } else { %}
					<a href="#fav_{%=file.name%}" OnClick="Favorite('{%=file.name%}')" data-toggle="tooltip" title="Favorite"><span id="fav_star_{%=file.name%}" class="glyphicon glyphicon-star-empty" style="font-size: 1.5em; vertical-align: middle;"></span></a>
				{% } %}	
				<img alt="" height="24px" width="24px" class="img-circle" src="data:image/png;base64,{%=file.user_avatar%}" data-toggle="tooltip" title="Uploader: {%=file.user_name%}">
			</td>
			<td>
				<span class="name" data-toggle="tooltip" title="Upload date">{%=file.timestamp%}</span>
			</td>
			<td style="min-width: 100px;">
				{% if (file.vendor.toLowerCase().indexOf("exploit") != -1) { %}
					<span id="vendor_{%=file.name%}" data-toggle="tooltip" title="Vendor Name: {%=file.vendor%}" class="label label-primary">{%=file.vendor%}</span>
				{% } else if (file.vendor.toLowerCase().indexOf("pup") != -1 || file.vendor.toLowerCase().indexOf("not-a-virus") != -1) { %}
					<span id="vendor_{%=file.name%}" data-toggle="tooltip" title="Vendor Name: {%=file.vendor%}" class="label label-warning">{%=file.vendor%}</span>
				{% } else if (file.vendor.toLowerCase().indexOf("rootkit") != -1 || file.vendor.toLowerCase().indexOf("trojan") != -1) { %}
					<span id="vendor_{%=file.name%}" data-toggle="tooltip" title="Vendor Name: {%=file.vendor%}" class="label label-danger">{%=file.vendor%}</span>
				{% } else { %}					
					<span id="vendor_{%=file.name%}" data-toggle="tooltip" title="Vendor Name: {%=file.vendor%}" class="label label-default">{%=file.vendor%}</span>
				{% } %}	
			</td>			
			<td>
				<span class="name">
					{% if (file.url) { %}
						<a href="{%=file.url%}" data-toggle="tooltip" title="MD5: {%=file.name%}" download="{%=file.name%}">{%=file.name%}</a>
					{% } else { %}
						<span>{%=file.name%}</span>
					{% } %}
				</span>
				{% if (file.error) { %}
					<div><span class="label label-danger">Error</span> {%=file.error%}</div>
				{% } %}
			</td>
			<td>
				<span data-toggle="tooltip" title="File name: {%=file.filename%}" class="name">{%=file.filename.substring(0, 50)%}</span>
			</td>
			<td>
				<span data-toggle="tooltip" title="File size: {%=o.formatFileSize(file.size)%}" class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<td>
				{% if (file.scanned == 1 || file.scanned == 0) { %}
				<button id="vt_scan_{%=file.name%}" type="button" class="btn btn-info btn-xs vtscan" OnClick="VTScan('{%=file.name%}');">
					<i class="glyphicon glyphicon-upload"></i>
					<span id="vt_scan_text_{%=file.name%}">VirusTotal</span>
				</button>
				&nbsp;
				{% } %}
				{% if (file.scanned == 1) { %}
					{% if (file.vtscore < 10) { %}
						<a href="{%=file.vtlink%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" style="color: green;"><span class="label label-success">VT: {%=file.vtscore%}/55</span></a>			
					{% } else if (file.vtscore >= 10 && file.vtscore < 20) { %}
						<a href="{%=file.vtlink%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" style="color: orange;"><span class="label label-warning">VT: {%=file.vtscore%}/55</span></a>	
					{% } else { %}
						<a href="{%=file.vtlink%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" style="color: red;"><span class="label label-danger">VT: {%=file.vtscore%}/55</span></a>	
					{% } %}
				{% } else if (file.scanned == 0) { %}
					<span data-toggle="tooltip" title="VirusTotal score: File unknown" class="label label-default">VT: Unknown</span>
				{% } else if (file.scanned == -5) { %}
					<span data-toggle="tooltip" title="VirusTotal score: File too big" class="label label-primary">VT: File too big</span>
				{% } else if (file.scanned == -3) { %}
					<span data-toggle="tooltip" title="VirusTotal score: API limit reached" class="label label-primary">VT: API limit reached</span>
				{% } else if (file.scanned == -2) { %}
					<a href="{%=file.vtlink%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: Currently scanning..."><span class="label label-primary">VT: Scanning</span></a>
				{% } else { %}
					<span data-toggle="tooltip" title="VirusTotal score: Error" class="label label-primary">VT: Error</span>
				{% } %}			
			</td>
			<td>
				<button id="edit_{%=file.name%}" data-id="{%=file.name%}" type="button" class="btn btn-warning btn-xs edit" OnClick="Edit('{%=file.name%}');">
					<i id="edit_img_{%=file.name%}" class="glyphicon glyphicon-edit"></i>
					<span id="edit_text_{%=file.name%}">Edit</span>
				</button>
				{% if (file.deleteUrl) { %}
					<button id="delete_{%=file.name%}" class="btn btn-danger btn-xs delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
						<i class="glyphicon glyphicon-trash"></i>
						<span>Delete</span>
					</button>					
				{% } %}					
			</td>
		</tr>
		<tr id="more_{%=file.name%}" class="collapse in">
			<td colspan=42>
				<div class="panel panel-info">
				<div class="panel-body form-group" style="margin-bottom: 0px;">
				
					<div class="row">
						<div class="col-md-2">
							<label>Comment:</label>
							<span id="comment_{%=file.name%}" data-toggle="tooltip" title="Comment: {%=file.comment%}" class="label label-default">{%=file.comment%}</span>							
						</div>
						<div class="col-md-2">
							<label>Tags:</label>
							<input type="text" id="tags_{%=file.name%}" placeholder="add tag..." class="tm-input tm-input-small"/>							
							<!--{% var tags=file.tags.split(",",5); %}
							{% for (var j=0, tag; tag=tags[j]; j++) { %}
							<span class="label label-primary" data-toggle="tooltip" title="Tags">{%=tag%}</span>
							{% } %}-->
						</div>
						<?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) {	 ?>
						<div class="col-md-2">
							{% if (file.ck_scanned == -2) { %}
								<button id="ck_scan_{%=file.name%}" type="button" class="btn btn-info btn-xs ckscan" OnClick="CuckooScan('{%=file.name%}');">
									<i class="glyphicon glyphicon-upload"></i>
									<span id="ck_scan_text_{%=file.name%}">Send</span>
								</button>
								&nbsp;
							{% } %}	
							{% if (file.ck_scanned == 0 && file.cklink ) { %}
								<a href="{%=file.cklink%}" target="_blank" data-toggle="tooltip" title="Cuckoo results" style="font-weight: bold; color: green;"><span class="label label-success">Cuckoo: Results</span></a>
							{% } else if (file.ck_scanned == -1) { %}
								<span data-toggle="tooltip" title="Cuckoo Results" class="label label-warning">Cuckoo: Scanning</span>
							{% } else { %}
								<span data-toggle="tooltip" title="Cuckoo Results" class="label label-primary">Cuckoo: No report</span>
							{% } %}														
						</div>
						<?php } ?>
					</div>
					
				</div>
				</div>
			</td>
		</tr>
	{% } %}	
	</script>
	<script src="public/js/vendor/jquery.min.js"></script>
	<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
	<script src="public/js/vendor/jquery.ui.widget.js"></script>
	<!-- The Templates plugin is included to render the upload/download listings -->
	<script src="public/js/tmpl.min.js"></script>
	<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
	<script src="public/js/load-image.all.min.js"></script>
	<!-- The Canvas to Blob plugin is included for image resizing functionality -->
	<script src="public/js/canvas-to-blob.min.js"></script>
	<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
	<script src="public/js/bootstrap.min.js"></script>
	<!-- blueimp Gallery script -->
	<script src="public/js/jquery.blueimp-gallery.min.js"></script>
	<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
	<script src="public/js/jquery.iframe-transport.js"></script>
	<!-- The basic File Upload plugin -->
	<script src="public/js/jquery.fileupload.js"></script>
	<!-- The File Upload processing plugin -->
	<script src="public/js/jquery.fileupload-process.js"></script>
	<!-- The File Upload image preview & resize plugin -->
	<script src="public/js/jquery.fileupload-image.js"></script>
	<!-- The File Upload audio preview plugin -->
	<script src="public/js/jquery.fileupload-audio.js"></script>
	<!-- The File Upload video preview plugin -->
	<script src="public/js/jquery.fileupload-video.js"></script>
	<!-- The File Upload validation plugin -->
	<script src="public/js/jquery.fileupload-validate.js"></script>
	<!-- The File Upload user interface plugin -->
	<script src="public/js/jquery.fileupload-ui.js"></script>
	<!-- The main application script -->
	<script src="public/js/main.js"></script>
	<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
	<!--[if (gte IE 8)&(lt IE 10)]>
	<script src="js/cors/jquery.xdr-transport.js"></script>
	<![endif]-->
    </script>	
	
	<!-- jqPagination scripts -->
	<script src="public/js/jquery.jqpagination.js"></script>
	<script src="public/js/scripts.js"></script>
	<!-- tags -->
	<script type="text/javascript" src="public/js/tagmanager.js"></script>
</body> 
</html>
