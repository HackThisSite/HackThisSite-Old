<?php
if (!empty($valid) && $valid):
?>
<u><h2>Edit User:  <?php echo $user['username']; ?></h2></u>

<form action="<?php echo Url::format('/user/settings/save'); ?>" method="post">
    <b>Email:  </b><input type="text" name="email" value="<?php echo htmlentities($user['email'], ENT_QUOTES, '', false); ?>"/><br />
<?php if (CheckAcl::can('editAcl')): ?>
    <b>Group:  </b><select name="group">
<?php foreach (acl::$acls as $acl): ?>
        <option value="<?php echo $acl; ?>"<?php echo ($acl == $user['group'] ? 'selected="selected"' : ''); ?>><?php echo ucwords($acl); ?></option>
<?php endforeach; ?>
    </select><br />
    <?php endif; ?>
    <input type="submit" name="submit" value="Save" />
</form>
<?php
endif;
?>
