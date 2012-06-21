<?php if (!empty($valid) && $valid): 
extract($post);
if (!is_array($tags)) { $tags = explode(',', clean($tags)); }
?>
<div class="page-header"><h1><?php echo ucwords($method); ?> News</h1></div>
<?php
if (!empty($preview) && $preview && !is_string($info)) {
    echo Partial::render('newsFull', $info);
}
?>
<form class="well form-vertical" action="<?php echo Url::format('/news/' . $method . (empty($_id) ? '' : '/' . $_id) . '/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" value="<?php echo clean($title); ?>" /><br />
    <label>Department:  </label> <input type="text" name="department" value="<?php echo (!empty($department) ? clean($department) : ''); ?>" /><br />
    <label>Text:  </label>
    <textarea style="width: 100%" rows="15" name="body"><?php echo clean($body); ?></textarea><br />
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
	
    <input type="submit" class="btn btn-info" name="preview" value="Preview" />
    <input type="submit" class="btn btn-primary" name="post" value="<?php echo ucwords($method); ?> News" />
</form>
<?php endif; ?>
