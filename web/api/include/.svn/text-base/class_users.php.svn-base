<?
/*
		Media hosting project
		Version 1.0-beta
		class_users.php
		02.12.2010
		Scripted by Poluboyarinov Mikhail
		mikle.sol@gmail.com
*/
class user
{

	function user_add($email,$name,$passwd,$group,$money,$key)
	{
	  global $db;
		if ($group == false) $group=1;
		if ($money == false) $money=0;
		$passwd=md5($passwd);
		$sql="INSERT INTO ".db_pr."users VALUES (NULL,'$email','$name','$passwd','$group','$money','$key')";
		if(!$db->sql_query($sql)){
			return "base";
		}else{
			$this->send_email($email,array('key' => $key),"register");
			return "true";
		}
	}

	function send_email($email,$values,$page)
	{
	  global $db,$config;
		$query="SELECT * FROM ".db_pr."email WHERE email_name='".$page."'";
		$sql=$db->sql_query($query);
		if($db->sql_numrows($sql) == 0 OR $key == false OR $email== false){
			return "Error";
		}else{
			$rw=$db->sql_fetchrow($sql);
			$to      = $email;
			$subject = $rw['email_subj'];
			$message = $rw['email_text'];
			$headers = 'From: '.$config['email'].'' . "\r\n" .
			    'Reply-To: '.$config['email'].'' . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			
			mail($to, $subject, $message, $headers);
		}
	}

	function user_update($user_info,$oldpasswd,$email,$name,$passwd,$passwd1)
	{
	  global $db;
		$pwt=$user->check_login($user_info['user_email'],$oldpasswd);
		if($pwt == false){
			return "oldpasswd";
		}else{
			if(strlen($passwd) > 4){
				if($passwd != $passwd1){
					return "npwd";
				}else{
					$newpasswd=", user_passwd='".md5($_POST['passwd'])."'";
				}
			}
			$query="UPDATE ".db_pr."users SET user_name='".$name."' $newpasswd WHERE user_id='".$pwt['user_id']."'";
			if($db->sql_query($query)){
				return "true";
			}else{
				return "base";
			}
		}
	}

	function user_money($user_info,$action,$amount)
	{
	  global $db;
		if($user_info['user_id'] == false){
			return "auth";
		}else{
	    $sql="UPDATE ".db_pr."users SET user_money=(user_money".$action.$amount.") WHERE user_id='".$user_info['user_id']."'";
			if($db->sql_query($sql)){
				return "true";
			}else{
				return "base";
			}
		}
	}

	function user_group_change($user_info,$group_id)
	{
		global $db;
		if($user_info['user_id'] == false){
			return "auth";
		}else{
			$sql="UPDATE ".db_pr."users SET user_group='".$group_id."' WHERE user_id='".$user_info['user_id']."'";
			if($db->sql_query($sql)){
				return "true";
			}else{
				return "base";
			}
		}
	}

  function check_auth($session_key)
  {
    global $db;
		$query="SELECT * FROM ".db_pr."users_session WHERE session_key='$session_key'";

		$sql=$db->sql_query($query);
		if ($db->sql_numrows($sql) == 0){
		  return false;
		}else{
			$rw=$db->sql_fetchrow($sql);
			if($rw['session_type'] == 0 AND ($rw['session_time']+86400) < time()){
				$this->session_del($session_key);
				return false;
			}else{
				return $rw['session_uid'];
			}
		}
  }

	function check_login($email,$passwd)
	{
	  global $db;
		$password=md5($passwd);
		$sql=$db->sql_query("SELECT user_id, user_name FROM ".db_pr."users WHERE user_email='$email' AND user_passwd='$password' AND user_key=''");
		if ($db->sql_numrows($sql) == 0){
		  return false;
		}else{
			$rw=$db->sql_fetchrow($sql);
			return $rw;
		}
	}

	function user_info($user_id)
	{
	  global $db;
		$query="SELECT
						".db_pr."users.user_email ,".db_pr."users.user_name ,".db_pr."users.user_type,".db_pr."users.user_group,".db_pr."users.user_money,
						".db_pr."users_group.group_name as user_group_name
					FROM 
						".db_pr."users 
					LEFT JOIN 
						".db_pr."users_group 
					ON 
						".db_pr."users.user_group=".db_pr."users_group.group_id
					WHERE 
						user_id='$user_id'";

		$sql=$db->sql_query($query);
		if($db->sql_numrows($sql) == 0){
			return false;
		}else{
			$rw=$db->sql_fetchrow($sql);
			return $rw;
		}
	}

	function group_info($user_id)
	{
	  global $db;
		if($user_id > 0){
			$query="SELECT
					".db_pr."users.user_id,
					".db_pr."users_group.group_name, ".db_pr."users_group.group_settings
				FROM
					".db_pr."users
				LEFT JOIN
					".db_pr."users_group
				ON
					".db_pr."users.user_group=".db_pr."users_group.group_id
				WHERE
					".db_pr."users.user_id='$user_id'
			";
		}else{
			$query="SELECT group_name, group_settings FROM ".db_pr."users_group WHERE group_id='0'";
		}

		$sql=$db->sql_query($query);
		$rw=$db->sql_fetchrow($sql);

		$g1a=explode(',',$rw['group_settings']);
		for($i=0; $i < each($g1a); $i++){
			list($per,$val)=explode("=",$g1a[$i]);
			$group_settings[$per]=$val;
		}

		$ret['group_name']=$rw['group_name'];
		$ret['group_settings']=$group_settings;
		return $ret;

	}

	function session_add($user_id,$session_key,$session_type)
	{
	  global $db;
		$sql1=$db->sql_query("SELECT session_id FROM ".db_pr."users_session WHERE session_uid='$user_id'");
		if($db->sql_numrows($sql1) == 0){
			$sql2="INSERT INTO ".db_pr."users_session VALUES (NULL,'$user_id','$session_key','".time()."','".$session_type."')";
			$db->sql_query($sql2);
		}else{
			$rw=$db->sql_fetchrow($sql1);
			$sql3="UPDATE ".db_pr."users_session SET session_key='$session_key', session_time='".time()."', session_type='$session_type' WHERE session_id='".$rw['session_id']."'";
			$db->sql_query($sql3);
		}
		setcookie("session_key", $session_key,time()+60*60*24*30*6,"/",".".$_SERVER['SERVER_NAME']);
	}

	function session_del($session_key)
	{
	  global $db;
		setcookie("session_key", "");
		$db->sql_query("DELETE FROM ".db_pr."users_session WHERE session_key='$session_key'");
	}
}
?>
