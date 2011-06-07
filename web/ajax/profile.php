<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}
include("../include/config.php");
$user_id=$user->check_auth($_COOKIE['session_key']);
if($user_id == false){
	echo "<script>tab_close('#profile');</script>";
	exit();
}

$user_info=$user->user_info($user_id);

if($_POST['edit'] == true){
	$pwt=$user->check_login($user_info['user_email'],$_POST['oldpasswd']);
	if($pwt == false){
		echo "oldpasswd";
	}else{
		if(strlen($_POST['passwd']) > 4){
			if($_POST['passwd'] != $_POST['passwd1']){
				echo "npwd";
				exit();
			}else{
				$newpasswd=", user_passwd='".md5($_POST['passwd'])."'";
			}
		}
		$query="UPDATE ".db_pr."users SET user_name='".$_POST['name']."', user_type='".$_POST['type']."' $newpasswd WHERE user_id='".$pwt['user_id']."'";
		if($db->sql_query($query)){
			echo "true";
		}else{
			echo "base";
		}
	}
	exit();
}
?>
<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="close_p" href="#index" style="font-size: 10px;">Закрыть</a></div>
<center><b>Изменение профиля</b>
<table>
<form id="changeme">
<tr><td>Имя:</td><td><input type="text" name="name" value="<?=$user_info['user_name'];?>"><span id="error_name"></span></td></tr>
<tr><td>Новый E-mail:</td><td><input type="text" name="email" disabled  value="<?=$user_info['user_email'];?>"><span id="error_email"></span></td></tr>
<tr><td>Новый Пароль<sup>*</sup>:</td><td><input type="password" name="passwd"><span id="error_passwd"></span></td></tr>
<tr><td>Повтор нового пароля:</td><td><input type="password" name="passwd1"><span id="error_passwd1"></span></td></tr>
<!--<tr><td>Кто может смотреть мои файлы:</td><td><select name="type"><option value="0" <?if($user_info['user_type'] == 0){ echo "selected"; }?>>Я</option><option value="1" <?if($user_info['user_type'] == 1){ echo "selected"; }?>>Пользователи</option><option value="2" <?if($user_info['user_type'] == 2){ echo "selected"; }?>>Все</option></select></td></tr>-->
<tr><td>Старый пароль:</td><td><input type="password" name="oldpasswd"><span id="error_oldpw"></span></td></tr>
<tr><td colspan="2" style="text-align: center;"><input type="submit" value="Изменить"> <input type="reset" value="Отмена"></td></tr>
</form>
</table>
</center>
* - если оставить поле новый праоль пустым, старый пароль не изменится.



<script type="text/javascript">
$(document).ready(function() { 
    var options = { 
				beforeSubmit: validate,
        success: showResponse,
        url: '/ajax_profile',
        type: 'post',
				data: { edit: 'true' }
    }; 
 
    $('#changeme').submit(function() { 
        $(this).ajaxSubmit(options); 
        return false; 
    }); 

		$('#close_p').click(function(){
				tab_close('#profile');
		}); 
}); 

function validate(formData, jqForm, options){
				var form = jqForm[0]; 

        if (!form.name.value) { 
					alert('Вы не ввели Имя'); 
					form.name.focus();
          return false; 
        }

				if (form.name.value.length < 3){
					alert('Имя не должно быть меньше 3х символов');
					form.name.focus();
          return false; 
				}

				if (form.passwd.value && form.passwd.value.length < 5){
					alert('Пароль не должен быть меньше 5и символов');
					form.passwd.focus();
          return false; 
				}

				if (form.passwd.value != form.passwd1.value){
					alert('Пароли не совпадают');
					form.passwd1.focus();
          return false; 
				}

			  if (!form.oldpasswd.value) { 
					alert('Для изменения профиля нужно ввести старый пароль'); 
					form.oldpasswd.focus();
          return false; 
        }

}

function showResponse(responseText, statusText, xhr, $form)  { 
	if(responseText == "oldpasswd"){
		alert("Неверный старый пароль");
	}
	if(responseText == "npwd"){
		alert("Новые пароли не совпадают");
	}
	if(responseText == "base"){
		alert("Ошибка базы данных, попробуйте позже.");
	}
	if(responseText == "true"){
		alert("Информация успешно изменена.");
	}
}
</script>
