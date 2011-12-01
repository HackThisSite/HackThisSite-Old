<?php
if (!empty($news)) {
	if ($multiple) {
		$first = true;
		foreach ($news as $post) {
			if (!$first) {
				echo '<br/ ><hr /><br />';
			}
			
			echo Partial::render('newsFull', $post);
			$first = false;
		}
	} else {
		
		echo Partial::render('newsFull', $news[0]);
		
		if ($news[0]['commentable']) {
			echo Partial::render('comment', array('id' => $news[0]['_id'], 'page' => 1));
		}
	}
}
?>
