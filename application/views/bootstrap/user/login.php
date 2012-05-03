<center><form action="<?php echo Url::format('/user/login'); ?>" method="post">
    Username: <input type="text" name="username" /><br />
    Password: <input type="text" name="password" /><br />
    <input type="submit" class="btn btn-primary" value="Login" />&nbsp;
    <a href="<?php echo Url::format('/user/register'); ?>" class="btn">Register</a><br />
    <a href="<?php echo Url::format('/lost'); ?>">Lost your password?</a>
</form></center>
