<?php
function encryptPassword($text) {
	$ret = "";
	for ($i=0; $i<strlen($text); $i++) {
		$ch = substr($text, $i, 1);
		$er = ord($ch);
		$ret = $ret . chr($er + $i);
	}
	return $ret;
}

if (isset($_POST['text']) && strlen($_POST['text']) < 15) {
	$encrypted = htmlentities(encryptPassword($_POST['text']));
	echo "<center>Your encrypted string is: '$encrypted'</center>";
} else {
?>
<center>Please enter a string below to have it encrypted.<br />
<form action="/missions/basic/6/encrypt.php" method="post">
<input type="text" name="text"><br />
<input type="submit" value="encrypt">
</form></center>
<?php
}
