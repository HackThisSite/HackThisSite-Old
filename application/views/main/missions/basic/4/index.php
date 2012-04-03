<?php
if (!empty($_POST['password']) && $_POST['password'] == Mission::generatePassword('basic4')) {
    echo Partial::render('missionDone', array('id' => $id, 'current' => 'basic 4', 'next' => 'basic/5'));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');

if ($uri == 'level4.php') {
	if ($_POST['to'] != 'webmaster@hulla-balloo.com') {
		echo 'password: ' . Mission::generatePassword('basic4');
	} else {
		echo 'Password reminder successfully sent.';
	}
	return;
}
?>
<center><b>Level 4</b></center><br /><br />
This time Sam hardcoded the password into the script. However, the password 
is long and complex, and Sam is often forgetful. So he wrote a script that 
would email his password to him automatically in case he forgot. Here is the 
script:<br /><br />

<center>
	<form action="<?php echo Url::format('/missions/basic/4/level4.php'); ?>" method="post">
		<input type="hidden" name="to" value="webmaster@hulla-balloo.com" />
		<input type="submit" value="Send password to Sam" />
	</form>
</center>
<br /><br />

<center><b>Password:</b><br />
	<form action="<?php echo Url::format('/missions/basic/4/index.php'); ?>" method="post">
		<input type="password" name="password" /><br /><br />
		<input type="submit" value="submit" />
	</form>
</center>
