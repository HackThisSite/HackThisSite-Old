<?php 
$location = Url::format('news/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); 
?>
<h2><a href="<?php echo $location; ?>" title=""><?php echo $title; ?></a></h2>
        
<p class="post-info">Posted by <?php echo $user['username']; ?></p>
    
<p><?php echo wordwrap(BBCode::parse($body), 100, "\n", true); ?></p>
    
<p class="post-footer">	
<a href="<?php echo $location; ?>" class="readmore">Read more</a> |
<a href="<?php echo $location; ?>" class="comments">Comments</a> |				
<span class="date"><?php echo Date::dayFormat($date); ?></span>

</p>
