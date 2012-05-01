<div class="well">
    <h3><a href="<?php echo Url::format('article/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>"><?php echo $title; ?></a></h3>
    
    <p><?php echo $description; ?></p>
</div>
