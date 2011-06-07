<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}
include("../include/config.php");
?>
<center>
<b>Список загруженных файлов на сервер</b><BR>

<?
$sql=$db->sql_query("SELECT * FROM ".db_pr."files ORDER BY file_id DESC ");

if($db->sql_numrows($sql) == 0){
	echo "Нет ни одного загруженного на сервер файла";
}else{
?>
	<table border="1" class="uinfo">
	<tr><td>Тип</td><td>Дата загрузки</td><td>Последний раз<BR>использовался</td><td>Статус</td><td>Медия статус</td></tr>
<?
$type=array("img" => "Изображение", "video" => "Видео файл", "audio" => "Аудио файл");
$status=array("В очереди","Обрабатывается", "Обработан");
$media_status=array("Не обработан","Первичная обработка", "Обработан полностью");
	while($rw=$db->sql_fetchrow($sql)){
		echo "<tr><td><a href=\"#view_".md5($rw[file_time])."\" onclick=\"tab_add('#view_".md5($rw[file_time])."','Информация о файле','/ajax_view?file=".md5($rw[file_time])."');\">".$type[$rw[file_type]]."</a></td><td>".date('d.m.Y',$rw[file_time])."</td><td>".date('d.m.Y',$rw[file_used])."</td><td>".$status[$rw[file_status]]."</td><td>".$media_status[$rw[file_media_status]]."</td></tr>";
	}
?>
	</table>
<?}?>


</center>

<script type="text/javascript">
$(document).ready(function() { 
		$('#close_f').click(function(){
				tab_close('#files');
		}); 
}); 
</script>
