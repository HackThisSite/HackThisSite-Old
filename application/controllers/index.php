<?php
class controller_index extends Controller {
    
    public function index($arguments) {
        $news = new news(ConnectionFactory::get('mongo'));
        $articles = new articles(ConnectionFactory::get('mongo'));
        $notices = new notices(ConnectionFactory::get('redis'));
        $irc = new irc(ConnectionFactory::get('redis'));
        $quotes = new quotes(ConnectionFactory::get('mongo'));
        
        // Set all site-wide notices.
        foreach ($notices->getAll() as $notice) {
            Error::set($notice, true);
        }
        
        // Fetch the easy data.
        $this->view['news'] = $news->getNewPosts();
        $this->view['shortNews'] = $news->getNewPosts(true);
        $this->view['newArticles'] = $articles->getNewPosts('new', 1, 5);
        $this->view['ircOnline'] = $irc->getOnline();
        $this->view['randomQuote'] = $quotes->getRandom();
        
        // Get online users.
        $apc = new APCIterator('user', '/' . Cache::PREFIX . 'Session_user_.*/');
        $this->view['onlineUsers'] = array();
        
        while ($apc->valid()) {
            $current = $apc->current();
            array_push($this->view['onlineUsers'], substr($current['key'], strlen(Cache::PREFIX . 'Session_') + 5));
            $apc->next();
        }
        
        // Set title.
        Layout::set('title', 'Home');
    }
    
}
