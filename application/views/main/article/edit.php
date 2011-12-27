<?php if (!empty($valid) && $valid) : 
extract(reset($post)); ?>
<h3><u>Edit Article</u></h3>

<form action="<?php echo Url::format('/article/edit/' . $_id . '/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" value="<?php echo $title; ?>" /><br />
    <b>Text:  </b><br />
    <textarea cols="50" rows="20" name="text"><?php echo $body; ?></textarea><br />
    <b>Commentable:  </b> <input type="checkbox" name="commentable" value="yes"<?php echo ($commentable ? ' checked="checked"' : ''); ?> /><br />
    <input type="submit" name="submit" value="Edit Article" />
</form>
<?php endif; ?>
