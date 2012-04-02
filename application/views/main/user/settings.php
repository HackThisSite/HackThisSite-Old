<?php
if (!empty($valid) && $valid):
?>
<u><h2>Edit User:  <?php echo $user['username']; ?></h2></u>

<form action="<?php echo Url::format('/user/settings/save'); ?>" method="post">
<?php if (CheckAcl::can('changeUsername')): ?>
	<b>Username: </b><input type="text" name="username" value="<?php echo htmlentities($user['username'], ENT_QUOTES, '', false); ?>" /><br />
<?php endif; ?>
    <b>Email:  </b><input type="text" name="email" value="<?php echo htmlentities($user['email'], ENT_QUOTES, '', false); ?>"/><br />
    <b>Hide your email?  </b> <input type="checkbox" name="hideEmail" value="true"<?php echo ($user['hideEmail'] ? ' checked="checked"' : ''); ?> /><br />
<?php if (CheckAcl::can('editAcl')): ?>
    <b>Group:  </b><select name="group">
<?php foreach (acl::$acls as $acl): ?>
        <option value="<?php echo $acl; ?>"<?php echo ($acl == $user['group'] ? 'selected="selected"' : ''); ?>><?php echo ucwords($acl); ?></option>
<?php endforeach; ?>
    </select><br />
    <?php endif; ?>
    
    <br />
	<b><u>Change Password</u></b><br />
	<b>Old Password:  </b><input type="text" name="oldpassword" /><br />
	<b>New Password:  </b><input type="text" name="password" /><br />
    <input type="submit" name="submit" value="Save" />
</form>
<?php
endif;
?>
