<?php
class controller_index extends Controller {
    
    public static $cache = array(
        'index' => array('type' => 'v', 'key' => 'index_{SI}', 'ttl' => 5)
        );
    
    public function index($arguments) {
        $news = new news(ConnectionFactory::get('mongo'));
        $articles = new articles(ConnectionFactory::get('mongo'));
        $notices = new notices(ConnectionFactory::get('redis'));
        $irc = new irc(ConnectionFactory::get('redis'));
        
        // Set all site-wide notices.
        foreach ($notices->getAll() as $notice) {
            Error::set($notice, true);
        }
        
        // Fetch the easy data.
        $this->view['news'] = $news->getNewPosts();
        $this->view['shortNews'] = $news->getNewPosts(true);
        $this->view['newArticles'] = $articles->getNewPosts('new', 1, 5);
        $this->view['ircOnline'] = $irc->getOnline();
        
        // Get online users.
        $apc = new APCIterator('user', '/user_.*/');
        $this->view['onlineUsers'] = array();
        
        while ($apc->valid()) {
            $current = $apc->current();
            array_push($this->view['onlineUsers'], substr($current['key'], 5));
            $apc->next();
        }
        
        // Set title.
        Layout::set('title', 'Home');
    }
    
}
