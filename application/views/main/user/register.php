<?php if (!empty($valid) && $valid): ?>
<h2><u>Register</u></h2>

<form action="<?php echo Url::format('/user/register/save'); ?>" method="post">
    <b>Username:  </b> <input type="text" name="username" /><br />
    <b>Password:  </b> <input type="text" name="password" /><br />
    <b>Email:  </b> <input type="text" name="email" /><br />
    <input type="submit" name="submit" value="Register" />&nbsp;
    <input type="reset" value="Reset" />
</form>
<?php endif; ?>
