<?
/*
		Media hosting project
		Version 1.0-beta
		function.php
		29.11.2010
		Scripted by Poluboyarinov Mikhail
		mikle.sol@gmail.com
*/

function create_thumb($full_path,$info,$file_id, $video = '')
{
		global $db;

		switch ($info['ext_o'])
		{
		  case ".jpg":
		  case ".jpeg":
		  	$img = imagecreatefromjpeg($full_path);
		    break;
		  case ".gif":
		  	$img = imagecreatefromgif($full_path);
		    break;
		  case ".png":
		  	$img = imagecreatefrompng($full_path);
		    break;
		  case ".bmp":
		  	$img = imagecreatefrombmp($full_path);
		    break;
		  case ".tif":
		  case ".tiff":
		  	$img = imagecreatefromjpeg($full_path);
		    break;
		}

			if (!$img) {
				echo "ERROR:could not create image handle";
				if(is_file($video) == true){
					@unlink($video);
				}
				@unlink($full_path);
				$db->sql_query("DELETE FROM ".db_pr."files WHERE file_time='$file_id'");
				exit(0);
			}
			$width = imageSX($img);
			$height = imageSY($img);
		
			if (!$width || !$height) {
				echo "ERROR:Invalid width or height";
				if(is_file($video) == true){
					@unlink($video);
				}
				@unlink($full_path);
				$db->sql_query("DELETE FROM ".db_pr."files WHERE file_time='$file_id'");
				exit(0);
			}

			// Build the thumbnail
			$target_width = 300;
			$target_height = 300;
			$target_ratio = ($target_width / $target_height);
		
			$img_ratio = ($width / $height);
		
			if ($target_ratio > $img_ratio) {
				$new_height = $target_height;
				$new_width = $img_ratio * $target_height;
			} else {
				$new_height = $target_width / $img_ratio;
				$new_width = $target_width;
			}
		
			if ($new_height > $target_height) {
				$new_height = $target_height;
			}
			if ($new_width > $target_width) {
				$new_height = $target_width;
			}
		
			$new_img = ImageCreateTrueColor($new_width, $new_height);
			if (!@imagefilledrectangle($new_img, 0, 0, $target_width-1, $target_height-1, 0)) {	// Fill the image black
				echo "ERROR:Could not fill new image";
				if(is_file($video) == true){
					@unlink($video);
				}
				@unlink($full_path);
				$db->sql_query("DELETE FROM ".db_pr."files WHERE file_time='$file_id'");
				exit(0);
			}
		
			if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
				echo "ERROR:Could not resize image";
				if(is_file($video) == true){
					@unlink($video);
				}
				@unlink($full_path);
				$db->sql_query("DELETE FROM ".db_pr."files WHERE file_time='$file_id'");
				exit(0);
			}
	
			ob_start();
			imagejpeg($new_img);
			$imagevariable = ob_get_contents();
			ob_end_clean();

		return $imagevariable;  
}


function ConvertBMP2GD($src, $dest = false) {
	if(!($src_f = fopen($src, "rb"))) {
		return false;
	}
	if(!($dest_f = fopen($dest, "wb"))) {
		return false;
	}
	$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,14));
	$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",
	fread($src_f, 40));
	
	extract($info);
	extract($header);
	
	if($type != 0x4D42) { // signature "BM"
		return false;
	}

	$palette_size = $offset - 54;
	$ncolor = $palette_size / 4;
	$gd_header = "";
	// true-color vs. palette
	$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
	$gd_header .= pack("n2", $width, $height);
	$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
	if($palette_size) {
		$gd_header .= pack("n", $ncolor);
	}
	// no transparency
	$gd_header .= "\xFF\xFF\xFF\xFF";
	
	fwrite($dest_f, $gd_header);
	
	if($palette_size) {
		$palette = fread($src_f, $palette_size);
		$gd_palette = "";
		$j = 0;
		while($j < $palette_size) {
			$b = $palette{$j++};
			$g = $palette{$j++};
			$r = $palette{$j++};
			$a = $palette{$j++};
			$gd_palette .= "$r$g$b$a";
		}
		$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
		fwrite($dest_f, $gd_palette);
	}
	
	$scan_line_size = (($bits * $width) + 7) >> 3;
	$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size &
	0x03) : 0;
	
	for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
		// BMP stores scan lines starting from bottom
		fseek($src_f, $offset + (($scan_line_size + $scan_line_align) *
		$l));
		$scan_line = fread($src_f, $scan_line_size);
		if($bits == 24) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$b = $scan_line{$j++};
				$g = $scan_line{$j++};
				$r = $scan_line{$j++};
				$gd_scan_line .= "\x00$r$g$b";
			}
		}
		else if($bits == 8) {
			$gd_scan_line = $scan_line;
		}
		else if($bits == 4) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$byte = ord($scan_line{$j++});
				$p1 = chr($byte >> 4);
				$p2 = chr($byte & 0x0F);
				$gd_scan_line .= "$p1$p2";
			} $gd_scan_line = substr($gd_scan_line, 0, $width);
		}
		else if($bits == 1) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$byte = ord($scan_line{$j++});
				$p1 = chr((int) (($byte & 0x80) != 0));
				$p2 = chr((int) (($byte & 0x40) != 0));
				$p3 = chr((int) (($byte & 0x20) != 0));
				$p4 = chr((int) (($byte & 0x10) != 0));
				$p5 = chr((int) (($byte & 0x08) != 0));
				$p6 = chr((int) (($byte & 0x04) != 0));
				$p7 = chr((int) (($byte & 0x02) != 0));
				$p8 = chr((int) (($byte & 0x01) != 0));
				$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
			} $gd_scan_line = substr($gd_scan_line, 0, $width);
		}
		
		fwrite($dest_f, $gd_scan_line);
	}
	fclose($src_f);
	fclose($dest_f);
	return true;
}

function imagecreatefrombmp($filename) {
	$tmp_name = tempnam("/tmp", "GD");
	if(ConvertBMP2GD($filename, $tmp_name)) {
		$img = imagecreatefromgd($tmp_name);
		unlink($tmp_name);
		return $img;
	} return false;
}


function log_add($text,$log){
	global $config;
	$fp=fopen($config['base_dir']."logs/".date("d-m-Y").".".$log.".log","a+");
	fwrite($fp,date("d-m-Y H:i:s")." - ".$text."\r\n");
	fclose($fp);
}
?>