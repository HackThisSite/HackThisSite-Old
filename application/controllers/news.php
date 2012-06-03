<?php
class controller_news extends Content {
        
    var $name = 'news';
    var $model = 'news';
    var $db = 'mongo';
    var $permission = 'News';
    var $createForms = array('title', '?department', 'text', '?tags', '?shortNews', '?commentable');
    var $location = '';
    var $hasRevisions = true;
    var $diffdFields = array('title', 'department', 'body', '$tags');

    public function view($arguments) {
        if (!empty($arguments[3])) {
            $page = (int) array_pop($arguments);
            if ($page < 1) {
                $this->view['commentPage'] = 1;
            } else {
                $this->view['commentPage'] = $page;
            }
        } else {
            $this->view['commentPage'] = 1;
        }
        
        @$id = implode('/', $arguments);
        if (empty($id)) return Error::set('Invalid id.');
        $newsModel = new news(ConnectionFactory::get('mongo'));
        $news = $newsModel->get($id);
        
        if (empty($news)) return Error::set('Invalid id.');
        
        $this->view['news'] = $news;
        $this->view['multiple'] = (count($news) > 1);
        
        if ($this->view['multiple']) return;

        $this->view['commentPageLoc'] = 'news/view/' . $id . '/';
        Layout::set('title', $this->view['news'][0]['title']);
        $mlt = Search::mlt($this->view['news'][0]['_id'], 'news', 'title,body,tags');
        $this->view['mlt'] = array();

        if (empty($mlt['hits']['hits'])) return;
        foreach ($mlt['hits']['hits'] as $post) {
            $fetched = $newsModel->get($post['_id'], false, true);
            
            if (empty($fetched)) continue;
            array_push($this->view['mlt'], $fetched);
        }
    }
    
}
