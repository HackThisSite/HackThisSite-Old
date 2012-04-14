<div class="well" style="word-wrap: break-word">
	<h1><?php echo $title; ?></h1>
	<small>
		Posted by: <?php echo $user['username']; ?> 
		on <?php echo Date::dayFormat($date); ?>
<?php if (!empty($department)): ?>
		from the <?php echo $department; ?> dept
<?php endif; ?>
	</small>
	
	<p><?php echo BBCode::parse($body); ?></p>
	
	<a class="btn btn-primary" href="<?php echo Url::format('news/view/' . Id::create(array('date' => $date, 'title' => $title), 'news')); ?>">
		Read More
	</a>
</div>
