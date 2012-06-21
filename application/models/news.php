<?php
/**
 * News
 * 
 * @package Model
 */
class news extends baseModel {

    var $cdata = array('title', 'department', 'body', '@tags');
    var $hasSearch = true;
    var $hasRevisions = true;
    var $collection = 'news';
    var $type = 'news';
    
    const PER_PAGE = 10;

    /**
     * Get new news posts.
     * 
     * @param bool $shortNews True if you're getting short news.
     * 
     * @return array The new news posts as an array.
     */
    protected function getNewPosts($shortNews = false) {
        $posts = $this->db->find(
            array(
                'shortNews' => $shortNews,
                'ghosted' => false
            )
        )->sort(array('date' => -1))
         ->limit(($shortNews ? 5 : 10));
         $posts = iterator_to_array($posts);

        $comments = new comments(ConnectionFactory::get('mongo'));
        
         foreach ($posts as $key => $post) {
             $this->resolveUser($posts[$key]['user']);
             $this->resolveUTF8($posts[$key]);
             $posts[$key]['comments'] = $comments->getCount($post['_id']);
         }
         
         return $posts;
    }

    /**
     * Get a news post.
     * 
     * @param string $id The news id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The news post as an array, or an error string.
     */
    protected function get($id, $idlib = true, $justOne = false, $fixUTF8 = true, $limit = self::PER_PAGE) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => $this->_toMongoId($id));
        }

        $results = $this->db->find($query)->limit($limit)->sort(array('date' => -1));
        
        if (empty($results)) return 'Invalid id.';
        $comments = new comments(ConnectionFactory::get('mongo'));
        
        if (!$idlib) {
            $toReturn = iterator_to_array($results);
            
            foreach ($toReturn as $key => $entry) {
                $this->resolveUser($toReturn[$key]['user']);
                if ($fixUTF8) $this->resolveUTF8($toReturn[$key]);
                $toReturn[$key]['comments'] = $comments->getCount($entry['_id']);
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
            if ($fixUTF8) $this->resolveUTF8($result);
            $result['comments'] = $comments->getCount($result['_id']);
            
            if ($justOne) return $result;
            array_push($toReturn, $result);
        }

        return $toReturn;
    }

    // Content management magic.
    public function validate($title, $department, $text, $tags, $shortNews, $commentable, $creating = true, $preview = false) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $department = substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80);
        $body = substr($this->clean($text), 0, 5000);
        if (is_array($tags)) $tags = implode(',', $tags);
        $tags = array_map($func, explode(',', $this->clean($tags)));
        
        if (empty($title)) return 'Invalid title.';
        if (empty($body)) return 'Invalid body.';
        
        $entry = array( 
            'title' => $title, 
            'department' => $department, 
            'body' => $body, 
            'tags' => $tags,
            'user' => $ref, 
            'date' => time(), 
            'shortNews' => (bool) $shortNews, 
            'commentable' => (bool) $commentable, 
            'ghosted' => false
            );
        if (!$creating) unset($entry['user'], $entry['date'], $entry['flaggable']);
        if (!$preview) { self::ApcPurge('getNewPosts', null); }
        return $entry;
    }

    // Content management magic.
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
