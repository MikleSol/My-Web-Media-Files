<?php
/*
		Media hosting project
		Version 1.0-beta
		index.php
		29.11.2010
		Scripted by Poluboyarinov Mikhail
		mikle.sol@gmail.com
*/
	session_start();
	$_SESSION["file_info"] = array();


	include("include/config.php");
	include("tpl/head.html");
	$sql_s=$db->sql_query("SELECT srv_id as srv, srv_url, srv_script FROM ".db_pr."files_servers WHERE (srv_quote-srv_used) > '2048' AND srv_status='1' ORDER by RAND() Limit 0,1");
	$rw_s=$db->sql_fetchrow($sql_s);
	$group_info=$user->group_info($user_id);
?>

			<div id="index">
	<?
	if($rw_s['srv'] > 0){
	?>
<script type="text/javascript">
		var swfu;

		window.onload = function() {
			var settings = {
				flash_url : "/images/uploader/swfupload.swf",
				upload_url: "<?=$rw_s['srv_url'];?>/<?=$rw_s['srv_script'];?>",
				file_size_limit : "<?=$group_info['group_settings']['upload_video']?> MB",
				file_types : "*.jpg;*.jpeg;*.gif;*.png;*.bmp;*.tif;*.tiff;*.mp3;*.wav;*.ogg;*.wma;*.avi;*.mpg;*.wmv;*.mov;*.flv",
				file_types_description : "All supported.",
				file_upload_limit : 0,
				file_queue_limit : 0,
				post_params: {session_key: '<?=$_COOKIE['session_key'];?>'},

				debug: false,

				button_image_url: "/images/uploader/XPButtonUploadText_61x22.png",
				button_width: "61",
				button_height: "22",
				button_placeholder_id: "spanButtonPlaceHolder",
				
				moving_average_history_size: 40,
				
				file_queued_handler : fileQueued,
				file_dialog_complete_handler: fileDialogComplete,
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				
				custom_settings : {
					tdCurrentSpeed : document.getElementById("tdCurrentSpeed"),
					tdTimeRemaining : document.getElementById("tdTimeRemaining"),
					tdTimeElapsed : document.getElementById("tdTimeElapsed"),
					tdPercentUploaded : document.getElementById("tdPercentUploaded"),
					tdSizeUploaded : document.getElementById("tdSizeUploaded"),
				}
			};

			swfu = new SWFUpload(settings);
	  };
</script>
		<div id="dialog-modal" title="Идет загрузка">
			<center>
				<table align="center" width="100%">
					<tr><td align="center" id="progressbar"></td></tr>
					<tr><td id="tdPercentUploaded" align="center"></td></tr>
				</table>
				<table cellspacing="0" align="center">
				<tr><td>
					<table>
					<tr>
					<td>Загруженно:</td>
					<td id="tdSizeUploaded"></td>
					</tr>		
					</table>
				</td><td>
					<table>
					<tr>
					<td>Скорость:</td>
					<td id="tdCurrentSpeed"></td>
					</tr>	
					</table>
				</td></tr>
				<tr><td>
					<table>
					<tr>
					<td>Осталось времени:</td>
					<td id="tdTimeRemaining"></td>
					</tr>		
					</table>
				</td><td>
					<table>
					<tr>
					<td>Прошло времени:</td>
					<td id="tdTimeElapsed"></td>
					</tr>			
					</table>
				</td></tr>
				</table>
			</center>
	</div>

	<center>
		<form id="form_upload" method="post" enctype="multipart/form-data">
			<div style="width: 61px; height: 22px; margin-bottom: 10px;">
				<span id="spanButtonPlaceHolder"></span>
			</div>
		</form>
	</center>
	<?
	}else{
	?>
		<center>
		Приносим извенения.<BR>В данный момент все Наши сервера переполнены.<BR>В ближайшее время время проблема будет устранена.<BR>Попробуйте зайти позже.
		</center>
	<?
	}
	?>
	<b>На сайте можно разместить</b>
	<p>
	<b>1. Графические файлы</b> в форматах <b>JPG, JPEG, GIF, PNG, BMP, TIF, TIFF</b>;<BR>
	<b>2. Аудио файлы</b> в формате <b>MP3, WAV, OGG, WMA</b>;<BR>
	<b>3. Видео файлы</b> в формате <b>AVI, MPG, WMV, MOV, FLV</b>;<BR>
	<BR><b>Примечание:</b><br>
	Аудио файлы в форматах <b>WAV, OGG, WMA</b> преобразуются в формат <b>MP3</b>.<BR>
	Видео файлы в форматах <b>AVI, MPG, WMV, MOV</b> преобразуются в формат <b>FLV</b>.<BR><BR>

	<center>
		<b>Отличие гостей от пользователей</b>
		<table border="1" class="uinfo">
			<tr><td></td><td>Гость</td><td>Пользователь</td></tr>
			<tr><td colspan="4">Лимит на загружаемый файл</td></tr>
			<tr><td>Графические файлы</td><td>2 Мбайта</td><td>10 Мбайт</td></tr>
			<tr><td>Аудио файлы</td><td>50 Мбайт</td><td>300 Мбайт</td></tr>
			<tr><td>Видео файлы</td><td>500 Мбайт</td><td>2048 Мбайт</td></tr>
			<tr><td>Скорость скачивания файлов</td><td>60 кб/с</td><td>Нету</td></tr>
			<tr><td>Защита от автозагрузок и скачиваний</td><td>Есть</td><td>Нету</td></tr>
			<tr><td>Срок хранения файлов</td><td>1 месяц при не использовании файла</td><td>Нет</td></tr>
		</table>
	</center>
	<BR>
	</p><BR>
	<p>Загружая файл Вы соглашаетесь со всеми <a href="javascript:;" onclick='$("#upload_box").tabs("select","#ui-tabs-2");'>правилами проекта</a>.</p>

			</div> 
	</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-20779400-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?
include("tpl/footer.html");
?>
