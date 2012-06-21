<?php 
if (isset($revisions)) {
?>
<h2><u>Current:</u></h2>
<?php $current['preview'] = true;echo Partial::render('articleFull', $current); ?><br />
<br />
<?php if (!empty($revisions)): ?><h2><u>Revisions:</u></h2><?php endif; ?>
<?php
    foreach ($revisions as $revision) {
        $revision['revision'] = true;
        echo Partial::render('articleFull', $revision);
    }
}
