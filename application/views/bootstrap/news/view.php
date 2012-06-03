<?php
if (!empty($news)) {
	if ($multiple) {
		foreach ($news as $post) {
			echo Partial::render('newsShort', $post);
		}
	} else {
        $news[0]['mlt'] = $mlt;
		echo Partial::render('newsFull', $news[0]);
		
		if ($news[0]['commentable']) {
			echo Partial::render('comment', array(
                'id' => $news[0]['_id'], 
                'page' => $commentPage, 
                'pageLoc' => $commentPageLoc
            ));
		}
	}
}
?>
