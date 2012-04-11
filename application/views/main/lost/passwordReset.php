Your password has been reset.  Check your email for a link.
<?php if (!$mail): ?>
<br /><br />
Mail is down so 
<a href="<?php echo Url::format('/lost/confirm/' . $id); ?>">click here</a>.
<?php endif; ?>
