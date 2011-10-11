<?php
class controller_news extends Controller {
	
	public function index() {
		// Nil
	}
	
	public function view($arguments) {
		if (empty($arguments[0])) return $this->setError('Invalid id.');
		$newsModel = new News;
		$news = $newsModel->getNews($arguments[0]);
		
		if (empty($news)) return $this->setError('Invalid id.');
		
		$this->view['news'] = $news;
		$this->cache();
	}
}
