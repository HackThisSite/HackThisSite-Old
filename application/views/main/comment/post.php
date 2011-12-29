<?php if (!empty($valid) && $valid && !empty($forms['id'])): ?>
<h4><u>New Comment</u></h4>
<form action="<?php echo Url::format('/comment/post/save/' . $id); ?>" method="post">
    <textarea name="text"></textarea><br />
    <input type="submit" name="submit" value="Post Comment" />
</form>
<?php elseif (isset($valid) && !$valid): ?>
<a href="<?php echo Url::format((empty($_SERVER['HTTP_REFERER']) ? '/' : $_SERVER['HTTP_REFERER'])); ?>">Go Back</a>
<?php endif; ?>
