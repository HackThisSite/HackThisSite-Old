<?php if (!empty($valid) && $valid): ?>
<h3><u>Post Notice</u></h3>

<form action="<?php echo Url::format('/notice/post/save'); ?>" method="post">
    <b>Text:  </b> <input type="text" name="text" /><br />
    <input type="submit" name="submit" value="Post Notice" />
</form>
<?php endif; ?>
