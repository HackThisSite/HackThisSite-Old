<?php
class controller_article extends Content {
    
    var $name = 'article';
    var $model = 'articles';
    var $db = 'mongo';
    var $permission = 'Article';
    var $createForms = array('title', 'text', '?tags');
	var $location = 'article';
    var $hasRevisions = true;
    var $diffdFields = array('title', 'body', '$tags');
	
    public function index($arguments) {
        $articles = new articles(ConnectionFactory::get('mongo'));
        $this->view['articles'] = $articles->getNewPosts();
    }
    
	public function view($arguments) {
		@$id = implode('/', $arguments);
		if (empty($id)) return Error::set('Invalid id.');
		$articlesModel = new articles(ConnectionFactory::get('mongo'));
		$article = $articlesModel->get($id);
		
		if (empty($article)) return Error::set('Invalid id.');
		
		$this->view['article'] = $article;
		$this->view['multiple'] = (count($article) > 1);
        
        if ($this->view['multiple'] == true) return;
        
        $mlt = Search::mlt($this->view['article'][0]['_id'], 'article', 'title,body,tags');
        $this->view['mlt'] = array();

        foreach ($mlt['hits']['hits'] as $article) {
            $fetched = $articlesModel->get($article['_id'], true, false);
            $fetched = reset($fetched);
            
            if (empty($fetched)) continue;
            array_push($this->view['mlt'], $fetched);
        }
	}
    
    public function approve($arguments) {
        if (!CheckAcl::can('approveArticles'))
            return Error::set('You can not approve articles!');
        
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
    
}
