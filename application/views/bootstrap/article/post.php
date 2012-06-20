<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Post Article</h1></div>

<form class="form-vertical well" action="<?php echo Url::format('/article/post/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" /><br />
    <label>Category:  </label>
    <select name="category">
<?php foreach (articles::$categories as $short => $category): ?>
        <option value="<?php echo $short; ?>"><?php echo $category; ?></option>
<?php endforeach; ?>
    </select>
    <label>Description:</label>
    <textarea rows="5" style="width: 100%" name="description"></textarea><br />
    <label>Text:  </label>
    <textarea rows="20" style="width: 100%" name="text"></textarea><br />
    <label>Tags:  </label> <input type="text" name="tags" />
    <span class="help-inline">(Comma seperated list of tags)</span><br />
    <input type="submit" value="Post Article" class="btn btn-primary" />
</form>
<?php else: ?>
<p>You're article has been submitted for approval by a moderator.  It really 
shouldn't take more than a few days.  If it does, then contact a moderator.</p>
<?php endif; ?>
