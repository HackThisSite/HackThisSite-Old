<?php
class controller_index extends Controller {
    
    public static $cache = array(
		'index' => array('type' => 'v', 'key' => 'index_{SI}', 'ttl' => 5)
		);
	
    public function index($arguments) {
        $news = new news(ConnectionFactory::get('mongo'));
        $notices = new notices(ConnectionFactory::get('redis'));
        
        foreach ($notices->getAll() as $notice) {
			Error::set($notice, true);
		}
		
        $this->view['news'] = $news->getNewPosts();
        
        Layout::set('title', 'Home');
        
        $apc = new APCIterator('user', '/user_.*/');
        $this->view['onlineUsers'] = array();
        
        
        while ($apc->valid()) {
			$current = $apc->current();
			array_push($this->view['onlineUsers'], substr($current['key'], 5));
			$apc->next();
		}
		
		$irc = new irc(ConnectionFactory::get('redis'));
		$this->view['ircOnline'] = $irc->getOnline();
    }
    
}
