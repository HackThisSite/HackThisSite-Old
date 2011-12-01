<?php
if (!empty($valid) && $valid):
?>
<u><h2>User Profile:  <?php echo $user['username']; ?></h2></u>

<b>Status: </b> <?php echo ucwords($user['status']); ?><br />
<b>Email: </b> <?php echo htmlentities($user['email'], ENT_QUOTES, '', false); ?><br />
<b>Warn Level: </b> <?php echo $user['warnLevel']; ?><br />
<?php
endif;
?>
