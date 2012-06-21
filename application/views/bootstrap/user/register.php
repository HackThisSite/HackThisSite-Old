<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Register</h1></div>

<form class="well form-vertical" action="<?php echo Url::format('/user/register/save'); ?>" method="post">
<label>Username</label>
<input type="text" name="username" required /><br />

<label>Password</label>
<input type="text" name="password" required /><br />

<label>Email</label>
<input type="text" name="email" required /><br />

<label class="checkbox">
	<input type="checkbox" name="hideEmail" value="true" /> Hide Your Email?
</label>

<div class="control-group">
<script type="text/javascript">
    var RecaptchaOptions = {
        theme : 'white'
    };
</script>
<script type="text/javascript"src="https://www.google.com/recaptcha/api/challenge?k=<?php echo $publicKey; ?>"></script>
<noscript>
    <iframe src="https://www.google.com/recaptcha/api/noscript?k=<?php echo $publicKey; ?>"
    height="300" width="500" frameborder="0"></iframe><br>
    <textarea name="recaptcha_challenge_field" rows="3" cols="40">
    </textarea>
    <input type="hidden" name="recaptcha_response_field"
    value="manual_challenge">
</noscript>
</div>

<input type="submit" value="Register" class="btn btn-primary" />
<input type="reset" value="Reset" class="btn btn-danger" /><br />
</form>
<?php endif; ?>
