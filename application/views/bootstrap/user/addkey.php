<?php if (!empty($valid) && $valid): ?>
<u><h2>Your Certificate:</h2></u>
<pre>
<?php echo $certificate; ?>
</pre><br />

<i>Save this to file so your browser can use it!</i><br />
<a href="<?php echo Url::format('pages/info/keyauthentication'); ?>">Read More...</a>
<?php endif; ?>
