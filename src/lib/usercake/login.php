<?php 
  require_once(__DIR__."/models/config.php");
  if (!securePage($_SERVER['PHP_SELF'])){die();}
  
  //Prevent the user visiting the logged in page if he/she is already logged in
  if(isUserLoggedIn()) { header("Location: account.php"); die(); }
  
  //Forms posted
  if(!empty($_POST))
  {
    $errors = array();
    $username = sanitize(trim($_POST["username"]));
    $password = trim($_POST["password"]);
    
    //Perform some validation
    //Feel free to edit / change as required
    if($username == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
    }
    if($password == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
    }
  
    if(count($errors) == 0)
    {
      //A security note here, never tell the user which credential was incorrect
      if(!usernameExists($username))
      {
        $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
      }
      else
      {
        $userdetails = fetchUserDetails($username);
        //See if the user's account is activated
        if($userdetails["active"]==0)
        {
          $errors[] = lang("ACCOUNT_INACTIVE");
        }
        else
        {
          //Hash the password and use the salt from the database to compare the password.
          $entered_pass = generateHash($password,$userdetails["password"]);
          
          if($entered_pass != $userdetails["password"])
          {
            //Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
            $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
          }
          else
          {
            //Passwords match! we're good to go'
            
            //Construct a new logged in user object
            //Transfer some db data to the session object
            $loggedInUser = new loggedInUser();
            $loggedInUser->email = $userdetails["email"];
            $loggedInUser->user_id = $userdetails["id"];
            $loggedInUser->hash_pw = $userdetails["password"];
            $loggedInUser->title = $userdetails["title"];
            $loggedInUser->displayname = $userdetails["display_name"];
            $loggedInUser->username = $userdetails["user_name"];
            $loggedInUser->avatar = $userdetails["avatar"];
            
            //Update last sign in
            $loggedInUser->updateLastSignIn();
            $_SESSION["userCakeUser"] = $loggedInUser;
            
            //Redirect to user account page
            header("Location: account.php");
            die();
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
    <a href="../../../starter.php"><?php echo $websiteName ?></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Sign in to start your session</p>
    
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

    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
      <div class="form-group has-feedback">
        <input type="text" id="username" name="username" class="form-control" placeholder="Username">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" id="password" name="password" class="form-control" placeholder="Password">
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
          <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
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

    <a href="forgot-password.php">I forgot my password</a><br>
    <a href="register.php" class="text-center">Register a new membership</a>

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
