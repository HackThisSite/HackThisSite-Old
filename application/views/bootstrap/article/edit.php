<?php if (!empty($valid) && $valid): 
extract($post);
if (!is_array($tags)) { $tags = explode(',', clean($tags)); }
$category = clean($category);
?>
<div class="page-header"><h1><?php echo ucwords($method); ?> Article</h1></div>
<?php
if (!empty($preview) && $preview && !is_string($info)) {
    echo Partial::render('articleFull', $info);
}
?>
<form class="form-vertical well" action="<?php echo Url::format('/article/' . $method . (empty($_id) ? '' : '/' . $_id) . '/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" value="<?php echo clean($title); ?>" /><br />
    <label>Category:  </label>
    <select name="category">
<?php foreach (articles::$categories as $short => $rCategory): ?>
        <option value="<?php echo $short; ?>"<?php if ($short == $category): ?> selected="selected"<?php endif; ?>><?php echo $rCategory; ?></option>
<?php endforeach; ?>
    </select>
    <label>Description:</label>
    <textarea rows="5" style="width: 100%" name="description"><?php echo clean($description); ?></textarea><br />
    <label>Text:  </label><br />
    <textarea rows="20" style="width:100%" name="body"><?php echo clean($body); ?></textarea><br />
    <label>Tags:  </label> <input type="text" name="tags" value="<?php echo implode(',', $tags); ?>" /><br />
    
    <input type="submit" class="btn btn-info" name="preview" value="Preview" />
    <input type="submit" class="btn btn-primary" name="post" value="<?php echo ucwords($method); ?> News" />
</form>
<?php endif; ?>
