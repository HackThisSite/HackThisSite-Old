<?php
if (!empty($good) && $good) {
	echo $template->textForm(array('title' => $title, 'text' => $text), 'admin/post_news/save/' . $id);
}
