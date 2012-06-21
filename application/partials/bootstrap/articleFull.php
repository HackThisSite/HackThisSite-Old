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
<?php if ($published && empty($revision) && empty($preview)): ?>
    <p style="margin-top: 20px"><br />
<?php if ($rating == 0): ?>
        <em>No ratings yet!</em>
<?php else:
$html = array();

if ($rating['likes'] != 0)
    $html[] = $rating['likes'] . ' like' . ($rating['likes'] == 1 ? '' : 's');
if ($rating['dislikes'] != 0)
    $html[] = $rating['dislikes'] . ' dislike' . ($rating['dislikes'] == 1 ? '' : 's');

$html = implode(', ', $html);
if (empty($html))
    $html = 'No votes!';
endif; ?>
        <em><?php echo $html; ?></em>
<?php if (CheckAcl::can('voteOnArticles')): ?>
        <a href="<?php echo Url::format('/article/vote/' . $_id . '/like'); ?>" 
        class="btn btn-small">
            <i class="icon-plus"></i>
            Like
        </a>&nbsp;
        <a href="<?php echo Url::format('/article/vote/' . $_id . '/dislike'); ?>" 
        class="btn btn-inverse btn-small">
            <i class="icon-minus icon-white"></i>
            Dislike
        </a>
<?php endif; ?>
    </p>
<?php endif; ?>
</div>
