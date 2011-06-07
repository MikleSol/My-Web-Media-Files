<?
/*
		Media hosting project
		Version 1.0-beta
		files.php
		29.11.2010
		Scripted by Poluboyarinov Mikhail
		mikle.sol@gmail.com
*/
	include("include/config.php");

	$image_id = isset($_GET["file"]) ? $_GET["file"] : false;
	if($image_id == false){
	header("HTTP/1.0 404 Not Found");
	exit(0);
	}
	global $db;
	list($file,$exts)=explode(".",$image_id);
	list($file_id,$size)=explode("_",$file);

	$sql=$db->sql_query("SELECT
		* 
	FROM 
		".db_pr."files 
	LEFT JOIN 
		".db_pr."files_servers 
	ON 
		".db_pr."files.file_server=".db_pr."files_servers.srv_id
	WHERE 
		md5(file_time)='$file_id'
	");

	if($db->sql_numrows($sql) == 0){
		header("HTTP/1.0 404 Not Found");
		exit(0);
	}

	if(strlen($size) > 0 AND $size != "hd"){
		echo "<script>location.href='http://mediahosting.ru/#view_".$file_id."'</script>";
		exit(0);
	}

	$rw=$db->sql_fetchrow($sql);
	$file_dir=$rw['srv_dir'].date("Y",$rw['file_time'])."/".date("m",$rw['file_time'])."/".date("d",$rw['file_time'])."/".$rw['file_time']."/";
	$file_path=date("Y",$rw['file_time'])."/".date("m",$rw['file_time'])."/".date("d",$rw['file_time'])."/".$rw['file_time']."/";

	if($_GET['thumb'] == 1){

		switch ($rw[file_type]){
		  case "img":
		  case "video":
			$file_name="thumb.jpg";
			$file_content_type="image/jpeg";
		  break;
		  case "audio":
			$file_name="small.mp3";
			$file_content_type="audio/mp3";
		  break;
		  default:
			header("HTTP/1.0 404 Not Found");
			exit(0);
	 	  break;
		}
	}else{
		if($rw['file_media_status'] == 0){
			header("HTTP/1.0 404 Not Found");
			exit(0);
		}

		$file_get_type=$_GET['ft'];
		switch ($file_get_type){
		  case "img":
		    $file_name="orig".$rw[file_ext];
		    switch ($rw['file_ext']){
					case ".jpg":
					case ".jpeg":
						$file_content_type="image/jpeg";
					break;
					case ".gif":
						$file_content_type="image/gif";
					break;
					case ".png":
						$file_content_type="image/png";
					break;
					case ".bmp":
						$file_content_type="image/bmp";
					break;
					case ".tif":
					case ".tiff":
						$file_content_type="image/tiff";
					break;
				}
	    break;
		  case "audio":
				include_once($config['base_dir']."include/classes/getid3/getid3.php");
				$file_name=file_name_hd($size,$rw,$file_dir,"mp3");
				$file_content_type="audio/mp3";
				$getID3 = new getID3;
				$ThisFileInfo = $getID3->analyze($file_dir.$file_name);
				getid3_lib::CopyTagsToComments($ThisFileInfo);
				if(isset($ThisFileInfo['comments_html']['artist'][0])){
					$file_dwn_name=@$ThisFileInfo['comments_html']['artist'][0].'-'.@$ThisFileInfo['comments_html']['title'][0].$rw['file_ext'];
				}else{
					$file_dwn_name=$file_id.$rw['file_ext'];
				}
	    break;
		  case "video":
		    $file_name=file_name_hd($size,$rw,$file_dir,$exts);
				$file_content_type="video/x-flv";
				$file_dwn_name=$file_name;
	    break;
		  default:
				header("HTTP/1.0 404 Not Found");
				exit(0);
	    break;
		}
	}

	$file=$file_dir.$file_name;
	$file_ngx=$file_path.$file_name;

		if(is_file($file) == false){
			header("HTTP/1.0 404 Not Found");
			exit(0);
		}
	$last_used=time();
	$db->sql_query("UPDATE ".db_pr."files SET file_used='$last_used' WHERE md5(file_time)='$file_id'");
	header("Content-Type: ".$file_content_type);
	header("Content-Length: ".filesize($file));
	if($_GET['thumb'] == false AND $file_get_type!="img"){ header('Content-Disposition: attachment; filename="'.$file_dwn_name.'"'); }
//	readfile($file_ngx);
	if($user->check_auth($_COOKIE['session_key']) == false){
	  header("X-Accel-Limit-Rate: 55536");
	}
	header("X-Accel-Redirect: /".$file_ngx);
	
	function file_name_hd($size,$rw,$file_dir,$ext){
		$orig=is_file($file_dir."orig.".$ext);
		$small=is_file($file_dir."small.".$ext);
		$hdv=is_file($file_dir."hd".$ext);

		if($orig == false AND $small == false AND $hdv == false){
			header("HTTP/1.0 404 Not Found");
			exit(0);
		}
		if($size == "hd" AND $hdv == true){
			$file_name="hd.mp4";
		}elseif($size == "hd" AND $orig == true){
			$file_name="orig.".$ext;
		}elseif($small == true){
			$file_name="small.".$ext;
		}else{
			$file_name="orig.".$ext;
		}
		return $file_name;
	}

?>