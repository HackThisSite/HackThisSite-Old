<?php
$handle = 'basic6';
if (!empty($_POST['password']) && $_POST['password'] == substr(Mission::generatePassword($handle), 0, 8)) {
    echo Partial::render('missionDone', array(
		'id' => $id, 
		'handle' => $handle, 
		'current' => 'basic 6', 
		'next' => 'basic/7'));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');

function encryptPassword($text) {
	$ret = "";
	for ($i=0; $i<strlen($text); $i++) {
		$ch = substr($text, $i, 1);
		$er = ord($ch);
		$ret = $ret . chr($er + $i);
	}
	return $ret;
}

?>
<center><b>Level 6</b></center><br /><br />
Network Security Sam has encrypted his password. The encryption system is 
publically available and can be accessed with this form:<br /><br />

<center>
	Please enter a string to have it encrypted.
	<form action="<?php echo Url::format('/missions/basic/6/encrypt.php'); ?>" method="post">
		<input type="text" name="text" /><br />
		<input type="submit" value="encrypt" />
	</form>

	You have recovered his encrypted password. It is:<br /><br />
	<b><?php echo encryptPassword(substr(Mission::generatePassword($handle), 0, 8)); ?></b><br /><br />
	
	Decrypt the password and enter it below to advance to the next level.<br /><br />
	
	<b>Password:</b><br />
	<?php if (!empty($_POST['password']) && $_POST['password'] != substr(Mission::generatePassword($handle), 0, 8)): ?>
	<center><b><u>Invalid password.</u></b></center>
	<?php endif; ?>
	<form action="<?php echo Url::format('/missions/basic/6/index.php'); ?>" method="post">
		<input type="password" name="password" /><br /><br />
		<input type="submit" value="submit" />
	</form>
</center>
