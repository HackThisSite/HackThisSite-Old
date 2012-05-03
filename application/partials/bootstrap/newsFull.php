<div class="well" style="word-wrap: break-word">
	<h1><?php echo $title; ?></h1>
	<small>
		<?php if (empty($revision)): ?>
		Posted by: <?php echo $user['username']; ?> on <?php echo Date::dayFormat($date); ?>
		<?php else: ?>
		Replaced on <?php echo Date::dayFormat($_id->getTimestamp()); ?>
		<?php endif; ?>
		<?php if (!empty($department)): ?> from the <?php echo $department; ?> dept<?php endif; ?>
		<?php if (empty($revision)): ?>
		<?php if (CheckAcl::can('editNews')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/edit/' . $_id); ?>">Edit</a><?php endif; ?>
		<?php if (CheckAcl::can('deleteNews')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/delete/' . $_id); ?>">Delete</a><?php endif; ?>
		<?php if (CheckAcl::can('viewNewsRevisions')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/revisions/' . $_id); ?>">Revisions</a><?php endif; ?>
		<?php else: ?>
		<?php if (CheckAcl::can('revertNews')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/revisions/' . $currentId . '/revert/' . $_id); ?>">Revert</a><?php endif; ?>
		<?php endif; ?>
	</small>
	
	<p><?php echo BBCode::parse($body); ?></p>
    
<?php if (!empty($mlt)): ?>
    <p><h4>More Like This:</h4>
<?php
foreach ($mlt as $fetched) {
    echo '<a href="' . Url::format('news/view/' . Id::create($fetched, 'news')) . '">' . $fetched['title'] . '</a><br />';
}
?></p>
<?php endif; ?>
</div>
