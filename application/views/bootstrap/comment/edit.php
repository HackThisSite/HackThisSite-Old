<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Edit Comment</h1></div>
<form class="well" action="<?php echo Url::format('/comment/edit/' . $post['_id'] . '/save'); ?>" method="post">
    <input type="hidden" name="contentId" value="<?php echo $post['contentId']; ?>" />
    <textarea name="text" rows="10" style="width: 100%"><?php echo $post['text']; ?></textarea><br />
    <input type="submit" value="Post Comment" class="btn btn-primary" />
</form>
<?php endif; ?>
