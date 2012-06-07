<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Account Reclamation</h1></div>

<p>Fill out the form below to have your old HackThisSite account converted 
into the new format.</p>
<center><form action="<?php echo Url::format('/reclaim/check'); ?>" method="post">
    Username: <input type="text" name="username" /><br />
    Password: <input type="password" name="password" /><br />
    
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
    <input type="submit" class="btn btn-success" value="Validate" />
</form></center>
<?php endif; ?>
