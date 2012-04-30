<?php
class controller_news extends Content {

    public static $cache = array(
        'view' => array('type' => 'v', 'key' => 'news/view_{REQ}', 'ttl' => 5)
        );
        
    var $name = 'news';
    var $model = 'news';
    var $db = 'mongo';
    var $permission = 'News';
    var $createForms = array('title', '?department', 'text', '?tags', '?shortNews', '?commentable');
    var $location = '';
    var $hasRevisions = true;
    var $diffdFields = array('title', 'department', 'body', '$tags');

    public function view($arguments) {
        @$id = implode('/', $arguments);
        if (empty($id)) return Error::set('Invalid id.');
        $newsModel = new news(ConnectionFactory::get('mongo'));
        $news = $newsModel->get($id);
        
        if (empty($news)) return Error::set('Invalid id.');
        
        $this->view['news'] = $news;
        $this->view['multiple'] = (count($news) > 1);
        
        if (!$this->view['multiple']) Layout::set('title', $this->view['news'][0]['title']);
        /*
        if ($this->view['multiple'] == true) return;

        $mlt = Search::mlt($this->view['news'][0]['_id'], 'news', 'title,body,tags');
        $this->view['mlt'] = array();

        foreach ($mlt['hits']['hits'] as $post) {
            $fetched = $newsModel->get($post['_id'], true, false);
            $fetched = reset($fetched);
            
            if (empty($fetched)) continue;
            array_push($this->view['mlt'], $fetched);
        }
        */
    }
    
}
