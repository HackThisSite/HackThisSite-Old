<?php
$handle = 'basic3';
if (!empty($_POST['password']) && $_POST['password'] == Mission::generatePassword($handle)) {
    echo Partial::render('missionDone', array(
		'id' => $id, 
		'handle' => $handle, 
		'current' => 'basic 3', 
		'next' => 'basic/4'));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');

if ($uri == 'password.php') {
	echo Mission::generatePassword($handle);
	return;
}
?>
<center><b>Level 3</b></center><br />
This time Network Security Sam remembered to upload the password file, 
but there were deeper problems than that.<br /><br />

<center>
	<b>Password:</b><br />
	<?php if (!empty($_POST['password']) && $_POST['password'] != Mission::generatePassword($handle)): ?>
	<center><b><u>Invalid password.</u></b></center>
	<?php endif; ?>
	<form action="<?php echo Url::format('/missions/basic/3/index.php'); ?>" method="post">
		<input type="hidden" name="file" value="password.php" />
		<input type="password" name="password" /><br /><br />
		<input type="submit" value="submit" />
	</form>
</center>
