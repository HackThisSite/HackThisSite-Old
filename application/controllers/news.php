<?php
class controller_news extends Content {

    var $name = 'news';
    var $model = 'news';
    var $db = 'mongo';
    var $permission = 'News';
    var $createForms = array('title', 'text', '?commentable');
	var $location = '';

    public function view($arguments) {
		@$id = implode('/', $arguments);
		if (empty($id)) return Error::set('Invalid id.');
		$newsModel = new news(ConnectionFactory::get('mongo'));
		$news = $newsModel->get($id);
		
		if (empty($news)) return Error::set('Invalid id.');
		
		$this->view['news'] = $news;
		$this->view['multiple'] = (count($news) > 1);
	}
    
}
