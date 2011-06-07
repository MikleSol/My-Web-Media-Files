<?
	header('Access-Control-Allow-Origin: http://myds.pp.ru');
	include("./include/config.php");
	$image_id = isset($_POST["file"]) ? $_POST["file"] : false;
	global $db;
	$user_id=$user->check_auth($_POST['session_key']);
	if($user_id == false){
		echo "auth";
		exit();
	}
	$sql=$db->sql_query("SELECT ".db_pr."files.file_id,".db_pr."files.file_time,".db_pr."files.file_type,".db_pr."files_servers.srv_dir FROM ".db_pr."files LEFT JOIN ".db_pr."files_servers ON ".db_pr."files.file_server=".db_pr."files_servers.srv_id WHERE md5(file_time)='$image_id' AND file_uid = '$user_id'");
	if($db->sql_numrows($sql) == 0){
		echo "file";
		exit();
	}
	$rw=$db->sql_fetchrow($sql);


    $db->sql_query("DELETE FROM ".db_pr."files WHERE file_id='".$rw['file_id']."'");
    $db->sql_query("DELETE FROM ".db_pr."files_meta WHERE file_meta_id='".$rw['file_id']."'");

    $file_dir=$rw['srv_dir'].date("Y")."/".date("m")."/".date("d")."/".$rw['file_time']."/";

if ($handle = opendir($file_dir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink($file_dir.$file);
        }
    }
    closedir($handle);
}
@rmdir($file_dir);

echo "true";

?>
