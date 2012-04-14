<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Post Notice</h1></div>

<form class="form-inline well" action="<?php echo Url::format('/notice/post/save'); ?>" method="post">
    <strong>New Notice:  </strong> <input type="text" name="text" />&nbsp;
    <input type="submit" value="Post Notice" class="btn btn-primary" />
</form>
<?php endif; ?>
