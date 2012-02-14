<?php
class news extends mongoBase {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";
	
	const ERROR_NONEXISTANT = "No news found by that id.";
	
    var $db;
    var $mongo;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }

    public function getNewPosts($cache = true, $shortNews = false) {
        $news = $this->realGetNewPosts($shortNews);
        return $news;
    }

    public function get($id, $cache = true, $idlib = true) {
        if ($cache && apc_exists('news_' . $id)) return apc_fetch('news_' . $id);

        $news = $this->realGetNews($id, $idlib);
        if ($cache && !empty($news)) apc_add('news_' . $id, $news, 10);
        return $news;
    }

    public function create($title, $department, $text, $shortNews, $commentable) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));

        $entry = array(
            'type' => 'news', 
            'title' => substr($this->clean($title), 0, 100), 
            'department' => substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80), 
            'body' => substr($this->clean($text), 0, 5000), 
            'user' => $ref, 
            'date' => time(), 
            'shortNews' => (bool) $shortNews, 
            'commentable' => (bool) $commentable, 
            'ghosted' => false, 
            'flaggable' => false
            );
            
        $this->db->insert($entry);
    }

    public function edit($id, $title, $department, $text, $shortNews, $commentable) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array(
            '$set' => array(
                'title' => substr($this->clean($title), 0, 100), 
                'department' => substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80),
                'body' => substr($this->clean($text), 0, 5000), 
                'shortNews' => (bool) $shortNews, 
                'commentable' => (bool) $commentable
                )
            ));
    }

    public function delete($id) {
        $entry = $this->db->findOne(array('_id' => $this->_toMongoId($id)));
        if (empty($entry))
            return self::ERROR_NONEXISTANT;
        
        return $this->db->update(array('_id' => $this->_toMongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }

    public function realGetNewPosts($shortNews) {
        $posts = $this->db->find(
            array(
                'type' => 'news',
                'shortNews' => false,
                'ghosted' => false
            )
        )->sort(array('date' => -1))
         ->limit(10);
         $posts = iterator_to_array($posts);

         foreach ($posts as $key => $post) {
             $posts[$key]['user'] = MongoDBRef::get($this->mongo, $post['user']);
         }
         
         return $posts;
    }

    public function realGetNews($id, $idlib) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('type' => 'news', 'ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => $this->_toMongoId($id), 'type' => 'news');
        }

        $results = $this->db->find($query);
        
        if (!$idlib)
            return iterator_to_array($results);
            
        $toReturn = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('ambiguity' => $keys['ambiguity'],
                'reportedDate' => $keys['date'], 'date' => $result['date'],
                'title' => $result['title']), 'news'))
                continue;

            $result['user'] = MongoDBRef::get($this->mongo, $result['user']);
            array_push($toReturn, $result);
        }

        return $toReturn;
    }
}
