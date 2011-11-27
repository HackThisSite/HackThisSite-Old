<?php
// Comments
// * $id - Content Id
// * $page - Comments page

$commLib = new comments;
$comments = $commLib->getComments($id, $page);

echo $template->commentHeader(count($comments));

if (empty($comments))
	echo $template->noComments();

foreach ($comments as $comment) {
	echo $template->comment($comment);
}

echo $template->commentFooter();
