<?php
class articles {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;
    var $mongo;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }

    public function getNewPosts($cache = true) {
        $news = $this->realGetNewPosts();
        return $news;
    }

    public function get($id, $cache = true, $idlib = true) {
        if ($cache && apc_exists('news_' . $id)) return apc_fetch('news_' . $id);

        $news = $this->realGet($id, $idlib);
        if ($cache && !empty($news)) apc_add('news_' . $id, $news, 10);
        return $news;
    }

    public function create($title, $text, $commentable) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));

        $entry = array('type' => 'article', 'title' => htmlentities($title), 'body' => $text, 'user' => $ref, 'date' => time(), 'commentable' => (bool) $commentable, 'ghosted' => false, 'flaggable' => false);
        $this->db->insert($entry);
    }

    public function edit($id, $title, $text, $commentable) {
        $this->db->update(array('_id' => new MongoId($id)), array('$set' => array(
            'title' => htmlentities($title), 'body' => $text, 'commentable' => (bool) $commentable)));
    }

    public function delete($id) {
        $this->db->remove(array('_id' => new MongoId($id)), array('justOne' => true));
        return true;
    }

    public function realGetNewPosts() {
        $posts = $this->db->find(
            array(
                'type' => 'article',
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

    public function realGet($id, $idlib) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('type' => 'article', 'ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => new MongoId($id));
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
