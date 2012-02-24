<?php if (!empty($valid) && $valid): ?>
<h4><u>Edit Comment</u></h4>
<form action="<?php echo Url::format('/comment/edit/' . $post['_id'] . '/save'); ?>" method="post">
    <input type="hidden" name="contentId" value="<?php echo $post['contentId']; ?>" />
    <textarea name="text"><?php echo $post['text']; ?></textarea><br />
    <input type="submit" name="submit" value="Post Comment" />
</form>
<?php endif; ?>
