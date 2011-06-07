<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}
$email=$_POST['email'];

if(!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$", $email)) {
     echo "<p>Не верный E-mail</p>\n";
		 exit(0);
}
$file_id=$_POST['file_id'];

$to      = $email;
$subject = 'Ссылка закаченного файла';
$message = 'Ссылка на информацию о вашем файле http://'.$_SERVER['SERVER_NAME'].'/view_'.$file_id.'

Данное письмо сгенерированно автоматический отвечать на него не надо.
С Уважением Администрация проекта http://'.$_SERVER['SERVER_NAME'];
$headers = 'From: no_reply@myhost' . "\r\n" .
    'Reply-To: no_reply@myhost' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

echo "Письмо успешно отправленно";
?>
