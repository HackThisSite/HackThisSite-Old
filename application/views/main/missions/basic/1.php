<?php
if (!empty($_POST['password']) && $_POST['password'] == Mission::generatePassword('basic1')) {
    echo Partial::render('missionDone', array('id' => $id, 'current' => 'basic 1', 'next' => null));
    return;
}
if (Mission::hasDone($id))
    echo Partial::render('missionOld');
?>
	<br />
<br /><center>
<br />
		<b>Level 1(the idiot test)</b>
		</center><br /><br />
		This level is what we call "The Idiot Test", if you can't complete it, don't give up on learning all you can, but, don't go begging to someone else for the answer, thats one way to get you hated/made fun of. Enter the password and you can continue. <br /><br />
		<!-- the first few levels are extremely easy: password is <?php echo Mission::generatePassword('basic1'); ?> -->
        <?php if (!empty($_POST['password']) && $_POST['password'] != Mission::generatePassword('basic1')): ?>
        <center><b><u>Invalid password.</u></b></center>
        <?php endif; ?>
		<center><b>password:</b><br /><form action="<?php echo Url::format('missions/basic/1/index.php'); ?>" method="post"><input type="password" name="password" /><br /><br /><input type="submit" value="submit" /></form></center>
                <center><table border="0" width="80%" cellspacing="0" cellpadding="0">
                <tr>
                        <td class="dark-td">&nbsp;<b>Help!</b></td>
                </tr>
                        <td class="light-td">&nbsp;If you have no idea what to do, you must <a href="http://www.w3schools.com/HTML">learn HTML</a>.
			<br /></td>
                </tr>
        </table></center>
