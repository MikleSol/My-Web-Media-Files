<?php

/*
  Media hosting project
  Version 1.0-beta
  upload.php
  29.11.2010
  Scripted by Poluboyarinov Mikhail
  mikle.sol@gmail.com
 */
set_time_limit(0);
error_reporting(7);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control');

if (isset($_POST["PHPSESSID"])) {
    session_id($_POST["PHPSESSID"]);
}
session_start();
// Check the upload
$api = ($_REQUEST['api'] == 1) ? true : false;
if ($api == true) {
    $file_url = ($_GET['url'] == true) ? $_GET['url'] : $_POST['url'];
    if($_REQUEST['screen'] == 1){
	print_r($_FILES);
	print_r($_REQUEST);
    }elseif ($file_url == true) {
	$file_contents = curl_init($file_url);
    } else {
        $headers = getallheaders();
        if (!isset(
                        $headers['Content-Type'], $headers['Content-Length'], $headers['X-File-Size'], $headers['X-File-Name']
                ) &&
                $headers['Content-Type'] != 'multipart/form-data' &&
                $headers['Content-Length'] != $headers['X-File-Size']
        ) {
            if ($config['log_lvl'] > 0) {
                log_add("ERROR: There was a problem with the upload", "upload");
            }
            echo "ERROR: There was a problem with the upload";
            exit(0);
        }
    }
} else {
    if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
        if ($config['log_lvl'] > 0) {
            log_add("ERROR: There was a problem with the upload", "upload");
        }
        echo "ERROR: There was a problem with the upload";
        exit(0);
    }
}

require_once("include/config.php");
require_once("include/class_upload.php");
global $db, $config;


$sql_s = $db->sql_query("SELECT * FROM " . db_pr . "files_servers WHERE (srv_quote-srv_used) > '2048' AND srv_status='1' AND srv_id='" . $config['srv_id'] . "' Limit 0,1");
$rw_s = $db->sql_fetchrow($sql_s);
$srv_id = $config['srv_id'];
if (isset($_POST['session_key'])) {
    $session_key = $_POST['session_key'];
} else {
    $session_key = $_COOKIE['session_key'];
}
$user_id = $user->check_auth($session_key);
$group_info = $user->group_info($user_id);
$file_id = time();

