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

    public function getNewPosts($shortNews = false) {
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
             $this->resolveUser($posts[$key]['user']);
         }
         
         return $posts;
    }

    public function get($id, $idlib = true, $justOne = false) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('type' => 'news', 'ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => $this->_toMongoId($id), 'type' => 'news');
        }

        $results = $this->db->find($query);
        
        if (!$idlib) {
            $toReturn = iterator_to_array($results);
            
            foreach ($toReturn as $key => $entry) {
                $this->resolveUser($toReturn[$key]['user']);
            }
            
            return ($justOne ? reset($toReturn) : $toReturn);
        }
            
        $toReturn = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('ambiguity' => $keys['ambiguity'],
                'reportedDate' => $keys['date'], 'date' => $result['date'],
                'title' => $result['title']), 'news'))
                continue;

            if (is_string($result['user'])) {
                $result['user'] = array('username' => $result['user']);
            } else {
                $result['user'] = MongoDBRef::get($this->mongo, $result['user']);
            }
            
            $this->resolveUser($return['user']);
            array_push($toReturn, $result);
        }

        return ($justOne ? reset($toReturn) : $toReturn);
    }

    public function create($title, $department, $text, $tags, $shortNews, $commentable) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));

        $func = function($value) { return trim($value); };

        $entry = array(
            'type' => 'news', 
            'title' => substr($this->clean($title), 0, 100), 
            'department' => substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80), 
            'body' => substr($this->clean($text), 0, 5000), 
            'tags' => array_map($func, explode(',', $this->clean($tags))),
            'user' => $ref, 
            'date' => time(), 
            'shortNews' => (bool) $shortNews, 
            'commentable' => (bool) $commentable, 
            'ghosted' => false, 
            'flaggable' => false
            );
            
        $this->db->insert($entry);
        
        $id = $entry['_id'];
        unset($entry['_id'], $entry['user'], $entry['date'], $entry['shortNews'], 
            $entry['commentable'], $entry['flaggable']);
        Search::index($id, $entry);
    }

    public function edit($id, $title, $department, $text, $tags, $shortNews, $commentable) {
        $func = function($value) { return trim($value); };
        
        $old = $this->get($id, false, false);
        $old = reset($old);
        
        $update = array(
                'type' => 'news',
                'title' => substr($this->clean($title), 0, 100), 
                'department' => substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80),
                'body' => substr($this->clean($text), 0, 5000), 
                'tags' => array_map($func, explode(',', $this->clean($tags))),
                'shortNews' => (bool) $shortNews, 
                'commentable' => (bool) $commentable,
                'ghosted' => false
                );
        
        $titleFD = new FineDiff($update['title'], $old['title']);
        $departmentFD = new FineDiff($update['department'], $old['department']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $diff = array(
            'contentId' => (string) $id,
            'title' => $titleFD->getOpcodes(),
            'department' => $departmentFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'shortNews' => $old['shortNews'],
            'commentable' => $old['commentable']
            );
        
        $this->db->update(array('_id' => $this->_toMongoId($id)), array(
            '$set' => $update
            ));
        $this->mongo->revisions->insert($diff);
        
        unset($update['_id'], $update['shortNews'], $update['commentable']);
        Search::index($id, $update);
    }

    public function delete($id) {
        $entry = $this->db->findOne(array('_id' => $this->_toMongoId($id)));
        if (empty($entry))
            return self::ERROR_NONEXISTANT;
        
        Search::delete($id);
        return $this->db->update(array('_id' => $this->_toMongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }
    
    private function resolveUser(&$user) {
        if (is_string($user)) {
            $user = array('username' => $user);
        } else {
            $user = MongoDBRef::get($this->mongo, $user);
        }
    }
}
