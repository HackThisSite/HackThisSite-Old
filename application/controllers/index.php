<?php
class controller_index extends Controller {
    
    public function index($arguments) {
        $news = new news(ConnectionFactory::get('mongo'));
        $this->view['news'] = $news->getNewPosts();
        Layout::set('title', 'Home');
    }
}
