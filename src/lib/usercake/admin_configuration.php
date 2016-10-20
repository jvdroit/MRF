<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->

<?php 
  require_once(__DIR__."/models/config.php");
  if (!securePage($_SERVER['PHP_SELF'])){die();}
  
  //Forms posted
  if(!empty($_POST))
  {
    $cfgId = array();
    $newSettings = $_POST['settings'];
    
    //Validate new site name
    if ($newSettings[1] != $websiteName) {
      $newWebsiteName = $newSettings[1];
      if(minMaxRange(1,150,$newWebsiteName))
      {
        $errors[] = lang("CONFIG_NAME_CHAR_LIMIT",array(1,150));
      }
      else if (count($errors) == 0) {
        $cfgId[] = 1;
        $cfgValue[1] = $newWebsiteName;
        $websiteName = $newWebsiteName;
      }
    }
    
    //Validate new short site name
    if ($newSettings[8] != $websiteShortName) {
    	$newWebsiteShortName = $newSettings[8];
    	if(minMaxRange(1,150,$newWebsiteShortName))
    	{
    		$errors[] = lang("CONFIG_NAME_CHAR_LIMIT",array(1,150));
    	}
    	else if (count($errors) == 0) {
    		$cfgId[] = 8;
    		$cfgValue[8] = $newWebsiteShortName;
    		$websiteShortName = $newWebsiteShortName;
    	}
    }
    
    //Validate new site email address
    if ($newSettings[3] != $emailAddress) {
      $newEmail = $newSettings[3];
      if(minMaxRange(1,150,$newEmail))
      {
        $errors[] = lang("CONFIG_EMAIL_CHAR_LIMIT",array(1,150));
      }
      elseif(!isValidEmail($newEmail))
      {
        $errors[] = lang("CONFIG_EMAIL_INVALID");
      }
      else if (count($errors) == 0) {
        $cfgId[] = 3;
        $cfgValue[3] = $newEmail;
        $emailAddress = $newEmail;
      }
    }
    
    //Validate email activation selection
    if ($newSettings[4] != $emailActivation) {
      $newActivation = $newSettings[4];
      if($newActivation != "true" AND $newActivation != "false")
      {
        $errors[] = lang("CONFIG_ACTIVATION_TRUE_FALSE");
      }
      else if (count($errors) == 0) {
        $cfgId[] = 4;
        $cfgValue[4] = $newActivation;
        $emailActivation = $newActivation;
      }
    }
    
    //Validate new email activation resend threshold
    if ($newSettings[5] != $resend_activation_threshold) {
      $newResend_activation_threshold = $newSettings[5];
      if($newResend_activation_threshold > 72 OR $newResend_activation_threshold < 0)
      {
        $errors[] = lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72));
      }
      else if (count($errors) == 0) {
        $cfgId[] = 5;
        $cfgValue[5] = $newResend_activation_threshold;
        $resend_activation_threshold = $newResend_activation_threshold;
      }
    }
    
    //Validate new language selection
    if ($newSettings[6] != $language) {
      $newLanguage = $newSettings[6];
      if(minMaxRange(1,150,$language))
      {
        $errors[] = lang("CONFIG_LANGUAGE_CHAR_LIMIT",array(1,150));
      }
      elseif (!file_exists($newLanguage)) {
        $errors[] = lang("CONFIG_LANGUAGE_INVALID",array($newLanguage));				
      }
      else if (count($errors) == 0) {
        $cfgId[] = 6;
        $cfgValue[6] = $newLanguage;
        $language = $newLanguage;
      }
    }
    
    //Validate new template selection
    /*if ($newSettings[7] != $template) {
      $newTemplate = $newSettings[7];
      if(minMaxRange(1,150,$template))
      {
        $errors[] = lang("CONFIG_TEMPLATE_CHAR_LIMIT",array(1,150));
      }
      elseif (!file_exists($newTemplate)) {
        $errors[] = lang("CONFIG_TEMPLATE_INVALID",array($newTemplate));				
      }
      else if (count($errors) == 0) {
        $cfgId[] = 7;
        $cfgValue[7] = $newTemplate;
        $template = $newTemplate;
      }
    }*/
    
    //Update configuration table with new settings
    if (count($errors) == 0 AND count($cfgId) > 0) {
      updateConfig($cfgId, $cfgValue);
      $successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
    }
  }
  
  $languages = getLanguageFiles(); //Retrieve list of language files
  //$templates = getTemplateFiles(); //Retrieve list of template files
  $permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
