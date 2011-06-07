<?

if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}

include("../include/config.php");
if($_POST['create_dir'] == "true"){
    $session_key=$_POST['session_key'];
    $user_id=$user->check_auth($session_key);
    $name=htmlspecialchars($_POST['dir_name']);
    if($user_id == false){
	echo "auth";
	exit();
    }
    
    if($db->sql_query("INSERT INTO ".db_pr."users_dirs VALUES (NULL,'$user_id','$name')")){
	echo $name;
    }else{
	echo "base";
    }
    
    exit();
}
$user_id=$user->check_auth($_COOKIE['session_key']);
if($user_id == false){
	if($_POST['file_id'] == false){
	    echo "<script>tab_close('#files');</script>";
	}
	exit();
}

if($_POST['file_id'] == true){
    $file_id=$_POST['file_id'];
    $type=htmlspecialchars($_POST['type']);
    $value=htmlspecialchars($_POST['str']);
    if(!eregi('[0-9]',$file_id)){
	exit();
    }
    $q1=$db->sql_query("SELECT file_id FROM ".db_pr."files WHERE file_id='$file_id'");
    if($db->sql_numrows($q1) == 0){
	exit();
    }
    $q2=$db->sql_query("SELECT file_meta_id FROM ".db_pr."files_meta WHERE file_meta_id='$file_id'");
    if($db->sql_numrows($q2) == 0){
	if($type == "text"){
	    $db->sql_query("INSERT INTO ".db_pr."files_meta (`file_meta_id`,`".$type."`, `privacy`) VALUES ('$file_id','$value','2')");
	}elseif($type == "privacy"){
	    $db->sql_query("INSERT INTO ".db_pr."files_meta (`file_meta_id`,`text`, `".$type."`) VALUES ('$file_id','','".$value."')");
	}
	echo "INS";
    }else{
//        echo "UPDATE ".db_pr."files_meta SET $type='$value' WHERE file_meta_id='$file_id'";
//        print_r($_POST);
	$db->sql_query("UPDATE ".db_pr."files_meta SET $type='$value' WHERE file_meta_id='$file_id'");
	echo "UPD";
    }
    exit();
}
?>
<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="close_f" href="#index" style="font-size: 10px;">Закрыть</a></div>
<center>
<b>Мои файлы</b><BR>

<?

	    
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
	    ".db_pr."files.file_uid='$user_id' AND user_file_id IS NULL
	    ";
