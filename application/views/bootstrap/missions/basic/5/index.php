<?php
$handle = 'basic5';
if (!empty($_POST['password']) && $_POST['password'] == Mission::generatePassword($handle)) {
    echo Partial::render('missionDone', array(
		'id' => $id, 
		'handle' => $handle, 
		'current' => 'basic 5', 
		'next' => 'basic/6'));
    return;
}

if ($uri == 'level5.php') {
	try {
		if (empty($_POST['to']) || $_POST['to'] == 'webmaster@hulla-balloo.com')
			throw new Exception();
		if (empty($_SERVER['HTTP_REFERER']))
			throw new Exception();
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (empty($url['host']) || !strpos(Config::get('other:baseUrl'), $url['host']))
			throw new Exception();
		
		echo 'password: ' . Mission::generatePassword($handle);
	} catch (Exception $e) {
		echo 'Password reminder successfully sent.';
	}
	return;
}

if (Mission::hasDone($id))
    echo Partial::render('missionOld');
?>
<center><b>Level 5</b></center><br /><br />
Sam has gotten wise to all the people who wrote their own forms to get 
the password. Rather than actually learn the password, he decided to make 
his email program a little more secure.<br /><br />

<center>
	<form action="<?php echo Url::format('/missions/basic/5/level5.php'); ?>" method="post">
		<input type="hidden" name="to" value="webmaster@hulla-balloo.com" />
		<input type="submit" value="Send password to Sam" />
	</form>
</center><br /><br />

<center>
	<b>Password:</b><br />
	<?php if (!empty($_POST['password']) && $_POST['password'] != Mission::generatePassword($handle)): ?>
	<center><b><u>Invalid password.</u></b></center>
	<?php endif; ?>
	<form action="<?php echo Url::format('/missions/basic/5/index.php'); ?>" method="post">
		<input type="password" name="password" /><br /><br />
		<input type="submit" value="submit" />
	</form>
</center>
