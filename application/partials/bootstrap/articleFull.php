<div class="well" style="word-wrap: break-word">
	<h1><?php echo $title; ?></h1>
	<small>
		<?php if (empty($revision)): ?>
		Posted by: <?php echo (is_string($user) ? $user : $user['username']); ?> 
        on <?php echo Date::dayFormat($date); ?> 
        under <?php echo articles::$categories[$category]; ?>.
        <?php else: ?>
        Replaced on <?php echo Date::dayFormat($_id->getTimestamp()); ?>
        <?php endif; ?>
        <?php if (empty($revision) && empty($preview)): ?>
		<?php if ($published): ?>
		<?php if (CheckAcl::can('editArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/edit/' . $_id); ?>">Edit</a><?php endif; ?>
		<?php if (CheckAcl::can('deleteArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/delete/' . $_id); ?>">Delete</a><?php endif; ?>
		<?php if (CheckAcl::can('viewArticleRevisions')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/revisions/' . $_id); ?>">Revisions</a><?php endif; ?>
		<?php endif;elseif (empty($preview)): ?>
		<?php if (CheckAcl::can('revertArticles')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/articles/revisions/' . $contentId . '/revert/' . $_id); ?>">Revert</a><?php endif; ?>
		<?php endif; ?>
	</small>
    
<?php if (!$published || !empty($revision) || !empty($preview)): ?><br /><br />
<blockquote>
<strong>Description:</strong><br />
<?php echo $description; ?>
</blockquote>
<?php endif; ?>
	
	<p><?php echo BBCode::parse($body); ?></p>

<?php if (!empty($mlt)): ?>
    <p><h4>More Like This:</h4>
<?php
foreach ($mlt as $fetched) {
    echo '<a href="' . Url::format('article/view/' . Id::create($fetched, 'news')) . '">' . $fetched['title'] . '</a><br />';
}
?></p>
<?php endif; ?>
<?php if ($published && empty($revision) && empty($preview)):
$data = array(
    '_id' => $_id,
    'rating' => $rating,
    'type' => 'Articles',
    'where' => 'article'
);
echo Partial::render('like', $data);
endif; ?>
</div>
