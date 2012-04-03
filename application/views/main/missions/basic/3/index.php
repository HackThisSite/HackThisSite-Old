<?php
if (!empty($_POST['password']) && $_POST['password'] == Mission::generatePassword('basic3')) {
    echo Partial::render('missionDone', array('id' => $id, 'current' => 'basic 3', 'next' => 'basic/4'));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');

if ($uri == 'password.php') {
	echo Mission::generatePassword('basic3');
	return;
}
?>
<center><b>Level 3</b></center><br />
This time Network Security Sam remembered to upload the password file, 
but there were deeper problems than that.<br /><br />

<center>
	<b>Password:</b><br />
	
	<form action="<?php echo Url::format('/missions/basic/3/index.php'); ?>" method="post">
		<input type="hidden" name="file" value="password.php" />
		<input type="password" name="password" /><br /><br />
		<input type="submit" value="submit" />
	</form>
</center>
