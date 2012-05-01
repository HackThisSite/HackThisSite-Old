<div class="well" style="word-wrap: break-word">
	<h1><?php echo $title; ?></h1>
	<small>
		<?php if (empty($revision)): ?>
		Posted by: <?php echo (is_string($user) ? $user : $user['username']); ?> on <?php echo Date::dayFormat($date); ?>
		<?php if ($published): ?>
		<?php if (CheckAcl::can('editArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/edit/' . $_id); ?>">Edit</a><?php endif; ?>
		<?php if (CheckAcl::can('deleteArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/delete/' . $_id); ?>">Delete</a><?php endif; ?>
		<?php if (CheckAcl::can('viewArticleRevisions')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/revisions/' . $_id); ?>">Revisions</a><?php endif; ?>
		<?php endif;else: ?>
		Replaced on <?php echo Date::dayFormat($_id->getTimestamp()); ?>
		<?php endif; ?>
	</small>
    
<?php if (!$published): ?><br /><br />
<blockquote>
<strong>Description:</strong><br />
<?php echo $description; ?>
</blockquote>
<?php endif; ?>
	
	<p><?php echo BBCode::parse($body); ?></p>
</div>
