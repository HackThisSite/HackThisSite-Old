<?php
class controller_article extends Content {
    
    var $name = 'article';
    var $model = 'articles';
    var $db = 'mongo';
    var $permission = 'Article';
    var $createForms = array('title', 'category', 'description', 'body', '?tags');
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
        
        if (!empty($_GET['p'])) {
            $page = (int) $_GET['p'];
            if ($page < 1) {
                $this->view['page'] = 1;
            } else {
                $this->view['page'] = $page;
            }
        } else {
            $this->view['page'] = 1;
        }
        
        @$id = implode('/', $arguments);
        if (empty($id)) return Error::set('Invalid id.');
        $articlesModel = new articles(ConnectionFactory::get('mongo'));
        list($article, $this->view['total']) = $articlesModel->get($id, true, false, true, $this->view['page']);
        
        if (is_string($article)) return Error::set($article);
        
        $this->view['article'] = $article;
        $this->view['multiple'] = (count($article) > 1);
        $this->view['url'] = Url::format('/article/view/' . $id . '?p=');
        
        if ($this->view['multiple']) return;
        
        $this->view['commentPageLoc'] = 'article/view/' . $id . '/';
        Layout::set('title', $this->view['article'][0]['title']);
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
        $post = $articles->get($arguments[0], false, true);
        
        if (is_string($result)) return Error::set($result, false, array('Back' => Url::format('/article/view/' . Id::create($post, 'news'))));
        Error::set('Vote cast!', true, array('Back' => Url::format('/article/view/' . Id::create($post, 'news'))));
    }
    
}
