<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest"){
	echo "<script>location.href='/'</script>";
	exit();
}
?>
<div style="right: 30px; display: block; margin:0; padding:0; position:absolute;"><a id="close_pay" href="#index" style="font-size: 10px;">Закрыть</a></div>
asdasdasddsa dsa dasd af sad

<script type="text/javascript">
$(document).ready(function() { 
		$('#close_pay').click(function(){
				tab_close('#pay');
		}); 
}); 
</script>
