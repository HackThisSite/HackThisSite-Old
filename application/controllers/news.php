<?php
class controller_news extends Content {
        
    var $name = 'news';
    var $model = 'news';
    var $db = 'mongo';
    var $permission = 'News';
    var $createForms = array('title', '?department', 'body', '?tags', '?shortNews', '?commentable');
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
        list($news, $total) = $newsModel->get($id);
        
        if (is_string($news)) return Error::set($news);
        
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
    
    public function vote($arguments) {
        if (!CheckAcl::can('voteOnNews')) 
            return Error::set('You can not vote on news posts.');
        if (empty($arguments[0]) || empty($arguments[1]))
            return Error::set('Vote or news id not found.');
        
        $news = new news(ConnectionFactory::get('mongo'));
        $result = $news->castVote($arguments[0], $arguments[1]);
        $post = $news->get($arguments[0], false, true);
        
        if (is_string($result)) return Error::set($result, false, array('Back' => Url::format('/news/view/' . Id::create($post, 'news'))));
        Error::set('Vote cast!', true, array('Back' => Url::format('/news/view/' . Id::create($post, 'news'))));
    }
    
}
