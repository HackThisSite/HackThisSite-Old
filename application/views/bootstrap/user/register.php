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

<input type="submit" value="Register" class="btn btn-primary" />
<input type="reset" value="Reset" class="btn btn-danger" /><br />
</form>
<?php endif; ?>
