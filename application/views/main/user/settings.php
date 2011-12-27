<?php
if (!empty($valid) && $valid):
?>
<u><h2>Edit User:  <?php echo $user['username']; ?></h2></u>

<form action="<?php echo Url::format('/user/settings/save'); ?>" method="post">
<b>Email:  </b><input type="text" name="email" value="<?php echo htmlentities($user['email'], ENT_QUOTES, '', false); ?>"/><br />
<input type="submit" name="submit" value="Save" />
</form>
<?php
endif;
?>
