<?
/*
		Media hosting project
		Version 1.0-beta
		config.php
		29.11.2010
		Scripted by Poluboyarinov Mikhail
		mikle.sol@gmail.com
*/
error_reporting(7);
$config['db_host']="localhost";
$config['db_user']="root";
$config['db_pass']="";
$config['db_base']="mwmf";
$config['db_pref']="mfh_";
$config['log_lvl']=0;
$config['srv_id']='1';
$config['base_dir']="/home/httpd/s1.mwf.mtkcom.ru/www/";
$config['file_ext']=array(".gif" => "img" ,".jpg" => "img",".jpeg" => "img",".png" => "img",".bmp" => "img",".tif" => "img",".tiff" => "img",".mp3" => "audio",".wav" => "audio",".ogg" => "audio",".wma" => "audio",".avi" => "video",".mpg" => "video",".wmv" => "video",".mov" => "video",".flv" => "video");

define('db_pr',$config['db_pref']);

include($config['base_dir']."include/class_mysql.php");
include($config['base_dir']."include/class_users.php");
include($config['base_dir']."include/function.php");

$user=new user();

$db=new sql_db($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_base']);
?>