$sql=$db->sql_query($sqs);
if($db->sql_numrows($sql) == 0){
	echo "У Вас нет ни одного загруженного на сервер файла";
}else{
    
?>
<!--	<a id="dir_create" href="javascript:;">Создать папку</a> -->
	<table border="1" class="uinfo" id="drag_drop">
	<thead>
	<tr><td>Имя</td><td>Описание</td><td>Кто?</td><td>Действия</td>
	</tr>
	</thead>
	<tbody>
<?
$type=array("img" => "Image", "video" => "Video", "audio" => "Audio");
$status=array("В очереди","Обрабатывается", "Обработан");
$media_status=array("Не обработан","Частичная обработка", "Полность обработан", "Полность обработан");
$privacy=array("Me","User","All");
	while($rw=$db->sql_fetchrow($sql)){
	    $title_thumb=($rw[file_type] != "audio")?"<center><img width='200' src='".$rw[srv_url]."/thumb_".md5($rw[file_time]).".jpg'></center><BR>":"";
	    $title_t="
	    $title_thumb
	    Тип файла: ".$type[$rw[file_type]]."<BR>
	    Дата загрузки: ".date('d.m.Y',$rw[file_time])."<BR>
	    Дата последней активности: ".date('d.m.Y',$rw[file_used])."<BR>
	    Статус файла: ".$status[$rw[file_status]]."<BR>
	    Медия статус файла: ".$media_status[$rw[file_media_status]]."<BR>
	    ";
		echo "
		<tr class=\"sorted\" title=\"".$title_t."\" fid=\"".md5($rw[file_time])."\">
		    <td class=\"edit_name\" id=\"".$rw[file_id]."\">".$rw[name]."</td>
		    <td class=\"edit_text\" id=\"".$rw[file_id]."\">".$rw[text]."</td>
		    <td class=\"edit_privacy\" id=\"".$rw[file_id]."\">".$privacy[$rw['privacy']]."</td>
		    <td><a href=\"/#view_".md5($rw[file_time])."\" class=\"open_file\" id=\"".md5($rw[file_time])."\">O</a> | <a href=\"javascript:;\" class=\"del_file\">D</a></td>
		</tr>";
	}
	
//	$sqs2="SELECT 
//	    *
//	FROM 
//	    ".db_pr."users_dirs
//	WHERE 
//	    ".db_pr."users_dirs.dir_uid='$user_id'
//	ORDER BY
//	    ".db_pr."users_dirs.dir_pos
//	ASC
//	    ";	
	    
//    function filesdirs($dir_id){
//	global $db, $type, $status, $media_status, $privacy;
//	$sql="
//	SELECT 
//	    *
//	FROM 
//	    ".db_pr."files
//	LEFT JOIN
//	    ".db_pr."files_servers
//	ON
//	    ".db_pr."files.file_server=".db_pr."files_servers.srv_id
//	LEFT JOIN
//	    ".db_pr."files_meta
//	ON
//	    ".db_pr."files.file_id=".db_pr."files_meta.file_meta_id
//	LEFT JOIN
//	    ".db_pr."users_dirsfiles
//	ON
//	    ".db_pr."files.file_id=".db_pr."users_dirsfiles.user_file_id
//	WHERE 
//	    ".db_pr."users_dirsfiles.user_dir_id='$dir_id'
//	";
//	
//	$vdir=$db->sql_query($sql);
//	if($db->sql_numrows($vdir) == 0){
//	    $ret='<tr><td colspan="4">В данной папке файлов не найдено</td></tr>';
//	}else{
//	    while($rw=$db->sql_fetchrow($vdir)){
//	    $title_thumb=($rw[file_type] != "audio")?"<center><img width='200' src='".$rw[srv_url]."thumb_".md5($rw[file_time]).".jpg'></center><BR>":"";
//	    $title_t="
//	    $title_thumb
//	    Тип файла: ".$type[$rw[file_type]]."<BR>
//	    Дата загрузки: ".date('d.m.Y',$rw[file_time])."<BR>
//	    Дата последней активности: ".date('d.m.Y',$rw[file_used])."<BR>
//	    Статус файла: ".$status[$rw[file_status]]."<BR>
//	    Медия статус файла: ".$media_status[$rw[file_media_status]]."<BR>
//	    ";
//		$ret.="
//		<tr class=\"sorted\" style=\"display: none;\" id=\"dr_".$dir_id."\" title=\"".$title_t."\" fid=\"".md5($rw[file_time])."\">
//		    <td class=\"edit_name\" id=\"".$rw[file_id]."\">".$rw[name]."</td>
//		    <td class=\"edit_text\" id=\"".$rw[file_id]."\">".$rw[text]."</td>
//		    <td class=\"edit_privacy\" id=\"".$rw[file_id]."\">".$privacy[$rw['privacy']]."</td>
//		    <td><a href=\"javascript:;\" class=\"open_file\" id=\"".md5($rw[file_time])."\">O</a> | <a href=\"javascript:;\" class=\"del_file\">D</a></td>
//		</tr>";
//    	    }
//	}
//	
//	return $ret;
//    
//    }
//
//	$sdir=$db->sql_query($sqs2);
//	while($rwd=$db->sql_fetchrow($sdir)){
//	    echo '<tr><td colspan="4">'.$rwd['dir_name'].' <a href="javascript:;" id="'.$rwd['dir_id'].'" class="view_dir">+</a></td></tr>';
//	    echo filesdirs($rwd['dir_id']);
//	}




?>
	</tbody>
	</table>
<?}?>
<span id="debug"></span>

</center>

<!--<div id="dialog-dircreate" title="Создать директорию">
<form id="create_dir">
<table align="center">
<tr><td>Название:</td><td><input type="text" name="dir_name"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Создать"> <input type="button" value="Cancel" onclick="$('#dialog-dircreate').dialog('close');"></td></tr>
</table>
</form>
</div>-->

