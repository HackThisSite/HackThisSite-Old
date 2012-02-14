<?php if (!empty($valid) && $valid): ?>
<h3><u>Post News</u></h3>

<form action="<?php echo Url::format('/news/post/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" /><br />
    <b>Department:  </b> <input type="text" name="department" /><br />
    <b>Text:  </b><br />
    <textarea cols="50" rows="20" name="text"></textarea><br />
    <b>Commentable:  </b> <input type="checkbox" name="commentable" value="yes" /><br />
    <b>Short News:  </b> <input type="checkbox" name="shortNews" value="yes" /><br />
    <input type="submit" name="submit" value="Post News" />
</form>
<?php endif; ?>
