<?php 
if (isset($revisions)) {
?>
<div class="page-header"><h1>Current</h1></div>
<?php echo Partial::render('newsFull', $current); ?><br />
<?php if (!empty($revisions)): ?><div class="page-header"><h1>Revisions</h1></div><?php endif; ?>
<?php
    foreach ($revisions as $revision) {
        $revision['revision'] = true;
        $revision['currentId'] = $current['_id'];
        echo Partial::render('newsFull', $revision);
    }
}
