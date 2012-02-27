<?php
class news extends baseModel {

	const ERROR_NONEXISTANT = "No news found by that id.";

    var $hasSearch = true;
    var $hasRevisions = true;
    
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
            
            $this->resolveUser($result['user']);
            
            if ($justOne) return $result;
            array_push($toReturn, $result);
        }

        return $toReturn;
    }

    public function validate($title, $department, $text, $tags, $shortNews, $commentable, $creating = true) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $deparment = substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80);
        $body = substr($this->clean($text), 0, 5000);
        $tags = array_map($func, explode(',', $this->clean($tags)));
        
        if (empty($title)) return 'Invalid title.';
        if (empty($body)) return 'Invalid body.';
        
        $entry = array(
            'type' => 'news', 
            'title' => $title, 
            'department' => $department, 
            'body' => $body, 
            'tags' => $tags,
            'user' => $ref, 
            'date' => time(), 
            'shortNews' => (bool) $shortNews, 
            'commentable' => (bool) $commentable, 
            'flaggable' => false, 
            'ghosted' => false
            );
        if (!$creating) unset($entry['user'], $entry['date'], $entry['flaggable']);
            
        return $entry;
    }

    public function generateRevision($update, $old) {
        $titleFD = new FineDiff($update['title'], $old['title']);
        $departmentFD = new FineDiff($update['department'], $old['department']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $revision = array(
            'title' => $titleFD->getOpcodes(),
            'department' => $departmentFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'shortNews' => $old['shortNews'],
            'commentable' => $old['commentable']
            );
        return $revision;
    }
    
}
