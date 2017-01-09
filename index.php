<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->

<?php 
  require_once(__DIR__."/src/lib/usercake/models/config.php");
  if (!securePage($_SERVER['PHP_SELF'])){die();}
?> 

<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $websiteName ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="plugins/ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect.
  -->
  <link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">

  <!-- Generic page styles -->
  <link rel="stylesheet" href="plugins/jQueryUpload/css/style.css">
  <!-- blueimp Gallery styles -->
  <link rel="stylesheet" href="plugins/jQueryUpload/css/blueimp-gallery.min.css">
  <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
  <link rel="stylesheet" href="plugins/jQueryUpload/css/jquery.fileupload.css">
  <link rel="stylesheet" href="plugins/jQueryUpload/css/jquery.fileupload-ui.css">
  <!-- CSS adjustments for browsers with JavaScript disabled -->
  <noscript><link rel="stylesheet" href="plugins/jQueryUpload/css/jquery.fileupload-noscript.css"></noscript>
  <noscript><link rel="stylesheet" href="plugins/jQueryUpload/css/jquery.fileupload-ui-noscript.css"></noscript>
  <!-- jqPagination styles -->
  <link rel="stylesheet" href="plugins/jQueryUpload/css/jqpagination.css" />	
  <!-- tags -->
  <link rel="stylesheet" type="text/css" href="plugins/jQueryUpload/css/tagmanager.css" />
  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  
  <style type="text/css">
    .table-responsive {
		min-height: 400px !important;
	}
	
	ul#dropdown-item-actions,
	ul#dropdown-item-actions {
	    z-index: 10000;
	}
  </style>
  
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php  include(__DIR__."/top-nav.php"); ?> 
  <?php  include(__DIR__."/left-nav.php"); ?> 
  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!--<section class="content-header">
      <h1>
        Page Header
        <small>Optional description</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol>
    </section>-->

    <!-- Main content -->
    <section class="content">

	<!-- Your Page Content Here -->
	<div class="panel panel-info">
		<div class="panel-heading">Repository Information</div>
		<div class="panel-body">
			<span style="font-weight: bold; color: black;">MRF v<?php echo $GLOBALS["config"]["version"]?></span>
			<br/>
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
						<div class="btn-group">
						  <button type="button" class="btn btn-default">Download</button>
						  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						    <span class="caret"></span>
						    <span class="sr-only">Toggle Dropdown</span>
						  </button>
						  <ul class="dropdown-menu">
						    <li>
						    	<a href="#" class="menu-button-download" OnClick="bulk_download(false)">
									<i class="glyphicon glyphicon-download"></i>
									<span id="edit_text_{%=file.name%}">Download ZIP</span>
								</a>
							</li>
							<!-- Disabled until password protected is implemented -->
							<li hidden="true">
						    	<a href="#" class="menu-button-download-pw" OnClick="bulk_download(true)">
									<i class="glyphicon glyphicon-download"></i>
									<span id="edit_text_{%=file.name%}">Download ZIP (Pass: malware)</span>
								</a>
							</li>
						  </ul>
						</div>
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
						<span class="btn btn-default fileinput-button" OnClick="clear_search()">
							<i class="glyphicon glyphicon-ban-circle"></i>
							<span>Clear</span>
						</span>
					</div>
				</div>
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
							<span class="input-group-addon" id="vendor-descr"><span class="glyphicon glyphicon-glass"></span> Threat Name</span>
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
							<select id="cuckoo-descr-input" aria-describedby="cuckoo-descr" class="selectpicker form-control" data-live-search="true" onchange="delayed_get_files()">
								<option value="none"></option>
								<option value="no-results">No Results</option>
							    <option value="results">Results</option>
							    <option value="scanning">Scanning</option>
							</select>
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
							<select id="fav-descr-input" aria-describedby="fav-descr" class="selectpicker form-control" data-live-search="true" onchange="delayed_get_files()">
								<option value="none"></option>
							    <option value="no-fav">No</option>
							    <option value="fav">Yes</option>
							</select>
						</div>
					</div>
				</div>	
				<div class="row" style="padding-bottom: 10px">
					<div class="control-group col col-lg-4">
						<div class="input-group">
							<span class="input-group-addon" id="tags-descr"><span class="glyphicon glyphicon-tags"></span> Tags</span>
							<input type="text" id="tags-descr-input" class="form-control" placeholder="tag" aria-describedby="tags-descr" onkeyup="delayed_get_files()">
						</div>
					</div>
                    <div class="control-group col col-lg-4">
						<div class="input-group">
							<span class="input-group-addon" id="tags-descr"><span class="glyphicon glyphicon-globe"></span> URLs</span>
							<input type="text" id="urls-descr-input" class="form-control" placeholder="url" aria-describedby="tags-descr" onkeyup="delayed_get_files()">
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
		<div class="table-responsive">
            <table role="presentation" class="table table-hover table-striped">
                <!--<thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>Date</th>
                        <th>Threat Name</th>
                        <th>Hash MD5</th>
                        <th>Filename</th>
                        <th>Size</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>-->
                <tbody class="files"></tbody>
            </table>
        </div>
	</form>
		
		<!-- The template to display files available for upload -->
		<script id="template-upload" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
			<tr class="template-upload fade">
				<td colspan="4">
					<span class="name">{%=file.name%}</span>
				</td>
				<!--<td>
					<strong class="error text-danger"></strong>
				</td>-->
				<td>
					<span class="size">Processing...</span>
				</td>
				<td colspan=4>
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
				<td colspan="2">
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
		
		<!-- The template to display files available for download -->
		<script id="template-download" type="text/x-tmpl">		
		{% for (var i=0, file; file=o.files[i]; i++) { %}
			<tr class="template-download fade" id="row_{%=file.name%}">      				         
                {% if (file.deleteUrl) { %}
                    <td class="visible-md visible-lg visible-xl"><input type="checkbox" id="select_{%=file.name%}" name="delete" value="1" class="toggle" style="vertical-align: middle;" data-toggle="tooltip" title="Select for action"></td>
                {% } %}
				<td class="visible-md visible-lg visible-xl">
                {% if (file.favorite) { %}
                    <a href="#fav_{%=file.name%}" OnClick="favorite('{%=file.name%}')" data-toggle="tooltip" title="Favorite"><span id="fav_star_{%=file.name%}" class="glyphicon glyphicon-star" style="font-size: 1.5em; vertical-align: middle;"></span></a>
                {% } else { %}
                    <a href="#fav_{%=file.name%}" OnClick="favorite('{%=file.name%}')" data-toggle="tooltip" title="Favorite"><span id="fav_star_{%=file.name%}" class="glyphicon glyphicon-star-empty" style="font-size: 1.5em; vertical-align: middle;"></span></a>
                {% } %}					
				</td>
                <td>
                    <a href="<?php echo $GLOBALS["config"]["urls"]["baseUrl"]; ?>sample.php?hash={%=file.name%}" target="_blank" data-toggle="tooltip" title="Open"><span class="glyphicon glyphicon-open" style="font-size: 1.5em; vertical-align: middle;"></span></a>
                </td>
                <td class="visible-md visible-lg visible-xl">
					<img alt="" height="24px" width="24px" class="img-circle" src="data:image/png;base64,{%=file.user_avatar%}" data-toggle="tooltip" title="Uploader: {%=file.user_name%}">
					{% if (file.icon.length > 0) { %}
					<img alt="" height="24px" width="24px" class="img" src="data:image/png;base64,{%=file.icon%}" data-toggle="tooltip" title="Icon">
					{% } %}	
				</td>
				<td>
					<span class="name" data-toggle="tooltip" title="Upload date">{%=file.timestamp%}</span>
				</td>
				<td class="visible-md visible-lg visible-xl">
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
                <td class="visible-md visible-lg visible-xl">
                    <div class="col-md-2">
                        <input type="text" id="tags_{%=file.name%}" placeholder="add tag..." class="tm-input tm-input-small"/>							
                        <!--{% var tags=file.tags.split(",",5); %}
                        {% for (var j=0, tag; tag=tags[j]; j++) { %}
                        <span class="label label-primary">{%=tag%}</span>
                        {% } %}-->
                    </div>
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
                    {% if (file.filename.length > 25) { %}
					    <span data-toggle="tooltip" title="File name: {%=file.filename%}" class="name">{%=file.filename.substring(0,25).concat('...')%}</span>
                    {% } else { %}
                        <span data-toggle="tooltip" title="File name: {%=file.filename%}" class="name">{%=file.filename%}</span>
                    {% } %}
				</td>
				<td class="visible-sm visible-md visible-lg visible-xl">
					<span data-toggle="tooltip" title="File size: {%=o.formatFileSize(file.size)%}" class="size">{%=o.formatFileSize(file.size)%}</span>
				</td>
				<td class="visible-sm visible-md visible-lg visible-xl">					
					{% if (file.scanned == 1) { %}
						{% if (file.vtscore < 10) { %}
							<a href="{%=file.vtlink%}" id="vt_score_link{%=file.name%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" ><span id="vt_score_{%=file.name%}" class="label label-success">{%=file.vtscore%}/55</span></a>			
						{% } else if (file.vtscore >= 10 && file.vtscore < 20) { %}
							<a href="{%=file.vtlink%}" id="vt_score_link{%=file.name%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" ><span id="vt_score_{%=file.name%}" class="label label-warning">{%=file.vtscore%}/55</span></a>	
						{% } else { %}
							<a href="{%=file.vtlink%}" id="vt_score_link{%=file.name%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: {%=file.vtscore%}" ><span id="vt_score_{%=file.name%}" class="label label-danger">{%=file.vtscore%}/55</span></a>	
						{% } %}
					{% } else if (file.scanned == 0) { %}
						<span id="vt_score_{%=file.name%}" data-toggle="tooltip" title="VirusTotal score: File unknown" class="label label-warning">Unknown</span>
					{% } else if (file.scanned == -6) { %}
						<span id="vt_score_{%=file.name%}" data-toggle="tooltip" title="VirusTotal score: File not checked" class="label label-default">Not Checked</span>
					{% } else if (file.scanned == -5) { %}
						<span id="vt_score_{%=file.name%}" data-toggle="tooltip" title="VirusTotal score: File too big" class="label label-primary">Too big</span>
					{% } else if (file.scanned == -3) { %}
						<span id="vt_score_{%=file.name%}" data-toggle="tooltip" title="VirusTotal score: API limit reached" class="label label-primary">API Error</span>
					{% } else if (file.scanned == -2) { %}
						<a href="{%=file.vtlink%}" id="vt_score_link{%=file.name%}" target="_blank" data-toggle="tooltip" title="VirusTotal score: Currently scanning..."><span id="vt_score_{%=file.name%}" class="label label-primary">Scanning</span></a>
					{% } else { %}
						<span id="vt_score_{%=file.name%}" data-toggle="tooltip" title="VirusTotal score: Error" class="label label-primary">Error</span>
					{% } %}			
				</td>
                <?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) {	 ?>
                <td class="visible-sm visible-md visible-lg visible-xl">
                    {% if (file.ck_scanned == 0 && file.cklink ) { %}
                        <a href="{%=file.cklink%}" id="ck_link{%=file.name%}" target="_blank" data-toggle="tooltip" title="Cuckoo results" style="font-weight: bold; color: green;"><span id="ck_{%=file.name%}" class="label label-success">Results</span></a>
                    {% } else if (file.ck_scanned == -1) { %}
                        <span id="ck_{%=file.name%}" data-toggle="tooltip" title="Cuckoo Results" class="label label-warning">Scanning</span>
                    {% } else { %}
                        <span id="ck_{%=file.name%}" data-toggle="tooltip" title="Cuckoo Results" class="label label-primary">None</span>
                    {% } %}														
                </td>
                <?php } ?>
				<td>
					<div class="btn-group">
                    {% if (i >= o.files.length - 10 && i >= 10) { %}
                    <div class="dropup">
                    {% } else { %}
                    <div class="dropdown">
                    {% } %}		
					<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
						<i class="glyphicon glyphicon-chevron-down"></i>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul id="dropdown-item-actions" class="dropdown-menu dropdown-menu-right" role="menu">					
						<li><a href="#" class="menu-button-edit" id="edit_{%=file.name%}" data-id="{%=file.name%}" OnClick="edit_data('{%=file.name%}');">
							<i id="edit_img_{%=file.name%}" class="glyphicon glyphicon-edit"></i>
							<span id="edit_text_{%=file.name%}">Edit</span>
						</a></li>
						{% if (file.deleteUrl) { %}
							<li><a href="#" class="menu-button-delete" id="delete_{%=file.name%}" OnClick="delete_sample('{%=file.name%}','{%=file.deleteUrl%}','{%=file.deleteType%}','{file.deleteWithCredentials}');">
								<i class="glyphicon glyphicon-trash"></i>
								<span>Delete</span>
							</a></li>				
						{% } %}
                        <li class="divider"></li>
                        <li><a href="#" class="menu-button-comment" id="comment_{%=file.name%}" data-id="{%=file.name%}" data-comment-value="{%=file.comment%}" data-toggle="modal" data-target="#commentModal">
                            <i class="glyphicon glyphicon-pencil"></i>
                            <span>Comment</span>
                        </a></li> 
                        <li><a href="#" class="menu-button-vt-comment" id="comment_vt_{%=file.name%}" data-id="{%=file.name%}" data-toggle="modal" data-target="#commentVTModal">
                            <i class="glyphicon glyphicon-pencil"></i>
                            <span>VT Comment</span>	
                        </a></li> 
                        <li class="divider"></li>
                        <li><a href="#" class="menu-button-urls" id="urls_{%=file.name%}" data-id="{%=file.name%}" data-urls-value="{%=file.urls%}" data-toggle="modal" data-target="#urlModal">
                            <i class="glyphicon glyphicon-globe"></i>
                            <span>Manage URLs</span>
                        </a></li> 			                   
						<li class="divider"></li>
						{% if (file.scanned == 1 || file.scanned == 0 || file.scanned == -6) { %}
						<li><a href="#" class="menu-button-scan-vt" id="vt_scan_{%=file.name%}" type="button" OnClick="vt_scan('{%=file.name%}');">							
                            {% if (file.scanned == 0 || file.scanned == -6) { %}
                            <i class="glyphicon glyphicon-upload"></i>
							<span id="vt_scan_text_{%=file.name%}">VT Scan</span>
                            {% } else { %}
                            <i class="glyphicon glyphicon-repeat"></i>
                            <span id="vt_scan_text_{%=file.name%}">VT Rescan</span>
                            {% } %}	
						</a></li>
						{% } %}   
                        <?php if ($GLOBALS["config"]["cuckoo"]["enabled"]) {	 ?>
                        {% if (file.ck_scanned != -1) { %}
                        <li><a href="#" class="menu-button-scan-cuckoo" id="ck_scan_{%=file.name%}" OnClick="cuckoo_scan('{%=file.name%}');">
                            {% if (file.ck_scanned == 0) { %}
                            <i class="glyphicon glyphicon-repeat"></i>
                            <span id="ck_scan_text_{%=file.name%}">Cuckoo Rescan</span>
                            {% } else { %}
                            <i class="glyphicon glyphicon-upload"></i>
                            <span id="ck_scan_text_{%=file.name%}">Cuckoo Scan</span>
                            {% } %}
                        </a></li>
						{% } %}
                        <?php } ?>
					</ul>
					</div>	
                    </div>								
				</td>
			</tr>			
		{% } %}	
		</script>
        
        <!-- Old code for row expansion -->
        <!--<td>
                <a href="#more_{%=file.name%}" data-toggle="collapse"><span class="glyphicon glyphicon-plus" style="font-size: 1em; vertical-align: middle;"></span></a>
            </td>-->
        <!--<tr id="more_{%=file.name%}" class="collapse in">
            <td colspan="100%">
                <div class="panel panel-info">
                <div class="panel-body form-group" style="margin-bottom: 0px;">	
                </div>
                </div>
            </td>
        </tr>-->
                
        <div id="commentModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">	
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Comment: (Click to edit)</h4>
                    </div>
                    <div class="modal-body" id="body_comment">
                        <div id="p_comment" style='width: 100%; height: 400px; margin-top: 20px;'></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="urlModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">	
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">URLs:</h4>
                    </div>
                    <div class="modal-body" id="body_urls">
                        <div id="surveyForm" method="post" class="form-horizontal">                            									 											 
                            <div class="form-group" id="url_node_first">
                                <div class="col-xs-4">
                                    <input type="text" placeholder="Description" id="name_first" class="form-control" value="" />
                                </div>
                                <div class="col-xs-7">
                                    <input type="text" placeholder="http://domain.tld" id="url_first" class="form-control" value="" />
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-default" OnClick="modal_add_url_area();">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                        
                            <!-- The option field template containing an option field and a Remove button -->
                            <div class="form-group hide" id="urltemplate">
                                <div class="col-xs-4">
                                    <input class="form-control" type="text" id="name_next" placeholder="Description" />
                                </div>
                                <div class="col-xs-7">
                                    <input class="form-control" type="text" id="url_next" placeholder="http://domain.tld" />
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-default" OnClick="modal_remove_url_area($(this));">
                                        <i class="glyphicon glyphicon-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success edit" OnClick="modal_send_urls();">
                            <i class="glyphicon glyphicon-send"></i>
                            <span>Update</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="commentVTModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">	
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Comment:</h4>
                    </div>
                    <div class="modal-body" id="body_vt_comment">
                        <textarea id="t_commentvt" style="width: 100%; height: 100px"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success edit" OnClick="vt_comment();">
                            <i class="glyphicon glyphicon-send"></i>
                            <span>Send</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php  include(__DIR__."/footer.php"); ?> 
  <?php  include(__DIR__."/right-nav.php"); ?> 
  
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.2.3 -->
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- TinyMCE -->
<script src="plugins/tinymce/js/tinymce/tinymce.min.js"></script>