<script type="text/javascript">
    var edit_n=[],edit_t=[], id, text, type, th, disp=[], $ii=0;

$('.sorted').tooltip();

$('.open_file').click(function(){
    var id=$(this).attr('id');
    tab_add('#view_'+id,'Информация о файле','/ajax_view?file='+id);
});

//$('.view_dir').click(function(){
//    var id=$(this).attr('id');
//    if($('#dr_'+id+':first').is(':hidden')){
//	$('#dr_'+id).show(500);
//	$(this).text("-");
//    }else{
//	$('#dr_'+id).hide(500);
//	$(this).text("+");
//    }
//});

//var options_create_dir = { 
//    success:	function(responseText, statusText, xhr, $form){
//	if(responseText == "base"){
//	    alert("Ошибка базы данных");
//	}else if(responseText == "auth"){
//	    alert("Ошибка авторизации");
//	}else{
//	$('#drag_drop > tbody').append('<tr><td colspan="4">'+responseText+'</td></tr>');
//	}
//	$('#dialog-dircreate').dialog('close');
//    },
//    url:       '/ajax_files',
//    type:      'post',
//    data: { create_dir: 'true', session_key: '<?=$_COOKIE['session_key'];?>' }
//}; 
// 
//$('#create_dir').submit(function() { 
//    $(this).ajaxSubmit(options_create_dir); 
//    return false; 
//});  
//
//$('#dir_create').click(function (){
//    $('#dialog-dircreate').dialog('open');
//});
//
//$( "#dialog-dircreate" ).dialog({
//    width: 360,
//    autoOpen: false,
//    resizable: false,
//    closeOnEscape: true,
//    draggable: true,
//    modal: true
//});


$('.edit_name').click(function(){
    id = $(this).attr('id');
    text = $(this).text();
    type = 'name';
    th = this;
    if(edit_n[id] != 1){
	$(this).html('<input type="text" id="'+type+'_'+id+'" value="'+text+'" onChange="ed_sb();" onBlur="ed_cn();" onKeyUp="return ed_en(event);">');
	$('#'+type+'_'+id).focus();
	edit_n[id]=1;
    }
});

$('.edit_text').click(function(){
    id = $(this).attr('id');
    text = $(this).text();
    type = 'text';
    th=this;
    if(edit_n[id] != 1){
	$(this).html('<textarea id="'+type+'_'+id+'" onChange="ed_sb();" onBlur="ed_cn();" onKeyUp="return ed_en(event);">'+text+'</textarea>');
	$('#'+type+'_'+id).focus();
	edit_n[id]=1;
    }
});

$('.edit_privacy').click(function(){
    id = $(this).attr('id');
    text = $(this).text();
    type = 'privacy';
    th=this;
    if(edit_n[id] != 1){
	$(this).html('<select id="'+type+'_'+id+'" onChange="ed_sb();" onBlur="ed_cn();" onKeyUp="return ed_en(event);"><option value="0">Me</option><option value="1">User</option><option value="2">All</option></select>');
	$('#'+type+'_'+id).focus();
	edit_n[id]=1;
    }
});

function ed_en(event){
    if(event.which == '13'){
	ed_sb();
    }a
}

function ed_sb(){
    var ntext = $('#'+type+'_'+id).val();
//        alert(ntext);
    if(ntext != undefined){
        edit_t[id]= 1;
        edit_n[id]= 0;        
        if(type == 'privacy'){
            var priv = ['Me', 'User', 'All'];
            $(th).text(priv[ntext]);
        }else{
            $(th).text(ntext);
        }

        $.ajax({
            url: "/ajax_files",
            type: "post",
            data: { file_id: id, type: type, str: ntext },
//            success:	function(responseText, statusText, xhr, $form){
//                $('#debug').text(responseText+edit_t[id]);
//            }
        });
        return false;
    }
}
function ed_cn(){
    var ntext = $('#'+type+'_'+id).val();
    if(text == ntext){
	$(th).text(text);
	edit_n[id]=0;
    }else{
	ed_sb();
    }
}
$(document).ready(function() { 
		$('#close_f').click(function(){
                    tab_close('#files');
		}); 
}); 
</script>
