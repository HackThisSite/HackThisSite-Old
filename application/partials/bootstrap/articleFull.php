<div class="well" style="word-wrap: break-word">
	<h1><?php echo $title; ?></h1>
	<small>
		<?php if (empty($revision)): ?>
		Posted by: <?php echo (is_string($user) ? $user : $user['username']); ?> 
        on <?php echo Date::dayFormat($date); ?> 
        under <?php echo articles::$categories[$category]; ?>.
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

<?php if (!empty($mlt)): ?>
    <p><h4>More Like This:</h4>
<?php
foreach ($mlt as $fetched) {
    echo '<a href="' . Url::format('article/view/' . Id::create($fetched, 'news')) . '">' . $fetched['title'] . '</a><br />';
}
?></p>
<?php endif; ?>
<?php if ($published): ?>
    <p style="margin-top: 20px"><br />
<?php if ($rating == 0): ?>
        <em>No ratings yet!</em>
<?php else: ?>
        <em>Average rating of:  <?php echo $rating; ?></em>
<?php endif; ?>
<?php if (CheckAcl::can('voteOnArticles')): ?>
        <form class="form-inline" action="<?php echo Url::format('/article/vote/'); ?>" method="post">
            <select name="vote" class="input-mini">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select>
            <input type="hidden" name="articleId" value="<?php echo $_id; ?>" />
            <input type="submit" value="Go" class="btn btn-success" />
        </form>
<?php endif; ?>
    </p>
<?php endif; ?>
</div>
