<?php if (!empty($valid) && $valid): ?>
<?php print_r($GLOBALS); ?>
<h4><u>New Comment</u></h4>
<form action="<?php echo Url::format('/comment/post/save/' . $id); ?>" method="post">
    <textarea name="text"></textarea><br />
    <input type="submit" name="submit" value="Post Comment" />
</form>
<?php else: ?>
<a href="<?php echo Url::format($_SERVER['HTTP_REFERER']); ?>">Go Back</a>
<?php endif; ?>
