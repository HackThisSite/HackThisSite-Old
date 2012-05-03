<?php 
$rand = rand(1, 3); 
if ($rand == 1): ?>
<center>
    <img src="<?php echo Url::format('/themes/bootstrap/img/404.png', true); ?>" />
</center>
<?php elseif ($rand == 2): ?>
<center>
    <h1>The Denver DC</h1><br /><br />
    <img src="<?php echo Url::format('/themes/bootstrap/img/beasts.png', true); ?>" /><br />
    <p>This is why we can't have nice things.<br />
    Oh, and by the way, we couldn't find the page you were looking for.<br />
    <em>Sorry.</em></p>
</center>
<?php elseif ($rand == 3): ?>
<div class="page-header"><h1>We Couldn't Find That</h1></div>
<center>
    <p>We may have forgotten to feed the beasties who <br />live in our server 
    closet, which often results in unexpected <br />data loss due to gnawing 
    and/or mutiny.</p>
    <img src="<?php echo Url::format('/themes/bootstrap/img/beasts.png', true); ?>" /><br />
    <p>We apologize for the inconvenience.</p>
</center>
<?php endif; ?>
