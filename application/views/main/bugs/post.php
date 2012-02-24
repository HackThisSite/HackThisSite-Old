<?php if (!empty($valid) && $valid): ?>
<h3><u>Post Article</u></h3>

<form action="<?php echo Url::format('/bugs/post/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" /><br />
    <b>Category:  </b> <select name="category">
<?php foreach (bugs::$category as $key => $cat): ?>
        <option value="<?php echo $key; ?>"><?php echo $cat; ?></option>

<?php endforeach; ?>
    </select><br />
    <b>Description:  </b><br />
    <textarea cols="50" rows="10" name="description"></textarea><br />
    <b>Steps to Reproduce:  </b><br />
    <textarea cols="50" rows="10" name="reproduction"></textarea><br />
    <b>Public:  </b> <input type="checkbox" name="public" value="1" checked="checked" /><br />
    <input type="submit" name="submit" value="Post Bug" />
</form>
<?php endif; ?>
