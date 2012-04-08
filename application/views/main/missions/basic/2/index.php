<?php
$handle = 'basic2';
if (!empty($_POST['password']) && $_POST['password'] == '') {
    echo Partial::render('missionDone', array(
		'id' => $id, 
		'handle' => $handle, 
		'current' => 'basic 2', 
		'next' => 'basic/3'));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');
?>
<center><b>Level 2</b></center><br />
Network Security Sam set up a password protection script. He made it load 
the real password from an unencrypted text file and compare it to the 
password the user enters. However, he neglected to upload the password 
file...<br /><br />

<center><b>Password:</b /><br />
	<?php if (!empty($_POST['password']) && $_POST['password'] != ''): ?>
	<center><b><u>Invalid password.</u></b></center>
	<?php endif; ?>
	<form action="<?php echo Url::format('/missions/basic/2/index.php'); ?>" method="post">
		  <input type="password" name="password" /><br /><br />
		  <input type="submit" value="submit" />
	</form>
</center>
