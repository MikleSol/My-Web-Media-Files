<?php
if ($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest") {
    echo "<script>location.href='/'</script>";
    exit();
}
include("../include/config.php");
$image_id = isset($_GET["file"]) ? $_GET["file"] : false;

if ($image_id == false) {
    echo "<script>location.href='/index.php';</script>";
}

global $db;

$sql = $db->sql_query("
		SELECT 
			" . db_pr . "files.file_uid, " . db_pr . "files.file_time, " . db_pr . "files.file_server, " . db_pr . "files.file_type, " . db_pr . "files.file_ext, " . db_pr . "files.file_status, " . db_pr . "files.file_media_status,
			" . db_pr . "files_servers.srv_url,
			" . db_pr . "files_meta.name, " . db_pr . "files_meta.text, " . db_pr . "files_meta.privacy
		FROM 
			" . db_pr . "files 
		LEFT JOIN
			" . db_pr . "files_servers
		ON
			" . db_pr . "files.file_server=" . db_pr . "files_servers.srv_id
		LEFT JOIN
			" . db_pr . "files_meta
		ON
			" . db_pr . "files.file_id=" . db_pr . "files_meta.file_meta_id
		WHERE 
			md5(file_time)='$image_id'
	");

if ($db->sql_numrows($sql) == 0) {
    echo "<script>tab_close('#view_<?=$image_id;?>');alert('Данного файла не существует');</script>";
    exit();
}
$rw = $db->sql_fetchrow($sql);
$user_id = $user->check_auth($_COOKIE['session_key']);


echo '<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="close_' . $image_id . '" href="#index" style="font-size: 10px;">Закрыть</a></div>';
if ($rw['privacy'] == 0 AND $rw['file_uid'] != $user_id AND $rw['file_uid'] != 0) {
    echo "<BR><center>Данный файл доступен только хозяину</center>";
    exit();
} elseif ($rw['privacy'] == 1 AND $user_id == false) {
    echo "<BR><center>Данный файл доступен только зарегенстрированным пользователями</center>";
    exit();
}

$file_url = $rw[srv_url] . $rw[file_type] . "_" . $image_id;
$page_url = "http://" . $_SERVER['SERVER_NAME'] . "/#view_" . $image_id;
$html_code = '<a href="http://' . $_SERVER['SERVER_NAME'] . '/#view_' . $image_id . '"><img border="0" src="' . $rw[srv_url] . 'thumb_' . $image_id . '"></a>';
$bb_code = '[url=http://' . $_SERVER['SERVER_NAME'] . '/#view_' . $image_id . '][img]' . $rw[srv_url] . 'thumb_' . $image_id . '[/img][/url]';

switch ($rw[file_type]) {
    case "img":
        if ($rw[file_status] == 0) {
            $write_file = "Поставлен в очередь на обработку";
        } elseif ($rw[file_status] == 1) {
            $write_file = "Проходит обработку";
        } elseif ($rw[file_status] == 2) {
            $write_file = "<img src=\"" . $rw[srv_url] . "thumb_" . $image_id . "\"><BR><a target=\"_blank\" href=\"" . $rw[srv_url] . $rw[file_type] . "_" . $image_id . $rw['file_ext'] . "\">Посмотреть в оригинале</a><BR>";
        }
        break;
    case "video":

        if ($rw['file_status'] == 2) {
            ?>
            <script type="text/javascript">
                jwplayer("v_player").setup({
                    flashplayer: "/images/player/player.swf",
                    image: "<?= $rw['srv_url']; ?>thumb_<?= $image_id; ?>.jpg",
                    file: "<?= $rw['srv_url']; ?>video_<?= $image_id; ?>.mp4",
                    width: 400,
                    height: 300,
                    skin: '/images/player/newtube.zip',
            <? if ($rw['file_media_status'] == 2) { ?>
                               plugins: {
                                   '/images/player/hd.swf': {
                                       file: "<?= $rw['srv_url']; ?>video_<?= $image_id; ?>_hd.mp4"
                                   }
                                   //'ltas': {
                                   //    'cc': 'hmkpmclgadzgnqh'
                                   //}
                               }
            <? } ?>
                       });
            </script>
            <?
        }
        if ($rw[file_status] == 0) {
            $write_file = "<img src=\"" . $rw[srv_url] . "thumb_" . $image_id . "\"><BR>поставлен в очередь на обработку";
        } elseif ($rw[file_status] == 1) {
            $write_file = "<img src=\"" . $rw[srv_url] . "thumb_" . $image_id . "\"><BR>Файл проходит обработку";
        } elseif ($rw[file_status] == 2) {
            $write_file = '<div id="v_player" style="display:block;width:400px;height:300px;"></div>';
            if ($rw['file_media_status'] == 2) {
                $write_file.='<a href="' . $rw[srv_url] . $rw[file_type] . '_' . $image_id . '_hd.mp4">Сохранить в наилучшем качестве</a><BR>';
            } else {
                $write_file.='<a href="' . $rw[srv_url] . $rw[file_type] . '_' . $image_id . '.mp4">Сохранить</a><BR>';
            }
        }
        break;
    case "audio":
        if ($rw[file_status] == 0) {
            $write_file = "поставлен в очередь на обработку";
        } elseif ($rw[file_status] == 1) {
            $write_file = "проходит обработку";
        } elseif ($rw[file_status] == 2) {
            $write_file = '
                            <audio controls>
                                <source src="' . $rw[srv_url] . $rw[file_type] . '_' . $image_id . '.mp3" />
                            </audio>
                            <br>
                            <a href="' . $rw[srv_url] . $rw[file_type] . '_' . $image_id . '.mp3">Сохранить</a>
                            <!--<div id="a_play" style="display:block;width:400px;height:30px;"></div>--><BR>';
        }
        break;
}
?>

<center>
    <?= ($rw[name] == true) ? $rw[name] . '<BR>' : ''; ?>
    <?= $write_file; ?><BR>
    <?= ($rw[text] == true) ? $rw[text] . '<BR>' : ''; ?>
    <BR>
    <table width="100%" border="0">
    <!--<tr><td width="150">File URL:</td><td><input type="text" style="width:100%" OnMouseOver="select( this );" onMouseOut="select(false);" readonly value="<?= $file_url; ?>"></td></tr>-->
        <tr><td>Page URL:</td><td><input type="text" style="width:100%" OnMouseOver="select( this );" onMouseOut="select(false);" readonly value="<?= $page_url; ?>"></td></tr>
        <tr><td>HTML Code:</td><td><input style="width:100%" type="text" OnMouseOver="select( this );" onMouseOut="select(false);" readonly value='<?= $html_code; ?>'></td></tr>
        <tr><td>BB Code:</td><td><input type="text" OnMouseOver="select( this );" onMouseOut="select(false);" style="width:100%" readonly value="<?= $bb_code; ?>"></td></tr>
        <? if ($user_id > 0 AND $rw[file_uid] == $user_id) { ?>
            <tr><td>Remove Link:</td><td><a href="javascript:;" id="removel_<?= $image_id; ?>">http://<?= $_SERVER['SERVER_NAME']; ?>/#remove_<?= $image_id; ?></a></td></tr>
        <? } ?>
    </table>
    <BR>
    <form id="email">
        Отправить ссылки на E-mail:<BR>
        <input type="text" name="email">
        <input type="submit" value="Отправить"><BR>
    </form>
</center>
<script type="text/javascript">
    new Ya.share({
        'element': 'shared_link',
        'description': 'Video File From my web media files',
        'image': '<?= $rw['srv_url']; ?>thumb_<?= $image_id; ?>.jpg',
        'elementStyle': {
            'type': 'button',
            'linkIcon': true,
            'border': false,
            'quickServices': ['yaru', 'vkontakte', 'facebook', 'twitter', 'odnoklassniki', 'friendfeed', 'moimir', 'lj']
        },
        'popupStyle': {
            'copyPasteField': true
        }
    });
</script>
<BR>
<div id="shared_link"></div><BR>

<script type="text/javascript">
    $(document).ready(function() { 
        var options = { 
            beforeSubmit: validate,
            success:       showResponse,
            url:       '/ajax_email',
            type:      'post',
            data: { file_id: '<?= $image_id; ?>' }
        }; 
 
        // bind to the form's submit event 
        $('#email').submit(function() { 
            $(this).ajaxSubmit(options); 
            return false; 
        }); 
        $('#close_<?= $image_id; ?>').click(function(){
            tab_close('#view_<?= $image_id; ?>');
        });

        $('#removel_<?= $image_id; ?>').click(function(){
            tab_add('#remove_<?= $image_id; ?>','Удаление файла','/ajax_remove?file=<?= $image_id; ?>');
        });  		
    }); 

    function validate(formData, jqForm, options){
        var form = jqForm[0]; 
        if (!form.email.value) { 
            alert('Вы не ввели e-mail'); 
            return false; 
        } 

        if (echeck(form.email.value)==false){
            form.email.value=""
            form.email.focus()
            return false
        }
    }

    function echeck(str) {

        var at="@"
        var dot="."
        var lat=str.indexOf(at)
        var lstr=str.length
        var ldot=str.indexOf(dot)
        if (str.indexOf(at)==-1){
            alert("Неверный E-mail")
            return false
        }

        if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
            alert("Неверный E-mail")
            return false
        }

        if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
            alert("Неверный E-mail")
            return false
        }

        if (str.indexOf(at,(lat+1))!=-1){
            alert("Неверный E-mail")
            return false
        }

        if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
            alert("Неверный E-mail")
            return false
        }

        if (str.indexOf(dot,(lat+2))==-1){
            alert("Неверный E-mail")
            return false
        }
		
        if (str.indexOf(" ")!=-1){
            alert("Неверный E-mail")
            return false
        }

        return true					
    }
 
    function showResponse(responseText, statusText, xhr, $form)  { 
        alert(responseText);
        $('#email').hide();
    } 
</script>
