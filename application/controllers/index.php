<?php
class controller_index extends Controller {
    
    public static $cache = array(
		'index' => array('type' => 'v', 'key' => 'index_{SI}', 'ttl' => 5)
		);
	
    public function index($arguments) {
        //$news = new news(ConnectionFactory::get('mongo'));
        //$notices = new notices(ConnectionFactory::get('redis'));
        $this->view['news'] = array();//$news->getNewPosts();
        $this->view['notices'] = array();//$notices->getAll();
        Layout::set('title', 'Home');
    }
    
}
