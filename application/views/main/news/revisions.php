<?php 
if (isset($revisions)) {
?>
<h2><u>Current:</u></h2>
<?php echo Partial::render('newsFull', $current); ?><br />
<br />
<?php if (!empty($revisions)): ?><h2><u>Revisions:</u></h2><?php endif; ?>
<?php
    foreach ($revisions as $revision) {
        $revision['revision'] = true;
        $revision['currentId'] = $current['_id'];
        echo Partial::render('newsFull', $revision);
    }
}
