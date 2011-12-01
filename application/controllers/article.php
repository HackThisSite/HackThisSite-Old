<?php
class controller_article extends Controller {
    
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
	}
    
    public function post($arguments) {
        if (!CheckAcl::can('postArticle'))
            return Error::set('You can not post articles!');
            
        $this->view['valid'] = true;
        
        if (!empty($arguments[0]) && $arguments[0] == 'save') {
            if (empty($_POST['title']) || empty($_POST['text']))
                return Error::set('All forms need to be filled out.');
                
            $articles = new articles(ConnectionFactory::get('mongo'));
            $articles->create($_POST['title'], $_POST['text'], 
                (!empty($_POST['commentable']) ? $_POST['commentable'] : false));
                
            Error::set('Entry posted!', true);
            header('Location: ' . Url::format('/'));
        }
    }
    
    public function edit($arguments) {
        if (!CheckAcl::can('editArticle'))
            return Error::set('You can not edit articles!');
        if (empty($arguments) || empty($arguments[0]))
            return Error::set('Article ID is required.');
        
        $articles = new articles(ConnectionFactory::get('mongo'));
        $post = $articles->get($arguments[0], false, false);
        
        if (empty($post))
            return Error::set('Invalid article ID.');
        
        $this->view['valid'] = true;
        $this->view['post'] = $post;
        
        if (!empty($arguments[1]) && $arguments[1] == 'save') {
            if (empty($_POST['title']) || empty($_POST['text']))
                return Error::set('All forms need to be filled out.');
                
            $articles = new articles(ConnectionFactory::get('mongo'));
            $articles->edit($arguments[0], $_POST['title'], $_POST['text'],
                (!empty($_POST['commentable']) ? $_POST['commentable'] : false));
            
            $this->view['post'] = $articles->get($arguments[0], false, false);
            Error::set('Entry edited!', true);
        }
    }
    
    public function delete($arguments) {
        if (!CheckAcl::can('deleteArticle'))
            return Error::set('You can not delete articles!');
        if (empty($arguments) || empty($arguments[0]))
            return Error::set('Article ID is required.');
        
        $articles = new articles(ConnectionFactory::get('mongo'));
        $post = $articles->get($arguments[0], false, false);
        
        if (empty($post))
            return Error::set('Invalid article ID.');
        
        $articles->delete($arguments[0]);
        Error::set('Entry deleted.', true);
        
        header('Location: ' . Url::format('/'));
    }
}
