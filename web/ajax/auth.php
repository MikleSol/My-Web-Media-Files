<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}

include("../include/config.php");

	if($_POST['auth'] == true){
		$rw=$user->check_login($_POST['lg_email'],$_POST['lg_passwd']);
		if($rw == false){
			echo "false";
		}else{
			$session_key=md5($rw['user_name'].$rw['user_id'].$_POST['lg_passwd']);
			$sstype=($_POST['lg_all'] == 1)? 1 : 0;
			$user->session_add($rw['user_id'],$session_key,$sstype);
			echo "true";
		}
	}elseif($_POST['logout'] == true){
		$session_key=$_COOKIE['session_key'];
		log_add("session ".$session_key." closed","auth.log");
		$user->session_del($session_key);
		return true;
	}
?>
