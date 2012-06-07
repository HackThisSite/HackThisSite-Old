<center><form action="<?php echo Url::format('/user/login'); ?>" method="post">
    Username: <input type="text" name="username" /><br />
    Password: <input type="password" name="password" /><br />
<?php if ($captcha): ?>
    <div class="control-group">
    <script type="text/javascript"src="http://www.google.com/recaptcha/api/challenge?k=<?php echo $publicKey; ?>"></script>
    <noscript>
        <iframe src="http://www.google.com/recaptcha/api/noscript?k=<?php echo $publicKey; ?>"
        height="300" width="500" frameborder="0"></iframe><br>
        <textarea name="recaptcha_challenge_field" rows="3" cols="40">
        </textarea>
        <input type="hidden" name="recaptcha_response_field"
        value="manual_challenge">
    </noscript>
    </div>
<?php endif; ?>
    <input type="submit" class="btn btn-primary" value="Login" />&nbsp;
    <a href="<?php echo Url::format('/user/register'); ?>" class="btn">Register</a><br />
    <a href="<?php echo Url::format('/lost'); ?>">Lost your password?</a><br />
    <br />
    <a class="btn btn-info" href="<?php echo Url::format('/reclaim'); ?>">Reclaim Your Account</a>
</form></center>
