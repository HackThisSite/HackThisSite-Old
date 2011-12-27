<?php if (!empty($valid) && $valid): ?>
<h3><u>Post Article</u></h3>

<form action="<?php echo Url::format('/article/post/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" /><br />
    <b>Text:  </b><br />
    <textarea cols="50" rows="20" name="text"></textarea><br />
    <input type="submit" name="submit" value="Post Article" />
</form>
<?php endif; ?>
