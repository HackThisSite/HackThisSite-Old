<?php if (!empty($valid) && $valid): ?>
<form class="form-inline" action="<?php echo Url::format('/lost/access'); ?>" method="post">
	<label>Your username:</label>
	<input type="text" name="username" />&nbsp;
	<input type="submit" value="Go" class="btn btn-primary" />
</form>
<?php endif; ?>
