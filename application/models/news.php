<?php
class News {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";
    
    var $db;
    
    public function __construct() {
        $db    = Config::get(self::KEY_DB);
        $mongo = new Mongo(Config::get(self::KEY_SERVER));

        $this->db = $mongo->$db->content;
    }
    
    public function getNewPosts($cache = true) {
        // temporarilly disabling cache for development - Joseph Moniz
        //if ($cache && apc_exists('top_news')) return apc_fetch('top_news');
        
        $news = $this->realGetNewPosts();
        //if ($cache && !empty($news)) apc_add('top_news', $news, 10);
        return $news;
    }
    
    public function getNews($id, $cache = true) {
        if ($cache && apc_exists('news_' . $id)) return apc_fetch('news_' . $id);
        
        $news = $this->realGetNews($id);
        if ($cache && !empty($news)) apc_add('news_' . $id, $news, 10);
        return $news;
    }
    
    public function saveNews($title, $text, $commentable) {
        $forums = new Forums;
        $data = $forums->loginData();
        $id = $data['id'];
        
        $entry = array('type' => 'news', 'title' => htmlentities($title), 'body' => $text, 'userId' => $id, 'date' => time(), 'commentable' => (bool) $commentable, 'ghosted' => false, 'flaggable' => false);
        $this->db->insert($entry);
    }
    
    public function editNews($id, $title, $text, $commentable) {
        $this->db->update(array('_id' => $id), array('$set' => array(
            'title' => htmlentities($title), 'body' => $text, 'commentable' => (bool) $commentable)));
    }
    
    public function deleteNews($id) {
        $idLib = new Id;
        
        $query = array('type' => 'news', 'ghosted' => false);
        $query = array_merge($idLib->dissectKeys($id, 'news'), $query);
        $results = $this->db->find($query);
        $actual = array();
        
        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('id' => (string) $result['_id'], 'date' => $result['date']), 'news'))
                continue;
            
            $actual = $result;
        }
        
        if (empty($actual)) return false;
        
        $this->db->remove(array('_id' => $actual['_id']), array('justOne' => true));
        return true;
    }
    
    public function realGetNewPosts() {
        return iterator_to_array(
            $this->db->find(
                array(
                    'type' => 'news', 
                    'ghosted' => false
                )
            )->sort(array('date' => -1))
              ->limit(10)
        );
    }
    
    public function realGetNews($id) {
        $idLib = new Id;
        
        $query = array('type' => 'news', 'ghosted' => false);
        $query = array_merge($idLib->dissectKeys($id, 'news'), $query);
        $results = $this->db->find($query);
        
        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('id' => (string) $result['_id'], 'date' => $result['date']), 'news'))
                continue;
            
            return $result;
        }
    }
}
