<?php require_once(__DIR__."/config.php"); ?>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">      
      <a class="navbar-brand" href="http://www.adlice.com/software/malware-repository-framework/">MRF v<?php echo $GLOBALS["config"]["version"]?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div>
      <ul class="nav navbar-nav">                
        <?php //Links for logged in user
        if(isUserLoggedIn()) { ?> 
        <li><img alt="" height="100%" width="50px" src="data:image/png;base64,<?php echo $loggedInUser->avatar?>" title="<?php echo $loggedInUser->displayname?>"></li>        
        <li class="dropdownuser">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">User <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/account.php'><span class="glyphicon glyphicon-home"></span> Account Home</a></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/user_settings.php'><span class="glyphicon glyphicon-cog"></span> User Settings</a></li>
            <li role="separator" class="divider"></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/logout.php'><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>            
          </ul>                     
        </li>
        <?php } ?>
        
        <?php //Links for logged in user
        if(!isUserLoggedIn()) { ?> 
        <li class="dropdownotlogged">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Account <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/login.php'><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/register.php'><span class="glyphicon glyphicon-new-window"></span> Register</a></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/forgot-password.php'><span class="glyphicon glyphicon-question-sign"></span> Forgot Password</a></li>
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
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/admin_configuration.php'><span class="glyphicon glyphicon-cog"></span> Admin Configuration</a></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/admin_users.php'><span class="glyphicon glyphicon-user"></span> Admin Users</a></li>
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/admin_permissions.php'><span class="glyphicon glyphicon-lock"></span> Admin Permissions</a></li>            
            <li><a href='<?php echo $GLOBALS["config"]["urls"]["baseUrl"]?>src/lib/usercake/admin_pages.php'><span class="glyphicon glyphicon-file"></span> Admin Pages</a></li>
          </ul>         
        </li> 
         <?php } ?>
               
      </ul>      
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>