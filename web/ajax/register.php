<?
include("../include/config.php");
if($_GET['act'] == "active"){

$code=$_GET['code'];

$sql=$db->sql_query("SELECT user_id FROM ".db_pr."users WHERE user_key='$code'");

if($db->sql_numrows($sql) == 0){
	echo '<META HTTP-EQUIV="Refresh" CONTENT="3; URL=/">Данный код не найден. Попробуйте позже или обратитесь в службу поддержки.';
}else{
	$rw=$db->sql_fetchrow($sql);
	$db->sql_query("UPDATE ".db_pr."users SET user_key='' WHERE user_id='".$rw['user_id']."'");
	echo '<META HTTP-EQUIV="Refresh" CONTENT="3; URL=/"> Аккаунт успешно активирован можете войти в систему и приступать к работе.';
}
exit();
}
if($_POST['reg'] == true){
extract($_POST);

$esql=$db->sql_query("SELECT user_id FROM ".db_pr."users WHERE user_email='$email'");
if($db->sql_numrows($esql) > 0){
echo "email";
exit();
}
$mypasswd=md5($passwd);
$key=md5($passwd.$name.$email.time());
/*
$new_name=iconv("UTF-8", "cp1251", $name);
$new_query=iconv("UTF-8", "cp1251", $query);
$new_answer=iconv("UTF-8", "cp1251", $answer);
*/
$sql="INSERT INTO ".db_pr."users VALUES (NULL,'$email','".htmlspecialchars($name)."','".htmlspecialchars($mypasswd)."','1','1','0','$key','".htmlspecialchars($query)."','".htmlspecialchars($answer)."')";
if(!$db->sql_query($sql)){
echo "base";
exit();
}

$to      = $email;
$subject = 'Активация аккаунта';
$message = 'Вы заполнили анкету для регистрации на сайте http://'.$_SERVER['SERVER_NAME'].'/
Для окончания регистрации вам нужно перейти по ссылке http://'.$_SERVER['SERVER_NAME'].'/ajax_register?act=active&code='.$key.'

Данное письмо сгенерированно автоматический отвечать на него не надо.
С Уважением Администрация проекта http://'.$_SERVER['SERVER_NAME'];
$headers = 'From: no_reply@'.$_SERVER['SERVER_NAME'].' ' . "\r\n" .
    'Reply-To: no_reply@'.$_SERVER['SERVER_NAME'].' ' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
echo "true";
exit();
}
?>
<style>
form { margin: 0; padding: 0;}
td { text-align: left; }
</style>
<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="close_r" href="#index" style="font-size: 10px;">Закрыть</a></div>
<center><b>Регистрация</b>
<table>
<form id="regme">
<tr><td>Имя:</td><td><input type="text" name="name"><span id="error_name"></span></td></tr>
<tr><td>E-mail:</td><td><input type="text" name="email"><span id="error_email"></span></td></tr>
<tr><td>Пароль:</td><td><input type="password" name="passwd"><span id="error_passwd"></span></td></tr>
<tr><td>Повтор пароля:</td><td><input type="password" name="passwd1"><span id="error_passwd1"></span></td></tr>
<!--<tr><td>Кто может смотреть мои файлы:</td><td><select name="type"><option value="0">Я</option><option value="1">Пользователи</option><option value="2" selected>Все</option></select></td></tr>-->
<tr><td>Секретный вопрос:</td><td><input type="text" name="query"><span id="error_query"></span></td></tr>
<tr><td>Секретный ответ:</td><td><input type="text" name="answer"><span id="error_answer"></span></td></tr>
<tr><td colspan="2"  style="text-align: center;"><a href="javascript:;" onclick='$("#upload_box").tabs("select","#ui-tabs-2");'>Я согласен с правилами проекта</a> <input type="checkbox" name="rules" value="1"><span id="error_rules"></span></td></tr>
<tr><td colspan="2" style="text-align: center;"><input type="submit" value="Зарегестрироваться"> <input type="reset" value="Отмена"></td></tr>
</form>
</table>
</center>

<script type="text/javascript">
$(document).ready(function() { 
    var options = { 
				beforeSubmit: validate,
        success:       showResponse,
        url:       '/ajax_register',
        type:      'post',
				data: { reg: 'true' }
        //clearForm: true
        //resetForm: true
        //timeout:   3000 
    }; 
 
    $('#regme').submit(function() { 
        $(this).ajaxSubmit(options); 
        return false; 
    }); 

		$('#close_r').click(function(){
				tab_close('#register');
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

        if (!form.email.value) { 
            alert('Вы не ввели e-mail'); 
						form.email.focus();
            return false; 
        } 

				if (echeck(form.email.value)==false){
					form.email.focus();
					return false
				}

        if (!form.passwd.value) { 
					alert('Вы не ввели пароль'); 
					form.passwd.focus();
          return false; 
        }

				if (form.passwd.value.length < 5){
					alert('Пароль не должен быть меньше 5и символов');
					form.passwd.focus();
          return false; 
				}

				if (form.passwd.value != form.passwd1.value){
					alert('Пароли не совпадают');
					form.passwd1.focus();
          return false; 
				}

        if (!form.query.value) { 
					alert('Вы не ввели секретный вопрос'); 
					form.query.focus();
          return false; 
        }

				if (form.query.value.length < 3){
					alert('Секретный вопрос не должен быть меньше 3х символов');
					form.query.focus();
          return false; 
				}

        if (!form.answer.value) { 
					alert('Вы не ввели секретный ответ'); 
					form.answer.focus();
          return false; 
        }

				if (form.answer.value.length < 5){
					alert('Секретный ответ не должен быть меньше 5и символов');
					form.answer.focus();
          return false; 
				}

				if(form.rules.checked == false){
					alert('Вы обязаны согласится с правилами проекта.');
					form.rules.focus();
          return false; 
				}

}

function error_add(name, text){
$('#error_'+name).fadeIn(500);
$('#error_'+name).html('<img src="/images/loading.gif"> '+text);
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
	if(responseText == "email"){
		alert("Данный E-mail уже зарегестрирован выберите другой или перейдите к процедуре восстановления пароля от аккаунта.");
	}
	if(responseText == "base"){
		alert("Ошибка базы данных, попробуйте позже.");
	}
	if(responseText == "true"){
		alert("Пользователь успешно зарегестрирован, информация по активации пользователя отправлена на e-mail.");
		$("#upload_box").tabs("select", 0);
		$("#upload_box").tabs("remove" , "#register");
		$add=0;		
	}
} 
</script>
