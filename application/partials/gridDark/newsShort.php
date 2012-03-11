<b style="font-size: 16px;"><span style="font-size: 12px;">
<img src="http://2.static.htscdn.org/images/tick.gif" alt="#" />
<?php echo Date::dayFormat($date); ?>:</span>
&nbsp;&nbsp;<?php echo $title; ?></b>
<span style="display:none;font-size: 9px;"><br /></span>

<div class="news" align="left">
<?php echo BBCode::parse(wordwrap($body, 100, "\n", true)); ?>
</div><br /><br />

<span style="font-size: 10px;">
<a href="<?php echo Url::format('news/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>">read more...</a> | 
<a href="<?php echo Url::format('news/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>#comments">comments (2)</a>
<br /><br /></span>
