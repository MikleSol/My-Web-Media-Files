-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 07 2011 г., 17:40
-- Версия сервера: 5.1.56
-- Версия PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `mwmf`
--

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Структура таблицы `mfh_files`
--

CREATE TABLE IF NOT EXISTS `mfh_files` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file_uid` int(11) NOT NULL,
  `file_time` varchar(255) NOT NULL,
  `file_server` int(11) NOT NULL,
  `file_ext` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_used` varchar(255) NOT NULL,
  `file_status` int(11) NOT NULL,
  `file_media_status` int(11) NOT NULL,
  UNIQUE KEY `file_id` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mfh_files`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mfh_files_media_info`
--

CREATE TABLE IF NOT EXISTS `mfh_files_media_info` (
  `id` bigint(20) DEFAULT NULL,
  `value` text NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

--
-- Дамп данных таблицы `mfh_files_media_info`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mfh_files_meta`
--

CREATE TABLE IF NOT EXISTS `mfh_files_meta` (
  `file_meta_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `privacy` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `mfh_files_meta`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mfh_files_servers`
--

CREATE TABLE IF NOT EXISTS `mfh_files_servers` (
  `srv_id` int(11) NOT NULL AUTO_INCREMENT,
  `srv_type` enum('1','2') DEFAULT NULL,
  `srv_url` varchar(255) NOT NULL,
  `srv_script` varchar(255) DEFAULT NULL,
  `srv_dir` varchar(255) NOT NULL,
  `srv_quote` int(11) NOT NULL,
  `srv_used` int(11) NOT NULL,
  `srv_status` enum('0','1','2') NOT NULL,
  UNIQUE KEY `srv_id` (`srv_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `mfh_files_servers`
--

INSERT INTO `mfh_files_servers` (`srv_id`, `srv_type`, `srv_url`, `srv_script`, `srv_dir`, `srv_quote`, `srv_used`, `srv_status`) VALUES
(1, '2', 'http://s1.myds.pp.ru/', 'upload.php', '/home/httpd/s1.mwf.mtkcom.ru/www/', 2147483647, 1039, '1');

-- --------------------------------------------------------

--
-- Структура таблицы `mfh_users`
--

CREATE TABLE IF NOT EXISTS `mfh_users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `user_name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `user_passwd` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `user_type` enum('0','1','2') CHARACTER SET cp1251 DEFAULT NULL,
  `user_group` int(11) DEFAULT NULL,
  `user_money` int(11) NOT NULL,
  `user_key` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `user_query` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `user_answer` varchar(255) CHARACTER SET cp1251 NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Структура таблицы `mfh_users_dirs`
--

CREATE TABLE IF NOT EXISTS `mfh_users_dirs` (
  `dir_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dir_name` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `mfh_users_dirs`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mfh_users_dirsfiles`
--

CREATE TABLE IF NOT EXISTS `mfh_users_dirsfiles` (
  `user_file_id` int(11) NOT NULL,
  `user_dir_id` int(11) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `mfh_users_dirsfiles`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mfh_users_group`
--

CREATE TABLE IF NOT EXISTS `mfh_users_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `group_settings` text CHARACTER SET cp1251 NOT NULL,
  UNIQUE KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `mfh_users_group`
--

INSERT INTO `mfh_users_group` (`group_id`, `group_name`, `group_settings`) VALUES
(0, 'Гость', 'upload_img=2,upload_audio=50,upload_video=500,dwn_speed=60,capcha=1,save_file=1,upload_files=1'),
(1, 'Пользователь', 'upload_img=10,upload_audio=300,upload_video=2048,dwn_speed=0,capcha=0,save_file=0,upload_files=10');

-- --------------------------------------------------------

--
-- Структура таблицы `mfh_users_session`
--

CREATE TABLE IF NOT EXISTS `mfh_users_session` (
  `session_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `session_uid` bigint(20) NOT NULL,
  `session_key` varchar(255) NOT NULL,
  `session_time` varchar(255) NOT NULL,
  `session_type` enum('0','1') DEFAULT '0',
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mfh_users_session`
--

