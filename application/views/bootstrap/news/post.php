<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Post News</h1></div>

<form class="well form-veritcal" action="<?php echo Url::format('/news/post/save'); ?>" method="post">
    <label>Title:  </label> <input type="text" name="title" /><br />
    <label>Department:  </label> <input type="text" name="department" /><br />
    <label>Text:  </label>
    <textarea style="width: 100%" rows="10" name="text"></textarea><br />
    <label>Tags:  </label> <input type="text" name="tags" /> <span class="help-inline">(Comma seperated list of tags)</span><br />
    <label class="checkbox"><input type="checkbox" name="commentable" value="yes" />  Commentable</label>
    <label class="checkbox"><input type="checkbox" name="shortNews" value="yes" />  Short News</label>
    <input type="submit" class="btn btn-primary" value="Post News" />
</form>
<?php endif; ?>