$upload_final = $rw_s['srv_dir'] . date("Y") . "/" . date("m") . "/" . date("d") . "/" . $file_id . "/";
if (is_dir($rw_s['srv_dir'] . date("Y") . "/") == false) {
    mkdir($rw_s['srv_dir'] . date("Y") . "/", 0777);
}
if (is_dir($rw_s['srv_dir'] . date("Y") . "/" . date("m") . "/") == false) {
    mkdir($rw_s['srv_dir'] . date("Y") . "/" . date("m") . "/", 0777);
}
if (is_dir($rw_s['srv_dir'] . date("Y") . "/" . date("m") . "/" . date("d") . "/") == false) {
    mkdir($rw_s['srv_dir'] . date("Y") . "/" . date("m") . "/" . date("d") . "/", 0777);
}
if (is_dir($upload_final) == false) {
    mkdir($upload_final, 0777);
    chmod($upload_final, 0777);
}
$my_upload = new file_upload;
if ($api == true) {
    if ($file_url == true) {
        $file->name = basename($file_url);
        $ext = strtolower(strrchr($file->name, "."));
        $uploaded_patch = $upload_final."orig".$ext;
	if (is_file($uploaded_patch)){
    	    @unlink($uploaded_patch);
	}
	$useragent="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.71 Safari/534.24";
        $fp = fopen($uploaded_patch, "w");
        curl_setopt($file_contents, CURLOPT_USERAGENT, $useragent);
        curl_setopt($file_contents, CURLOPT_FILE, $fp);
        curl_setopt($file_contents, CURLOPT_HEADER, 0);
        curl_exec($file_contents);
        if (curl_errno($file_contents) != 0){
            if ($config['log_lvl'] > 0){
                log_add("ERROR: There was a problem with the upload", "upload");
    	    }
    	    echo "ERROR: There was a problem with the upload";
    	    exit(0);
        }
        curl_close($file_contents);
        fclose($fp);
    }else{
        $file = new stdClass;
        $file->name = basename($headers['X-File-Name']);
        $file->size = $headers['X-File-Size'];
        $file->content = file_get_contents("php://input");
	$ext = strtolower(strrchr($file->name, "."));
	$uploaded_patch = $upload_final . "orig" . $ext;
	if (is_file($uploaded_patch)){
    	    @unlink($uploaded_patch);
	}
	file_put_contents($uploaded_patch, $file->content);
    }

    chmod($uploaded_patch, 0777);
    if ($user_id == 0) {
        $group_info['group_settings']['upload_img'] = 10;
    }
} else {
    $my_upload->upload_dir = $upload_final;
    $my_upload->rename_file = true;
    $my_upload->the_temp_file = $_FILES['Filedata']['tmp_name'];
    $my_upload->the_file = $_FILES['Filedata']['name'];
    $my_upload->http_error = $_FILES['Filedata']['error'];
    $my_upload->replace = "n";
    $my_upload->do_filename_check = "n";
    if (!$my_upload->upload("orig")) {
        if ($config['log_lvl'] > 0) {
            log_add("ERROR: " . $my_upload->show_error_string(), "upload");
        }
        echo "ERROR: " . $my_upload->show_error_string();
        exit(0);
    }
    $uploaded_patch = $my_upload->upload_dir . $my_upload->file_copy;
}
// create the object and assign property
if ($config['log_lvl'] > 1) {
    log_add("INFO: File was uploaded filename=" . $file_id, "upload");
}
$full_path = $uploaded_patch;
$info = $my_upload->get_uploaded_file_info($full_path);
if ($info['ext'] != '') {
    $db->sql_query("INSERT INTO " . db_pr . "files VALUES(NULL,'$user_id','$file_id','$srv_id','" . $info['ext_o'] . "','" . $info['ext'] . "','$file_id','0','0')");
    $file_real_id = $db->sql_nextid();

    if ($info['ext'] == "img") {
        if ($info['file_size'] > ($group_info['group_settings']['upload_img'] * 1024 * 1024)) {
            echo "ERROR: Image size is long. Max image size is " . $group_info['group_settings']['upload_img'] . " MB";
            @unlink($full_path);
            @rmdir($upload_final);
            $db->sql_query("DELETE FROM " . db_pr . "files WHERE file_id='$file_real_id'");
            if ($config['log_lvl'] > 0) {
                log_add("ERROR: Image size is long (" . ($info['file_size'] / 1024 / 1024) . " MB). Max image size is: " . $group_info['group_settings']['upload_img'], "upload");
            }
            exit(0);
        }

        $thumb = create_thumb($full_path, $info, $file_id);
        $fp = fopen($upload_final . "thumb.jpg", "w");
        fwrite($fp, $thumb);
        fclose($fp);
        $new_ftime = time();
        if ($config['log_lvl'] > 1) {
            log_add("INFO: Image&Thumbnail was uploaded&created.", "upload");
        }
        $db->sql_query("UPDATE " . db_pr . "files SET file_status='2', file_media_status='2' WHERE file_id='" . $file_real_id . "'");
    } elseif ($info['ext'] == "audio") {
        if ($info['file_size'] > ($group_info['group_settings']['upload_audio'] * 1024 * 1024)) {
            echo "ERROR: Audio size is long. Max audio size is " . $group_info['group_settings']['upload_audio'] . " MB";
            @unlink($full_path);
            @rmdir($upload_final);
            $db->sql_query("DELETE FROM " . db_pr . "files WHERE file_time='$file_id'");
            if ($config['log_lvl'] > 0) {
                log_add("ERROR: Audio size is long (" . ($info['file_size'] / 1024 / 1024) . " MB). Max image size is:" . $group_info['group_settings']['upload_audio'], "upload");
            }
            exit(0);
        }
        include("include/classes/getid3/getid3.php");
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($full_path);
        getid3_lib::CopyTagsToComments($ThisFileInfo);
        $o_ch = $ThisFileInfo['audio']['channels'];
        $o_rate = $ThisFileInfo['audio']['sample_rate'];
        $o_bitrate = ($ThisFileInfo['audio']['bitrate'] > 0) ? ceil($ThisFileInfo['audio']['bitrate']) : 64000;
        $s_rate = ($o_rate > 22050) ? 22050 : $o_rate;
        $value_i = '"ch" => "' . $o_ch . '", "r" => "' . $s_rate . '", "br" => "' . $o_bitrate . '"';
        $db->sql_query("INSERT " . db_pr . "files_media_info VALUES ('$file_real_id','$value_i')");
    } elseif ($info['ext'] == "video") {
        if ($info['file_size'] > ($group_info['group_settings']['upload_video'] * 1024 * 1024)) {
            echo "ERROR: Video size is long (" . ($info['file_size'] / 1024 / 1024) . " MB). Max Video size is " . $group_info['group_settings']['upload_video'] . " MB";
            @unlink($full_path);
            @rmdir($upload_final);
            $db->sql_query("DELETE FROM " . db_pr . "files WHERE file_time='$file_id'");
            if ($config['log_lvl'] > 0) {
                log_add("ERROR: Video size is long. Max image size is:" . $group_info['group_settings']['upload_video'], "upload");
            }
            exit(0);
        }
        exec("ffmpeg -i " . $full_path . " -an -ss 00:00:01 -r 1 -vframes 1 -y -f mjpeg " . $upload_final . "big_img.jpg");
        $info_img['ext_o'] = ".jpg";
        $thumb = create_thumb($upload_final . "big_img.jpg", $info_img, $file_id, $upload_final . $file_id . ".flv");
        $fp = fopen($upload_final . "thumb.jpg", "w");
        fwrite($fp, $thumb);
        fclose($fp);
        include("include/classes/getid3/getid3.php");
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($full_path);
        getid3_lib::CopyTagsToComments($ThisFileInfo);
        $o_x = $ThisFileInfo['video']['resolution_x'];
        $o_y = $ThisFileInfo['video']['resolution_y'];
        $o_r = ($o_x / $o_y);
        $o_ch = $ThisFileInfo['audio']['channels'];
        $o_rate = $ThisFileInfo['audio']['sample_rate'];
        $o_bitrate = ($ThisFileInfo['audio']['bitrate'] > 0) ? ceil($ThisFileInfo['audio']['bitrate']) : 64000;
        $value_i = '"x" => "' . $o_x . '", "y" => "' . $o_y . '", "ra" => "' . $o_r . '", "ch" => "' . $o_ch . '", "r" => "' . $o_rate . '", "br" => "' . $o_bitrate . '"';
        $db->sql_query("INSERT " . db_pr . "files_media_info VALUES ('$file_real_id','$value_i')");
    }

    $file_size_mb = ceil($info['file_size'] / 1024 / 1024);
    $db->sql_query("UPDATE " . db_pr . "files_servers SET srv_used=(srv_used+$file_size_mb) WHERE srv_id='$srv_id'");
    echo "FILEID:" . md5($file_id);
}
?>