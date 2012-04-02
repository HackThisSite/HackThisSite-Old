<?php if (!empty($valid) && $valid): ?>
<u><h2>User Profile:  <?php echo $user['username']; ?></h2></u>

<b>Email: </b> <?php 
echo ($user['hideEmail'] ? 'hidden' : htmlentities($user['email'], ENT_QUOTES, '', false)); 
?><br />
<b>Warn Level: </b> <?php echo $user['warnLevel']; ?><br />
<b>Missions:  </b><br />
<?php
if (!empty($user['missions'])) { 
	foreach ($user['missions'] as $type => $missions) {
?>
&nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo ucwords($type); ?>:  </b>
<?php
	echo implode(', ', array_keys($missions)), '<br />';
	}
}
endif;
