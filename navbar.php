<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">      
      <a class="navbar-brand" href="http://www.adlice.com/software/malware-repository-framework/">MRF v2.0</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">                
        <?php //Links for logged in user
        if(isUserLoggedIn()) { ?> 
        <li><img alt="" height="100%" width="50px" src="data:image/png;base64,<?php echo $loggedInUser->avatar?>" title="<?php echo $loggedInUser->displayname?>"></li>        
        <li class="dropdownuser">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">User <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href='account.php'>Account Home</a></li>
            <li><a href='user_settings.php'>User Settings</a></li>
            <li role="separator" class="divider"></li>
            <li><a href='logout.php'>Logout</a></li>            
          </ul>                     
        </li>
        <?php } ?>
        
        <?php //Links for logged in user
        if(!isUserLoggedIn()) { ?> 
        <li class="dropdownotlogged">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Account <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href='login.php'>Login</a></li>
            <li><a href='register.php'>Register</a></li>
            <li><a href='forgot-password.php'>Forgot Password</a></li>
            <?php if ($emailActivation) { ?>
            <li><a href='resend-activation.php'>Resend Activation Email</a></li>
            <?php } ?>           
          </ul>                     
        </li>
        <?php } ?>     
        
        <?php //Links for permission level 2 (default admin)
        if (isUserLoggedIn() && $loggedInUser->checkPermission(array(2))){ ?>
        <li class="dropdownadmin">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href='admin_configuration.php'>Admin Configuration</a></li>
            <li><a href='admin_users.php'>Admin Users</a></li>
            <li><a href='admin_permissions.php'>Admin Permissions</a></li>            
            <li><a href='admin_pages.php'>Admin Pages</a></li>
          </ul>         
        </li> 
         <?php } ?>
               
      </ul>      
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>