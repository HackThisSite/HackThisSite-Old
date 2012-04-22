<?php
if (!empty($article)):
    echo Partial::render('articleFull', $article);
?>
<hr />
<form action="<?php echo Url::format('article/approve/save'); ?>" method="post">
    <center>
        <input type="submit" name="decision" value="Publish" class="btn btn-success" />
        &nbsp;
        <input type="submit" name="decision" value="Delete" class="btn btn-danger" />
    </center>
    
    <input type="hidden" name="id" value="<?php echo $article['_id']; ?>" />
</form>
<?php
endif;
?>
