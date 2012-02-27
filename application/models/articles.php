<?php
class articles extends baseModel {

    var $hasSearch = true;
    var $hasRevisions = true;
    
    public function getNewPosts() {
        $posts = $this->db->find(
            array(
                'type' => 'article',
                'ghosted' => false,
                'published' => true
            )
        )->sort(array('date' => -1))
         ->limit(10);
         
         $toReturn = array();
         
         foreach ($posts as $post) {
            $this->resolveUser($post['user']);
            array_push($toReturn, $post);
        }
         
         return $toReturn;
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
            
            $this->resolveUser($result['user']);
            
            if ($justOne) return $result;
            array_push($toReturn, $result);
        }

        return $toReturn;
    }
    
    public function getNextUnapproved() {
        $record = $this->db->findOne(array('published' => false, 'ghosted' => false));
        if (!empty($record)) $this->resolveUser($record['user']);
        return $record;
    }

    public function validate($title, $text, $tags, $creating = true) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $body = substr($this->clean($text), 0, 3500);
        $tags = array_map($func, explode(',', $this->clean($tags)));
        
        if (empty($title)) return 'Invalid title.';
        if (empty($body)) return 'Invalid body.';
        
        $entry = array(
            'type' => 'article', 
            'title' => $title, 
            'body' => $body, 
            'tags' => $tags,
            'user' => $ref, 
            'date' => time(), 
            'commentable' => true, 
            'published' => false, 
            'ghosted' => false, 
            'flaggable' => false
            );
        if (!$creating) unset($entry['user'], $entry['date'], 
            $entry['commentable'], $entry['published'], $entry['flaggable']);
        
        return $entry;
    }
    
    public function generateRevision($update, $old) {
        $titleFD = new FineDiff($update['title'], $old['title']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $revision = array(
            'title' => $titleFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'published' => $old['published'],
            'flaggable' => $old['flaggable'],
            'commentable' => $old['commentable']
            );
        return $revision;
    }

    public function approve($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => array('published' => true)));
        return true;
    }
}