<!-- MRF -->
<!--<script src="plugins/jQueryUpload/js/vendor/jquery.min.js"></script>-->
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<!--<script src="plugins/jQueryUpload/js/vendor/jquery.ui.widget.js"></script>-->
<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="plugins/jQueryUpload/js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="plugins/jQueryUpload/js/load-image.all.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="plugins/jQueryUpload/js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<!--<script src="plugins/jQueryUpload/js/bootstrap.min.js"></script>-->
<!-- blueimp Gallery script -->
<script src="plugins/jQueryUpload/js/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="plugins/jQueryUpload/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="plugins/jQueryUpload/js/jquery.fileupload-ui.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="js/cors/jquery.xdr-transport.js"></script>
<![endif]-->

<!-- Bootstrap 3.3.6 -->
<!-- Bootstrap needs to be placed AFTER jquery-ui because of tootltip conflicts -->
<script src="bootstrap/js/bootstrap.min.js"></script>

<!-- jqPagination scripts -->
<script src="plugins/jQueryUpload/js/jquery.jqpagination.js"></script>
<!-- tags -->
<script type="text/javascript" src="plugins/jQueryUpload/js/tagmanager.js"></script>
<!-- The main application script -->
<script src="dist/js/main.js"></script>

<script>
$(function() {
	initRepo();
});
</script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. Slimscroll is required when using the
     fixed layout. -->
</body>
</html>
