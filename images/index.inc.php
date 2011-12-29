<?php
if (checkAccess('moderator')){
?>
<CENTER>
<?php
// Configuration Settings 

$thumbnail['width']=(isset($_GET['width']) && ctype_digit($_GET['width'])) ? $_GET['width'] : 540; //Width of image in pixels 
$thumbnail['size']=(isset($_GET['size']) && ctype_digit($_GET['size'])) ? $_GET['size'] : 70; //Width of thumbnails in pixels 
$thumbnail['max_h'] = (isset($_GET['max_h']) && ctype_digit($_GET['max_h'])) ? $_GET['max_h'] : 360;  	//Max height of the thumbnails window in pixels 
$thumbnail['cols']= (isset($_GET['cols']) && ctype_digit($_GET['cols'])) ? $_GET['cols'] : 6; 


//Get the path to the current directory 

//Define allowed file extensions 
$allowed = array('jpg','gif','png'); 

if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) {
        if (!is_dir($file))
        {
	       $file_size = filesize($path . $file); 
	        $file_extension = file_ext($file); 
	        if (in_array($file_extension, $allowed))
	        {
	 	        $images[] = array('name' => $file, 
		                          'size' => $file_size); 
		    }
        }
    }
    closedir($handle);
}
?>


<script type="text/javascript"> 
//The array to hold all the images 
var photos = new Array(); 
//Preload images function 
function loadimages(){ 

<?php 
$count = count($images); 
for($i=1;$i<=$count;$i++){ 
    echo 'photos[' . $i . ']= new Image()' . "\n"; 
    echo 'photos[' . $i . '].src="images/' . $images[$i-1]['name'] . '"' . "\n"; 
} 
?> 
} 
//Javascript to display the images 

function showit(step){ 
    document.images.view.src=photos[step].src 
} 

window.onload = loadimages(); 
</script> 
<?php 

//Check for images present in the directory 
if ($count < 1){ 
    die('<center>No images present in current directory</center>'); 
} 
//Create the image navigation table 
$image_nav = '<div style="overflow:auto;max-height:' . $thumbnail['max_h'] . 'px;"><table class="articleC" align="center"><tr>'; 
for ($x=1; $x<=$count; $x++){ 
    if ($x <= $count){ 
         $url = $images[$x -1]['name']; 
         $image_nav .= '<td align="center" valign="center">'; 
         if ($x%$thumbnail['cols']==0){ 
            $image_nav .= '<a onclick="showit(' . $x . ')"><img class="navpic" src="images/' . $url . '" alt="' . $x . '" border="0" width="' . $thumbnail['size'] . 'px" /></a></td></tr><tr>'    ; 
        }else{ 
            $image_nav .= '<a onclick="showit(' . $x . ')"><img class="navpic" src="images/' . $url . '" alt="' . $x . '" border="0" width="' . $thumbnail['size'] . 'px" /></a></td>'; 
        }     
    } 
} 
$image_nav .= '</tr></table></div>'; 


//The image to be shown 
$image = '<img id="view" src="images/' . $images[0]['name'] . '" width="' . $thumbnail['width'] . '">'; 
//This script is free to use and modify to your needs, I just ask that you do not remove this copyright notice 
$faith = 'Choose an image above'; 

//Display all elements 
?> 
<table id="images" border="0" cellpadding="2px" class="articleC"> 
    <tr><td align="left" valign="top"> <?php echo $image_nav; ?></tr>
    <tr id="pic" align="left" valign="top" style="padding:2px;"><?php echo  $image; ?></tr> 
    <tr><td colspan="2" id="copy" align="center"><?php echo $faith; ?></td></tr> 
</table>
</CENTER> 
<form method="get">
<?php
	$arr = array('width' => 'Width: ', 'size' => 'Size: ', 'max_h' =>'Max height:', 'cols' => 'Column: ');
	$out ='';
	foreach($arr as $key=>$value)
	{
		$out .="<label for=\"$key\">$value</label>\n<input name=\"$key\" id=\"$key\" type=\"text\" size=\"3\" value=\"{$thumbnail[$key]}\"/>";
	}
	$out .=' <input type="submit" value="refresh" />';
	echo $out;
?>

</form>
<?php
} else die("moooooooooooooooooo");
?>
