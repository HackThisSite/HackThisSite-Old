<?php if (!empty($valid) && $valid) : 
extract($post); ?>
<div class="page-header"><h1>Edit Notice</h1></div>

<form class="form-horizontal" action="<?php echo Url::format('/notice/edit/' . ++$id . '/save'); ?>" method="post">
    <b>Text:  </b> <input type="text" name="text" value="<?php echo $notice; ?>" />&nbsp;
    <input type="submit" value="Edit Notice" class="btn btn-primary" />
</form>
<?php endif; ?>
