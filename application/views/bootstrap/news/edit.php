<?php if (!empty($valid) && $valid): 
extract($post); ?>
<div class="page-header"><h1>Edit News</h1></div>

<form class="well form-vertical" action="<?php echo Url::format('/news/edit/' . $_id . '/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" value="<?php echo $title; ?>" /><br />
    <label>Department:  </label> <input type="text" name="department" value="<?php echo (!empty($department) ? $department : ''); ?>" /><br />
    <label>Text:  </label>
    <textarea style="width: 100%" rows="15" name="text"><?php echo $body; ?></textarea><br />
    <label>Tags:  </label> <input type="text" name="tags" value="<?php echo implode(',', $tags); ?>" /><br />
    <label class="checkbox">
		<input type="checkbox" name="commentable" value="yes"
		<?php echo ($commentable ? ' checked="checked"' : ''); ?> />
		Commentable
	</label>
	
    <label class="checkbox">
		<input type="checkbox" name="shortNews" value="yes"<?php echo ($shortNews ? ' checked = "checked"' : ''); ?> />
		Short News
	</label>
	
    <input type="submit" class="btn btn-primary" value="Edit News" />
</form>
<?php endif; ?>
