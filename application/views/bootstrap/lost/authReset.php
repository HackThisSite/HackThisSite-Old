Check your email for a link to restore access to your account.
<?php if (!$mail): ?>
<br /><br />
Mail is down so 
<a href="<?php echo Url::format('/lost/confirm/' . $id . '/auth'); ?>">click here</a>.
<?php endif; ?>
