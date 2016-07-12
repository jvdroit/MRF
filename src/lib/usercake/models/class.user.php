<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

class loggedInUser {
	public $email = NULL;
	public $hash_pw = NULL;
	public $user_id = NULL;
	public $avatar = NULL;
	
	//Simple function to update the last sign in of a user
	public function updateLastSignIn()
	{
		global $mysqli,$db_table_prefix;
		$time = time();
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET
			last_sign_in_stamp = ?
			WHERE
			id = ?");
		$stmt->bind_param("ii", $time, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Return the timestamp when the user registered
	public function signupTimeStamp()
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT sign_up_stamp
			FROM ".$db_table_prefix."users
			WHERE id = ?");
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($timestamp);
		$stmt->fetch();
		$stmt->close();
		return ($timestamp);
	}
	
	//Return the token
	public function activationtoken()
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT activation_token
			FROM ".$db_table_prefix."users
			WHERE id = ?");
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($activation_token);
		$stmt->fetch();
		$stmt->close();
		return ($activation_token);
	}
	
	//Check if API key is valid
	public static function checkapikey($key)
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT count(*) 
			FROM ".$db_table_prefix."users
			WHERE activation_token = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();
		return ((bool)$count);
	}
	
	//Update a users password
	public function updatePassword($pass)
	{
		global $mysqli,$db_table_prefix;
		$secure_pass = generateHash($pass);
		$this->hash_pw = $secure_pass;
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET
			password = ? 
			WHERE
			id = ?");
		$stmt->bind_param("si", $secure_pass, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Update a users email
	public function updateAvatar($avatar_base64)
	{
		global $mysqli,$db_table_prefix;
		$this->avatar = $avatar_base64;
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET 
			avatar = ?
			WHERE
			id = ?");
		$stmt->bind_param("si", $avatar_base64, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Get user avatar
	public static function getavatar($user)
	{
		global $mysqli,$db_table_prefix;
		$stmt = $mysqli->prepare("SELECT avatar 
			FROM ".$db_table_prefix."users
			WHERE id = ?");			
		$stmt->bind_param("i", $user);
		$stmt->execute();
		$stmt->bind_result($avatar);
		$stmt->fetch();
		$stmt->close();
		return ($avatar);
	}
	
	//Get user name
	public static function getname($user)
	{
		global $mysqli,$db_table_prefix;
		$stmt = $mysqli->prepare("SELECT user_name 
			FROM ".$db_table_prefix."users
			WHERE id = ?");			
		$stmt->bind_param("i", $user);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return ($name);
	}
	
	//Get user id by API key
	public static function getuserbyapikey($key)
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT id 
			FROM ".$db_table_prefix."users
			WHERE activation_token = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->bind_result($user);
		$stmt->fetch();
		$stmt->close();
		return ($user);
	}
	
	//Get users candidates
	public static function getusersbyname($name)
	{
		global $mysqli,$db_table_prefix;
		$name_wild = '%' . $name . '%';
		
		$stmt = $mysqli->prepare("SELECT id 
			FROM ".$db_table_prefix."users
			WHERE user_name LIKE ?");
		$stmt->bind_param("s", $name_wild);
		$stmt->execute();
		$stmt->bind_result($user);
		$users = array();
		while($stmt->fetch()) {
			$users[] = $user;
		}
		$stmt->close();
		return ($users);
	}
	
	//Update a users email
	public function updateEmail($email)
	{
		global $mysqli,$db_table_prefix;
		$this->email = $email;
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET 
			email = ?
			WHERE
			id = ?");
		$stmt->bind_param("si", $email, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Is a user has a permission
	public function checkPermission($permission, $user = null)
	{		
		global $mysqli,$db_table_prefix,$master_account;
		$user_id = $user ? $user : $this->user_id;
		
		//Grant access if master user
		
		$stmt = $mysqli->prepare("SELECT id 
			FROM ".$db_table_prefix."user_permission_matches
			WHERE user_id = ?
			AND permission_id = ?
			LIMIT 1
			");
		$access = 0;
		foreach($permission as $check){
			if ($access == 0){
				$stmt->bind_param("ii", $user_id, $check);
				$stmt->execute();
				$stmt->store_result();
				if ($stmt->num_rows > 0){
					$access = 1;
				}
			}
		}
		if ($access == 1)
		{
			return true;
		}
		if ($user_id == $master_account){
			return true;	
		}
		else
		{
			return false;	
		}
		$stmt->close();
	}
	
	//Logout
	public function userLogOut()
	{
		destroySession("userCakeUser");
	}	
}

?>