?> 

<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $websiteName ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="../../../bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../../dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect.
  -->
  <link rel="stylesheet" href="../../../dist/css/skins/skin-blue.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
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

  <?php  include(__DIR__."/../../../top-nav.php"); ?> 
  <?php  include(__DIR__."/../../../left-nav.php"); ?> 
  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">      
      <div id='content'>          
        <!-- Horizontal Form -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Admin Configuration</h3>
          </div>
          <!-- /.box-header -->
          <!-- form start -->          
          <form name='adminConfiguration' action="<?php echo $_SERVER['PHP_SELF'] ?>"" method="post" class="form-horizontal">
            <div class="box-body">  
              <div class="form-group">
                <label for="website_name" class="col-sm-2 control-label">Website Name</label>
                <div class="col-sm-10">
                  <input type="text" id="website_name" name='settings[<?php echo $settings['website_name']['id'] ?>]' value='<?php echo $websiteName ?>' class="form-control" placeholder="Website Name">
                </div>
              </div>
              <div class="form-group">
                <label for="website_short_name" class="col-sm-2 control-label">Website Short Name</label>
                <div class="col-sm-10">
                  <input type="text" id="website_short_name" name='settings[<?php echo $settings['website_short_name']['id'] ?>]' value='<?php echo $websiteShortName ?>' class="form-control" placeholder="Website Short Name">
                </div>
              </div>  
              <div class="form-group">
                <label for="email" class="col-sm-2 control-label">Email</label>
                <div class="col-sm-10">
                  <input type="text" id="email" name='settings[<?php echo $settings['email']['id'] ?>]' value='<?php echo $emailAddress ?>' class="form-control" placeholder="Email">
                </div>
              </div>
              <div class="form-group">
                <label for="activation_threshold" class="col-sm-2 control-label">Activation Threshold</label>
                <div class="col-sm-10">
                  <input type="text" id="activation_threshold" name='settings[<?php echo $settings['resend_activation_threshold']['id'] ?>]' value='<?php echo $resend_activation_threshold ?>' class="form-control" placeholder="Activation Threshold">
                </div>
              </div>
              <div class="form-group">
                <label for="language" class="col-sm-2 control-label">Language</label>
                <div class="col-sm-10">
                  <select type="text" id="language" name='settings[<?php echo $settings['language']['id'] ?>]' class="form-control">
                    <?php
                    //Display language options
                    foreach ($languages as $optLang) {
                      if ($optLang == $language) {
                        echo "<option value='".$optLang."' selected>$optLang</option>";
                      }
                      else {
                        echo "<option value='".$optLang."'>$optLang</option>";
                      }
                    }
                    ?>        
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="activation" class="col-sm-2 control-label">Email Activation</label>
                <div class="col-sm-10">
                  <select type="text" id="activation" name='settings[<?php echo $settings['activation']['id'] ?>]' class="form-control">
                    <?php
                    //Display email activation options
                    if ($emailActivation == "true"){ ?>
                        <option value='true' selected>True</option>
                        <option value='false'>False</option>
                      </select>
                    <?php } else { ?>
                        <option value='true'>True</option>
                        <option value='false' selected>False</option>
                      </select>
                    <?php } ?>  
                </div>
              </div>
              <!--<div class="form-group">
                <label for="template" class="col-sm-2 control-label">Template</label>
                <div class="col-sm-10">
                  <select type="text" id="template" name='settings[<?php echo $settings['template']['id'] ?>]' class="form-control">
                    <?php
                    //Display template options
                    foreach ($templates as $temp){
                      if ($temp == $template){
                        echo "<option value='".$temp."' selected>$temp</option>";
                      }
                      else {
                        echo "<option value='".$temp."'>$temp</option>";
                      }
                    }
                    ?> 
                   </select>
                </div>
              </div>-->            
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
              <button type="submit" class="btn btn-info pull-right">Submit</button>
            </div>
            <!-- /.box-footer -->            
          </form>
        </div>
      </div>
      
      <!-- Breadcrumb -->
     <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol>-->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Your Page Content Here -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php  include(__DIR__."/../../../footer.php"); ?> 
  <?php  include(__DIR__."/../../../right-nav.php"); ?> 
  
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.2.3 -->
<script src="../../../plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../../../bootstrap/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="../../../dist/js/app.min.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. Slimscroll is required when using the
     fixed layout. -->
</body>
</html>
