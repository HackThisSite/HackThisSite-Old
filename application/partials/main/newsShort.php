<h3 style="padding-bottom: 0px;margin-bottom: 0px;"><?php echo $title; ?></h3>
<sub>Posted by: <?php echo $user['username']; ?> on <?php echo Date::dayFormat($date); ?></sub>
<p><?php echo $body; ?></p>
<a href="<?php echo Url::format('news/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>">Read more...</a>
<hr />
