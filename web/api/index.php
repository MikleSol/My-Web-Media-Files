<?php
/*
  Media hosting project
  Version 1.0-beta
  index.php
  29.11.2010
  Scripted by Poluboyarinov Mikhail
  mikle.sol@gmail.com
 */
include("include/config.php");

header('Access-Control-Allow-Origin: *');

switch ($_GET['mod']) {
    case "get_files":
	$user_id=$user->check_auth($_COOKIE['session_key']);
	if($user_id == false){
	    echo json_encode("no_auth");
	    exit(0);
	}
	if((!isset($_GET['limit'])) OR ($_GET['limit'] > 30)){
	    echo json_encode("error_limit");
	    exit(0);
	}
	$limit=$_GET['limit'];
	$sqs="SELECT 
	    *
	FROM 
	    ".db_pr."files
	LEFT JOIN
	    ".db_pr."files_meta
	ON
	    ".db_pr."files.file_id=".db_pr."files_meta.file_meta_id
	LEFT JOIN
	    ".db_pr."users_dirsfiles
	ON
	    ".db_pr."files.file_id=".db_pr."users_dirsfiles.user_file_id
	LEFT JOIN
	    ".db_pr."files_servers
	ON
	    ".db_pr."files.file_server=".db_pr."files_servers.srv_id
	WHERE 
	    ".db_pr."files.file_uid='$user_id' AND user_file_id IS NULL ORDER by file_id DESC Limit 0,".$limit;
	$sql=$db->sql_query($sqs);
	if($db->sql_numrows($sql) == 0){
	    echo json_encode("no_files");
	}else{
	    $privacy=array("Me","User","All");
	    $type=array("img" => "Image", "video" => "Video", "audio" => "Audio");
	    while($rw=$db->sql_fetchrow($sql)){
		$return[]=array('fileid' => md5($rw['file_time']), 'type' => $type[$rw['file_type']], 'srv' => $rw['srv_url'], 'name' => $rw['name'], 'privacy' => $privacy[$rw['privacy']]);
	    }
	    echo json_encode($return);
	}
	break;
    case "upload_url":
        $sql_s = $db->sql_query("SELECT srv_id as srv, srv_url, srv_script FROM " . db_pr . "files_servers WHERE (srv_quote-srv_used) > '2048' AND srv_status='1' ORDER by RAND() Limit 0,1");
        $rw_s = $db->sql_fetchrow($sql_s);
        $url=$rw_s['srv_url'].$rw_s['srv_script'];
        echo $url;
	break;
    case "auth":
	$user_id=$user->check_auth($_COOKIE['session_key']);
	if($user_id == false){
	    echo "false";
	}else{
	    echo "true";
	}
        break;
    case "upload":
        header('Content-Type: application/json; charset=utf-8');
        $sql_s = $db->sql_query("SELECT srv_id as srv, srv_url, srv_script FROM " . db_pr . "files_servers WHERE (srv_quote-srv_used) > '2048' AND srv_status='1' ORDER by RAND() Limit 0,1");
        $rw_s = $db->sql_fetchrow($sql_s);
        $group_info = $user->group_info($user_id);
        if ($rw_s['srv'] > 0) {
            $return['url']=$rw_s['srv_url'].$rw_s['srv_script'];
            $return['limit']=$group_info['group_settings']['upload_video'];
            $return['key']=$_COOKIE['session_key'];
            echo json_encode($return);
        } else {
            echo false;
        }
        break;
}
?>