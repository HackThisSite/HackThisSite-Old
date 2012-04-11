<?php if (!empty($valid) && $valid): ?>
<form action="<?php echo Url::format('/lost/access'); ?>" method="post">
	<b>What's your username?</b><br />
	<input type="text" name="username" />&nbsp;
	<input type="submit" value="Go" />
</form>
<?php endif; ?>
