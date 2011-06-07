#!/usr/bin/perl

  use DBI;
  use DBD::mysql;
  use URI::Escape;
  use CGI qw(:standard);
  use CGI::Carp 'fatalsToBrowser';
  use File::Copy;

# Настройки БД
  $dbuser = 'root';
  $dbpasswd = '';
  $dbname = 'mwmf';
  $dbhost = 'localhost';
  $dbport = '3306';
  $charset = 'utf8';
  $set_charset = 'yes';

# Подключение к БД
  $dbh = DBI->connect("dbi:mysql:$dbname:$dbhost:$dbport",$dbuser,$dbpasswd) or die "Unable to connect: $DBI::errstr\n";;

# Установка кодировки БД
  if($set_charset eq 'yes')
  {
     $query = $dbh->do("SET CHARACTER SET $charset");
  }

# Выборка из БД
  $query = $dbh->prepare("SELECT * FROM mfh_files LEFT JOIN mfh_files_media_info ON id=file_id WHERE file_status = '0' ORDER BY file_time ASC");
  $query->execute() or die("Error query");
 
  while($row = $query->fetchrow_hashref())
  { 
     my $file_id = $row->{file_id};
     my $file_time = $row->{file_time};
     my $file_ext = $row->{file_ext};
     my $file_type = $row->{file_type};
     @file_info = split(/,/,$row->{value});

		 @x1=split(/=>/,@file_info[0]);
		 $x1=@x1[1];
		 $x1=~ s/"//g;
		 $x1=~ s/ //g;

		 @x2=split(/=>/,@file_info[1]);
		 $x2=@x2[1];
		 $x2=~ s/"//g;
		 $x2=~ s/ //g;

		 @x3=split(/=>/,@file_info[2]);
		 $x3=@x3[1];
		 $x3=~ s/"//g;
		 $x3=~ s/ //g;

		 @x4=split(/=>/,@file_info[3]);
		 $x4=@x4[1];
		 $x4=~ s/"//g;
		 $x4=~ s/ //g;

		 @x5=split(/=>/,@file_info[4]);
		 $x5=@x5[1];
		 $x5=~ s/"//g;
		 $x5=~ s/ //g;
		 $x5=~ s/ //g;

		 @x6=split(/=>/,@file_info[5]);
		 $x6=@x6[1];
		 $x6=~ s/"//g;
		 $x6=~ s/ //g;
		 $x6=~ s/ //g;

			($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($file_time);
			$year += 1900;
			$mon += 1;
			if($mon < 10){
			    $mon='0'.$mon;
			}
			if($mday < 10){
			    $mday='0'.$mday;
			}
			$filename="./".$year."/".$mon."/".$mday."/".$file_time."/orig".$file_ext;
			$upload_final="./".$year."/".$mon."/".$mday."/".$file_time."/";

		if($file_type eq "video"){
			if($x1 > 320){
                            $t_x=320;
                            $t_y=240;
                            $t_r=($t_x / $t_y);

                            if ($t_r > $x3) {
                                    $n_y = $t_y;
                                    $n_x = $x3 * $t_y;
                            } else {
                                    $n_y = $t_x / $x3;
                                    $n_x = $t_x;
                            }

                            $n_x = sprintf("%.0f", $n_x);
                            $n_y = sprintf("%.0f", $n_y);

                            $update = $dbh->do("UPDATE mfh_files SET file_status='1' WHERE file_time='".$file_time."'");

                            $ffmpeg="ffmpeg -y -i ".$filename." -vcodec h264 -threads 8 -r 24 -g 48 -b 320k -bt 320k -s ".$n_x."x".$n_y." -acodec libfaac -aq 100 ".$upload_final."small.mp4";
                            print($file_time." video x > 320, FFmpeg Exec");
                            system($ffmpeg);
                            $update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='1' WHERE file_time='".$file_time."'");
                            print("x > 320, file ext is ".$file_ext." New file x:$n_x y:$n_y\n");
			}else{
                            print($file_time." Video <= 320 not mp4, ffmpeg exec);
                            $ffmpeg="ffmpeg -y -i ".$filename." -vcodec h264 -threads 8 -r 24 -g 48 -b 320k -bt 500k -acodec libfaac -aq 100 ".$upload_final."small.mp4";
                            system($ffmpeg);
                            $update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='3' WHERE file_time='".$file_time."'");
                            print("x <= 320, file ext is ".$file_ext." New file mp4\n");
			}
		}
		elsif ($file_type eq "audio"){
			$s_rate=($x2 > 22050)? 22050 : $x2;
			$s_brate=($x3 < 65000)? 64 : ($x3/1000);
			$s_ch=($x1 < 2)? 1 : $x1;
			if ($x3 < 65000){
			  if ($file_ext eq ".mp3"){
					print($file_time." Audio File bt<65 mp3, copy\n");
					$update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='2' WHERE file_time='".$file_time."'");
					#$delete = $dbh->do("DELETE FROM mfh_files_media_info WHERE id-'".$file_id."'");
			  }else{
					print($file_time." Audio bt<65, ffmpeg exec\n");
					$update = $dbh->do("UPDATE mfh_files SET file_status='1' WHERE file_time='".$file_time."'");
					$ffmpeg="ffmpeg -i ".$filename." -ar ".$s_rate." -acodec libmp3lame -ab ".$s_brate."k -ac ".$s_ch." ".$upload_final."orig.mp3";
					system($ffmpeg);
					$update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='2' WHERE file_time='".$file_time."'");
					#$delete = $dbh->do("DELETE FROM mfh_files_media_info WHERE id-'".$file_id."'");
					unlink($filename);
				}
			}else{
			  if ($file_ext eq ".mp3"){
					print($file_time." Audio mp3 copy\n");
					$update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='2' WHERE file_time='".$file_time."'");
					#$delete = $dbh->do("DELETE FROM mfh_files_media_info WHERE id-'".$file_id."'");
				}else{
					$update = $dbh->do("UPDATE mfh_files SET file_status='1' WHERE file_time='".$file_time."'");
					print($file_time." Audio non mp3, ffmpeg exec\n");
					$ffmpeg_orig="ffmpeg -i ".$filename." -ar ".$s_rate." -acodec libmp3lame -ab ".$s_brate."k -ac ".$s_ch." ".$upload_final."orig.mp3";
					system($ffmpeg_orig);
					$update = $dbh->do("UPDATE mfh_files SET file_status='2', file_media_status='2' WHERE file_time='".$file_time."'");
					#$delete = $dbh->do("DELETE FROM mfh_files_media_info WHERE id-'".$file_id."'");
					unlink($filename);
				}
			}
##			print("Audio INFO ch:$x1 rate:$x2 bitrate:$x3 \n")
		}



  }

  $query->finish();
  $dbh->disconnect();