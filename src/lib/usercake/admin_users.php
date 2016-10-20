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
    $deletions = $_POST['delete'];
    if ($deletion_count = deleteUsers($deletions)){
      $successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  
  $userData = fetchAllUsers(); //Fetch information for all users
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
              <h3 class="box-title">Users</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->  
            <form name='adminUsers' action="<?php echo($_SERVER['PHP_SELF']) ?>"" method="post" class="form-horizontal"> 
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
                
                <div class="table-responsive"> 
                  <table class="table">
                    <thread>
                      <tr>
                        <th>Delete</th>
                        <th>Username</th>
                        <th>Display Name</th>
                        <th>Title</th>
                        <th>Last Sign In</th>
                      </tr>   
                    </thread>
                    <tbody>       
                    <?php
                      //Cycle through users
                      foreach ($userData as $v1) { ?>
                        <tr>
                          <td>
                            <input type='checkbox' name='delete["<?php echo $v1['id'] ?>"]' id='delete["<?php echo $v1['id'] ?>"]' value='<?php echo $v1['id'] ?>'>
                          </td>                          
                          <td>
                              <?php if (!empty($v1['avatar'])) { ?>
                                    <img src="data:image/png;base64,<?php echo $v1['avatar']?>" width="24" height="24" class="img-circle" alt="User Image">
                                <?php } else { ?>
                                    <img src="../../../dist/img/noavatar.jpg" width="24" height="24" class="img-circle" alt="User Image">
                                <?php } ?>
                              <a href='admin_user.php?id=<?php echo $v1['id'] ?>'>                                
                                <?php echo $v1['user_name'] ?>
                              </a>
                          </td>
                          <td><?php echo $v1['display_name'] ?></td>
                          <td><?php echo $v1['title'] ?></td>
                          <td> 
                            <?php                        
                            //Interprety last login
                            if ($v1['last_sign_in_stamp'] == '0'){
                              echo "Never";	
                            }
                            else {
                              echo date("j M, Y", $v1['last_sign_in_stamp']);
                            }
                            ?>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>                               
              </div>
              <!-- /.box-body -->
               <div class="box-footer">
                 <button type="submit" class="btn btn-info pull-right">Delete</button>
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
