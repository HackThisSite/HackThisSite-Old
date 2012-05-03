<?php if (!empty($valid) && $valid) : 
extract($post); ?>
<div class="page-header"><h1>Edit Article</h1></div>

<form class="form-vertical well" action="<?php echo Url::format('/article/edit/' . $_id . '/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" value="<?php echo $title; ?>" /><br />
    <label>Category:  </label>
    <select name="category">
<?php foreach (articles::$categories as $short => $rCategory): ?>
        <option value="<?php echo $short; ?>"<?php if ($short == $category): ?> selected="selected"<?php endif; ?>><?php echo $rCategory; ?></option>
<?php endforeach; ?>
    </select>
    <label>Description:</label>
    <textarea rows="5" style="width: 100%" name="description"><?php echo $description; ?></textarea><br />
    <label>Text:  </label><br />
    <textarea rows="20" style="width:100%" name="text"><?php echo $body; ?></textarea><br />
    <label>Tags:  </label> <input type="text" name="tags" value="<?php echo implode(',', $tags); ?>" /><br />
    <input type="submit" value="Submit" class="btn btn-primary"/>
</form>
<?php endif; ?>
