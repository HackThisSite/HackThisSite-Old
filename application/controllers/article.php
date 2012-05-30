<?php
class controller_article extends Content {
    
    var $name = 'article';
    var $model = 'articles';
    var $db = 'mongo';
    var $permission = 'Article';
    var $createForms = array('title', 'category', 'description', 'text', '?tags');
    var $location = 'article';
    var $hasRevisions = true;
    var $diffdFields = array('title', 'category' ,'description', 'body', '$tags');
    
    public function index($arguments) {
        $articles = new articles(ConnectionFactory::get('mongo'));
        
        $category = 'new';
        $page = 1;
        
        if (!empty($arguments[0])) $category = $arguments[0];
        if (!empty($arguments[1])) $page = $arguments[1];
        
        $this->view = $articles->getNewPosts($category, $page);
        
        Layout::set('title', 'Articles');
        
        if (empty($this->view['articles'])) Error::set('There are no articles!');
    }
    
    public function view($arguments) {
        @$id = implode('/', $arguments);
        if (empty($id)) return Error::set('Invalid id.');
        $articlesModel = new articles(ConnectionFactory::get('mongo'));
        $article = $articlesModel->get($id);
        
        if (empty($article)) return Error::set('Invalid id.');
        
        $this->view['article'] = $article;
        $this->view['multiple'] = (count($article) > 1);
        
        if (!$this->view['multiple']) {
            Layout::set('title', $this->view['article'][0]['title']);
        } else return;
        
        $mlt = Search::mlt($this->view['article'][0]['_id'], 'article', 'title,body,tags');
        
        $this->view['mlt'] = array();
        
        if (empty($mlt['hits']['hits'])) return;
        foreach ($mlt['hits']['hits'] as $article) {
            $fetched = $articlesModel->get($article['_id'], false, true);
            
            if (empty($fetched)) continue;
            array_push($this->view['mlt'], $fetched);
        }
    }
    
    public function approve($arguments) {
        if (!CheckAcl::can('approveArticles'))
            return Error::set('You can not approve articles!');
        
        Layout::set('title', 'Unapproved Articles');
        
        $articles = new articles(ConnectionFactory::get('mongo'));
        $unapproved = $articles->getNextUnapproved();

        if (empty($unapproved))
            return Error::set('No unapproved articles.', true);
        
        if (!empty($arguments[0]) && $arguments[0] == 'save' && !empty($_POST['decision'])) {
            if ($_POST['decision'] ==  'Publish') {
                $articles->approve($unapproved['_id']);
                Error::set('Article approved.', true);
            } else if ($_POST['decision'] == 'Delete') {
                $articles->delete($unapproved['_id']);
                Error::set('Article deleted.', true);
            }
            
            $unapproved = $articles->getNextUnapproved();
            
            if (empty($unapproved))
                return Error::set('No unapproved articles left.', true);
        }
        
        $this->view['article'] = $unapproved;
    }
    
    public function vote($arguments) {
        if (!CheckAcl::can('voteOnArticles')) 
            return Error::set('You can not vote on articles.');
        if (empty($arguments[0]) || empty($arguments[1]))
            return Error::set('Vote or article id not found.');
        
        $articles = new articles(ConnectionFactory::get('mongo'));
        $result = $articles->castVote($arguments[0], $arguments[1]);
        
        if (is_string($result)) return Error::set($result);
        Error::set('Vote cast!', true);
    }
    
}
