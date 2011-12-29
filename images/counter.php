<?php
#################################################################################################
#				         Kounter v1.0						#
#					 Made By: Kage						#
#				       http://vitund.com					#
#			Copyright © 2007 Kage (Alex), All Rights Reserved			#
#################################################################################################
# counter.php - Counter script									#
#################################################################################################
# See README.TXT for help and details.								#
#################################################################################################
###################################### LEAVE CREDIT HERE ########################################
#################################################################################################

 $counterfile = "/usr/local/www/hackthissite.org/count.txt";
 // This is the absolute path to the file that holds the count for your site.
 // If left as is, it should work anyway.


 $digits = "10";
 // If you want filler digits, put in how many digits you want for your counter.
 // If not, put in 0
 // DO NOT exceed 10 digits


 $filler = "0";
 // If you set something higher than 0 for $digits above, then put in what you
 // want to fill in the blanks on your counter.
 // Recommended filler is 0, but you can use anything from a dash to a dot.
 // Only enter ONE character (Ex. "0" NOT "000")


 $textcolor = array(
	"red" => "186",
	"green" => "186",
	"blue" => "186"
	);
 // RGB color of the counter text


 $overlaycolor = array(
	"red" => "51",
	"green" => "51",
	"blue" => "51"
	);
 // RGB color of the overlay text


 $bgcolor = array(
	"red" => "0",
	"green" => "0",
	"blue" => "0"
	);
 // RGB color of the counter background


 $bordercolor = array(
	"red" => "200",
	"green" => "200",
	"blue" => "200"
	);
 // RGB color of the counter border


#################################################################################################
#########################################     END      ##########################################
#########################################     USER     ##########################################
#########################################  SERVICABLE  ##########################################
#########################################    PARTS     ##########################################
################################################################################################
#												#
#  ANY editing of anything below this line voids your ability to recieve technical support on	#
#       this script.  Please do not tamper with anything below -- it works fine as it is.	#
#												#
#################################################################################################

 $error = "";
if ($fp = @fopen($counterfile, "r+")) {
  @flock($fp, 1);
  $count = (int)fgets($fp, 4096);
  $count++;
// Hehe, cheating...
$count += rand(0,7);
  @fseek($fp, 0);
  @fputs($fp, $count);
  @flock($fp, 3);
  @fclose($fp);
 } else {
  $error = "No Count File";
 }

 if (!$error) {
  $newcount = number_format($count);
  if (($digits && $digits > 0 && is_numeric($digits)) && $length < $digits) {
   if ($digits > 10) { $digits = 10; }
   $fillcount = $digits-$length;
   $filler = (strlen($filler) > 1 ? substr($filler, 0, 1) : $filler);
   $overlay = str_repeat($filler, $fillcount);
   $newcount = $overlay.$newcount;
  }
 }
 $newcount = " ".($error ? $error : $newcount)." ";

 header("Content-type: image/jpeg");
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
 header("Pragma: no-cache");
 header("Cache-Control: no-cache, must-revalidate");
 $img = ImageCreate(strlen($newcount)*7, 14);
  $color = ImageColorAllocate($img, $textcolor["red"], $textcolor["green"], $textcolor["blue"]);
  $darkcolor = ImageColorAllocate($img, $overlaycolor["red"], $overlaycolor["green"], $overlaycolor["blue"]);
  $errorcolor = ImageColorAllocate($img, 238, 238, 238);
  $bg = ImageColorAllocate($img, $bgcolor["red"], $bgcolor["green"], $bgcolor["blue"]);
  $rect = ImageColorAllocate($img, $bordercolor["red"], $bordercolor["green"], $bordercolor["blue"]);
  ImageFilledRectangle($img, 0, 0, strlen($newcount)*7, 13, $bg);
  ImageLine($img, 0, 0, strlen($newcount)*7, 0, $rect);
  ImageLine($img, 0, 13, strlen($newcount)*7, 13, $rect);
  ImageLine($img, 0, 0, 0, 13, $rect);
  ImageLine($img, strlen($newcount)*7-1, 0, strlen($newcount)*7-1, 13, $rect);
  if (!$error) {
   ImageString($img, 3, 0, 0, $newcount, $color);
   ImageString($img, 3, 0, 0, " ".$overlay, $darkcolor);
  } else {
   ImageString($img, 3, 0, 0, $newcount, $errorcolor);
  }
  ImageJPEG($img, "", 100);
 ImageDestroy($img);
?>
