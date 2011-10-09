<?php
if (!empty($news)) {
	echo $template->showNews($news);
	
	if ($news['commentable']) {
		echo new Widget('comment', array('id' => $news['_id'], 'page' => 1));
	}
}
?>
