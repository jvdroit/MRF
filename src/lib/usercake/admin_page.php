<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->

<?php 
  require_once(__DIR__."/models/config.php");
  if (!securePage($_SERVER['PHP_SELF'])){die();}
  $pageId = $_GET['id'];
  
  //Check if selected pages exist
  if(!pageIdExists($pageId)){
    header("Location: admin_pages.php"); die();	
  }
  
  $pageDetails = fetchPageDetails($pageId); //Fetch information specific to page
  
  //Forms posted
  if(!empty($_POST)){
    $update = 0;
    
    if(!empty($_POST['private'])){ $private = $_POST['private']; }
    
    //Toggle private page setting
    if (isset($private) AND $private == 'Yes'){
      if ($pageDetails['private'] == 0){
        if (updatePrivate($pageId, 1)){
          $successes[] = lang("PAGE_PRIVATE_TOGGLED", array("private"));
        }
        else {
          $errors[] = lang("SQL_ERROR");
        }
      }
    }
    elseif ($pageDetails['private'] == 1){
      if (updatePrivate($pageId, 0)){
        $successes[] = lang("PAGE_PRIVATE_TOGGLED", array("public"));
      }
      else {
        $errors[] = lang("SQL_ERROR");	
      }
    }
    
    //Remove permission level(s) access to page
    if(!empty($_POST['removePermission'])){
      $remove = $_POST['removePermission'];
      if ($deletion_count = removePage($pageId, $remove)){
        $successes[] = lang("PAGE_ACCESS_REMOVED", array($deletion_count));
      }
      else {
        $errors[] = lang("SQL_ERROR");	
      }
      
    }
    
    //Add permission level(s) access to page
    if(!empty($_POST['addPermission'])){
      $add = $_POST['addPermission'];
      if ($addition_count = addPage($pageId, $add)){
        $successes[] = lang("PAGE_ACCESS_ADDED", array($addition_count));
      }
      else {
        $errors[] = lang("SQL_ERROR");	
      }
    }
    
    $pageDetails = fetchPageDetails($pageId);
  }
  
  $pagePermissions = fetchPagePermissions($pageId);
  $permissionData = fetchAllPermissions();
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
        <div id='main'>  
          
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Page Administration</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->  
            <form name='adminPage' action="<?php echo($_SERVER['PHP_SELF'] . "?id=" . $pageId) ?>"" method="post" class="form-horizontal">   
              <div class="box-body">  
                
                <?php
                foreach($errors as $error) { ?>
                  <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $error ?>
                  </div>      
                <?php } ?>
                
                <?php
                foreach($successes as $success) { ?>
                  <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $success ?>
                  </div>      
                <?php } ?>
                
                <input type='hidden' name='process' value='1'>
                
                <div class="form-group">
                  <label for="page_id" class="col-sm-2 control-label">Page ID</label>
                  <div class="col-sm-10">
                    <input type="text" id="page_id" value='<?php echo $pageDetails['id'] ?>' class="form-control" disabled>
                  </div>
                </div>                
                <div class="form-group">
                  <label for="page_name" class="col-sm-2 control-label">Page Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="page_name" value='<?php echo $pageDetails['page'] ?>' class="form-control" disabled>
                  </div>
                </div>                
                <div class="form-group"> 
                  <label for="page_name" class="col-sm-2 control-label">Private</label>
                  <div class="col-sm-10">               
                    <div class="checkbox">
                      <?php
                      //Display private checkbox
                      if ($pageDetails['private'] == 1){
                        echo "<input type='checkbox' name='private' id='private' value='Yes' checked>";
                      }
                      else {
                        echo "<input type='checkbox' name='private' id='private' value='Yes'>";	
                      }
                      ?>
                    </div>
                  </div>
                </div>
                <div class="form-group"> 
                  <label for="remove_access" class="col-sm-2 control-label">Remove Access</label>
                  <div class="col-sm-10">               
                    <div class="checkbox">
                      <?php
                      //Display list of permission levels with access
                      foreach ($permissionData as $v1) {
                        if(isset($pagePermissions[$v1['id']])){
                          echo "<br><input type='checkbox' name='removePermission[".$v1['id']."]' id='removePermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name'];
                        }
                      }
                      ?>
                    </div>
                  </div>
                </div>
                <div class="form-group"> 
                  <label for="add_access" class="col-sm-2 control-label">Add Access</label>
                  <div class="col-sm-10">               
                    <div class="checkbox">
                      <?php
                      //Display list of permission levels without access
                      foreach ($permissionData as $v1) {
                        if(!isset($pagePermissions[$v1['id']])){
                          echo "<br><input type='checkbox' name='addPermission[".$v1['id']."]' id='addPermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name'];
                        }
                      }
                      ?>
                    </div>
                  </div>
                </div>
               </div>
               <!-- /.box-body -->
               <div class="box-footer">
                 <button type="submit" class="btn btn-info pull-right">Update</button>
               </div>
               <!-- /.box-footer -->   
            </form>
          </div>           
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
