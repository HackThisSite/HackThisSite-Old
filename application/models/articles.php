<?php
class articles extends mongoBase {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;
    var $mongo;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }
    
    private function UserInfo($record, $single = false) {
        if (empty($record))
            return $record;
        
        if ($single) {
            if (is_string($record['user'])) return $record;
            $record['user'] = MongoDBRef::get($this->mongo, $this->clean($record['user']));
        } else {
            foreach ($record as $key => $entry) {
                if (is_string($entry['user'])) continue;
                $record[$key]['user'] = MongoDBRef::get($this->mongo, $this->clean($entry['user']));
            }
        }
        
        return $record;
    }

    public function getNewPosts() {
        $posts = $this->db->find(
            array(
                'type' => 'article',
                'ghosted' => false,
                'published' => true
            )
        )->sort(array('date' => -1))
         ->limit(10);
         $posts = iterator_to_array($posts);
         $posts = $this->UserInfo($posts);
         
         return $posts;
    }

    public function get($id, $idlib = true, $justOne = false) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('type' => 'article', 'ghosted' => false, 'published' => true);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => $this->_toMongoId($id), 'type' => 'article', 'published' => true, 'ghosted' => false);
        }

        $results = $this->db->find($query);

        if ($results->count() == 0) return 'Invalid id.';
        if (!$idlib) {
            $array = iterator_to_array($results);
            return ($justOne ? reset($array) : $array);
        }
        
        $toReturn = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('ambiguity' => $keys['ambiguity'],
                'reportedDate' => $keys['date'], 'date' => $result['date'],
                'title' => $result['title']), 'news'))
                continue;

            $result = $this->UserInfo($result, true);
            array_push($toReturn, $result);
        }

        return $toReturn;
    }
    
    public function getNextUnapproved() {
        $record = $this->db->findOne(array('published' => false, 'ghosted' => false));
        $record = $this->UserInfo($record, true);
        return $record;
    }

    public function create($title, $text, $tags) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        
        $func = function($value) { return trim($value); };
        
        $entry = array(
            'type' => 'article', 
            'title' => substr($this->clean($title), 0, 100), 
            'body' => substr($this->clean($text), 0, 3500), 
            'tags' => array_map($func, explode(',', $this->clean($tags))),
            'user' => $ref, 
            'date' => time(), 
            'commentable' => true, 
            'published' => false, 
            'ghosted' => false, 
            'flaggable' => false
            );
        $this->db->insert($entry);
        
        $id = $entry['_id'];
        unset($entry['_id'], $entry['user'], $entry['date'], $entry['commentable'],
            $entry['published'], $entry['flaggable']);
        Search::index($id, $entry);
    }

    public function edit($id, $title, $text, $tags) {
        $func = function($value) { return trim($value); };
        
        $old = $this->get($id, false, false);
        $old = reset($old);
        
        $update = array(
            'type' => 'article',
            'title' => substr($this->clean($title), 0, 100), 
            'body' => substr($this->clean($text), 0, 4000000), 
            'tags' => array_map($func, explode(',', $this->clean($tags))),
            'ghosted' => false
            );
        
        $titleFD = new FineDiff($update['title'], $old['title']);
        $textFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $diff = array(
            'contentId' => (string) $id,
            'title' => $titleFD->getOpcodes(),
            'body' => $textFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            );
        
        $this->db->update(array('_id' => new MongoId($id)), array('$set' => $update));
        $this->mongo->revisions->insert($diff);
        
        unset($update['_id'], $update['commentable']);
        Search::index($id, $update);
    }

    public function delete($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => array('ghosted' => true)));
        Search::delete($id);
        return true;
    }

    public function approve($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => array('published' => true)));
        return true;
    }
}
