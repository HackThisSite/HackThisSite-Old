<table border="1" width="90%"><tr><td>
    <a href="<?php echo Url::format('article/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>"><?php echo $title; ?></a>
</td></tr></table>
