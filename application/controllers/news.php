<?php
class controller_news extends Controller {
	
	public function index() {
		// Nil
	}
	
	public function view($arguments) {
		@$id = implode('/', $arguments);
		if (empty($id)) return $this->setError('Invalid id.');
		$newsModel = new News;
		$news = $newsModel->getNews($id);
		
		if (empty($news)) return $this->setError('Invalid id.');
		
		$this->view['news'] = $news;
		$this->view['multiple'] = (count($news) > 1);
		$this->cache();
	}
}
