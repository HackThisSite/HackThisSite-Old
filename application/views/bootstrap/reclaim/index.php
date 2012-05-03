<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Account Reclamation</h1></div>

<p>Fill out the form below to have your old HackThisSite account converted 
into the new format.</p>
<center><form action="<?php echo Url::format('/reclaim/check'); ?>" method="post">
    Username: <input type="text" name="username" /><br />
    Password: <input type="text" name="password" /><br />
    <input type="submit" class="btn btn-success" value="Validate" />
</form></center>
<?php endif; ?>
