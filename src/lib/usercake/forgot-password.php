<?php 
  require_once(__DIR__."/models/config.php");
  if (!securePage($_SERVER['PHP_SELF'])){die();}
  
  //User has confirmed they want their password changed 
  if(!empty($_GET["confirm"]))
  {
    $token = trim($_GET["confirm"]);
    
    if($token == "" || !validateActivationToken($token,TRUE))
    {
      $errors[] = lang("FORGOTPASS_INVALID_TOKEN");
    }
    else
    {
      $rand_pass = getUniqueCode(15); //Get unique code
      $secure_pass = generateHash($rand_pass); //Generate random hash
      $userdetails = fetchUserDetails(NULL,$token); //Fetchs user details
      $mail = new userCakeMail();		
      
      //Setup our custom hooks
      $hooks = array(
        "searchStrs" => array("#GENERATED-PASS#","#USERNAME#"),
        "subjectStrs" => array($rand_pass,$userdetails["display_name"])
        );
      
      if(!$mail->newTemplateMsg("your-lost-password.txt",$hooks))
      {
        $errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
      }
      else
      {	
        if(!$mail->sendMail($userdetails["email"],"Your new password"))
        {
          $errors[] = lang("MAIL_ERROR");
        }
        else
        {
          if(!updatePasswordFromToken($secure_pass,$token))
          {
            $errors[] = lang("SQL_ERROR");
          }
          else
          {	
            if(!flagLostPasswordRequest($userdetails["user_name"],0))
            {
              $errors[] = lang("SQL_ERROR");
            }
            else {
              $successes[]  = lang("FORGOTPASS_NEW_PASS_EMAIL");
            }
          }
        }
      }
    }
  }
  
  //User has denied this request
  if(!empty($_GET["deny"]))
  {
    $token = trim($_GET["deny"]);
    
    if($token == "" || !validateActivationToken($token,TRUE))
    {
      $errors[] = lang("FORGOTPASS_INVALID_TOKEN");
    }
    else
    {
      
      $userdetails = fetchUserDetails(NULL,$token);
      
      if(!flagLostPasswordRequest($userdetails["user_name"],0))
      {
        $errors[] = lang("SQL_ERROR");
      }
      else {
        $successes[] = lang("FORGOTPASS_REQUEST_CANNED");
      }
    }
  }
  
  //Forms posted
  if(!empty($_POST))
  {
    $email = $_POST["email"];
    $username = sanitize($_POST["username"]);
    
    //Perform some validation
    //Feel free to edit / change as required
    
    if(trim($email) == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
    }
    //Check to ensure email is in the correct format / in the db
    else if(!isValidEmail($email) || !emailExists($email))
    {
      $errors[] = lang("ACCOUNT_INVALID_EMAIL");
    }
    
    if(trim($username) == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
    }
    else if(!usernameExists($username))
    {
      $errors[] = lang("ACCOUNT_INVALID_USERNAME");
    }
    
    if(count($errors) == 0)
    {      
      //Check that the username / email are associated to the same account
      if(!emailUsernameLinked($email,$username))
      {
        $errors[] =  lang("ACCOUNT_USER_OR_EMAIL_INVALID");
      }
      else
      {
        //Check if the user has any outstanding lost password requests
        $userdetails = fetchUserDetails($username);
        if($userdetails["lost_password_request"] == 1)
        {
          $errors[] = lang("FORGOTPASS_REQUEST_EXISTS");
        }
        else
        {
          //Email the user asking to confirm this change password request
          //We can use the template builder here
          
          //We use the activation token again for the url key it gets regenerated everytime it's used.
          
          $mail = new userCakeMail();
          $confirm_url = lang("CONFIRM")."\n".$websiteUrl."src/lib/usercake/forgot-password.php?confirm=".$userdetails["activation_token"];
          $deny_url = lang("DENY")."\n".$websiteUrl."src/lib/usercake/forgot-password.php?deny=".$userdetails["activation_token"];
          
          //Setup our custom hooks
          $hooks = array(
            "searchStrs" => array("#CONFIRM-URL#","#DENY-URL#","#USERNAME#"),
            "subjectStrs" => array($confirm_url,$deny_url,$userdetails["user_name"])
            );
          
          if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks))
          {
            $errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
          }
          else
          {
            if(!$mail->sendMail($userdetails["email"],"Lost password request"))
            {
              $errors[] = lang("MAIL_ERROR");
            }
            else
            {
              //Update the DB to show this account has an outstanding request
              if(!flagLostPasswordRequest($userdetails["user_name"],1))
              {
                $errors[] = lang("SQL_ERROR");
              }
              else {
                
                $successes[] = lang("FORGOTPASS_REQUEST_SUCCESS");
              }
            }
          }
        }
      }
    }
  }
?> 

<!DOCTYPE html>
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
  <!-- iCheck -->
  <link rel="stylesheet" href="../../../plugins/iCheck/square/blue.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="../../../index.php"><?php echo $websiteName ?></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Password recevery</p> 
    
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

    <form name='newLostPass' action="<?php echo $_SERVER['PHP_SELF'] ?>"" method="post">
      <div class="form-group has-feedback">
        <input type="text" id="username" name="username" class="form-control" placeholder="Username">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" id="email" name="email" class="form-control" placeholder="Email">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8">
          <!--<div class="checkbox icheck">
            <label>
              <input type="checkbox"> Remember Me
            </label>
          </div>-->
        </div>
        <!-- /.col -->
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Submit</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    <!--<div class="social-auth-links text-center">
      <p>- OR -</p>
      <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using
        Facebook</a>
      <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> Sign in using
        Google+</a>
    </div>-->
    <!-- /.social-auth-links -->

    <!--<a href="forgot-password.php">I forgot my password</a><br>-->
    <!--<a href="register.php" class="text-center">Register a new membership</a>-->

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.2.3 -->
<script src="../../../plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../../../bootstrap/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="../../../plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
</body>
</html>
