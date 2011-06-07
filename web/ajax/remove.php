<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}
	include("../include/config.php");
	$image_id = isset($_GET["file"]) ? $_GET["file"] : false;
	global $db;
	$user_id=$user->check_auth($_COOKIE['session_key']);
	if($user_id == false){
		echo "<script>tab_close('#remove_<?=$image_id;?>');</script>";
		exit();
	}
	$sql=$db->sql_query("SELECT ".db_pr."files.file_id,".db_pr."files.file_time,".db_pr."files.file_type,".db_pr."files_servers.srv_url FROM ".db_pr."files LEFT JOIN ".db_pr."files_servers ON ".db_pr."files.file_server=".db_pr."files_servers.srv_id WHERE md5(file_time)='$image_id' AND file_uid = '$user_id'");

	if($db->sql_numrows($sql) == 0){
		echo "<script>location.href='/index.php';</script>";
	}
	$rw=$db->sql_fetchrow($sql);

$type=array("img" => "Изображение", "video" => "Видео файл", "audio" => "Аудио файл");
?>
<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="rclose_<?=$image_id;?>" href="#index" style="font-size: 10px;">Закрыть</a></div>
Вы действительно хотите удалить данный файл?<BR>
<?=$type[$rw[file_type]];?><BR>
<a href="javascript:;" id="yes_<?=$image_id;?>">Да</a> • <a href="javascript:;" id="no_<?=$image_id;?>">Нет</a>
<BR><span id="debug"></span>
<script type="text/javascript">
$(document).ready(function() { 
    $('#yes_<?=$image_id;?>').click(function() { 
        $.ajax({
            success:       showResponse,
            cache: false,
    	    url:       '<?=$rw['srv_url'];?>remove.php',
    	    type:      'post',
	    data: { file: '<?=$image_id;?>', session_key: '<?=$_COOKIE['session_key'];?>' }
        }); 
        return false; 
    }); 

		$('#rclose_<?=$image_id;?>').click(function(){
				tab_close('#remove_<?=$image_id;?>');
		});
		$('#no_<?=$image_id;?>').click(function(){
				tab_close('#remove_<?=$image_id;?>');
		});
}); 

function showResponse(responseText, statusText, xhr, $form)  {
    var res=responseText;
    if(res == "auth"){
	alert('Вы не авторизованы в системе');
    }else if(res == "file"){
	alert('Запрашиваемый файл на удаление не найден');
    }else if(res == "true"){
	alert("Файл удален");
	tab_close('#remove_<?=$image_id;?>');
	tab_close('#view_<?=$image_id;?>');
    }
} 
</script>
