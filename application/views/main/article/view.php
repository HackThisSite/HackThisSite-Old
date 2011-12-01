<?php
if (!empty($article)) {
	if ($multiple) {
		$first = true;
		foreach ($article as $post) {
			if (!$first) {
				echo '<br/ ><hr /><br />';
			}
			
			echo Partial::render('articleFull', $post);
			$first = false;
		}
	} else {
		
		echo Partial::render('articleFull', $article[0]);
		
		if ($article[0]['commentable']) {
			echo Partial::render('comment', array('id' => $article[0]['_id'], 'page' => 1));
		}
	}
}
?>
