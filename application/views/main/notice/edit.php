<?php if (!empty($valid) && $valid) : 
extract($post); ?>
<h3><u>Edit Notice</u></h3>

<form action="<?php echo Url::format('/notice/edit/' . ++$id . '/save'); ?>" method="post">
    <b>Text:  </b> <input type="text" name="text" value="<?php echo $notice; ?>" /><br />
    <input type="submit" name="submit" value="Edit Notice" />
</form>
<?php endif; ?>
