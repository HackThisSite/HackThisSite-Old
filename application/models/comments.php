<?php
/**
 * Comments
 * 
 * @package Model
 */
class comments extends baseModel {
    
    const PAGE_LIMIT = 10;
    
    var $cdata = array('text');
    var $hasSearch = false;
    var $hasRevisions = false;
    var $baseQuery = array('ghosted' => false);
    var $commentableCollections = array('articles', 'bugs', 'lectures', 'news', 'users');
    var $collection = 'comments';
    var $type = 'comment';
    
    /**
     * Gets a comment
     * 
     * @param string $id The comment id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The comment as an array, or an error string.
     */
    protected function get($id, $idlib = false, $justOne = true, $fixUTF8 = true) {
        // $justOne is going to be ignored since we're searching by id.
        $query = $this->baseQuery;$query['_id'] = $this->_toMongoId($id);
        $comment = $this->db->findOne($query);
        
        if (empty($comment)) return 'Invalid id.';
        
        $this->resolveUser($comment['user']);
        if ($fixUTF8) $this->resolveUTF8($comment);
        return $comment;
    }
    
    /**
     * Get comments for a content id.
     * 
     * @param string $id The content id to use.
     * @param bool $idlib If the Id library should be used.
     * @param bool $justOne If only one entry should be returned.
     * @param int $page The page to get comments for.
     * 
     * @return mixed The comment/comments as an array, or an error string.
     */
    protected function getForId($id, $idlib = false, $justOne = true, $page = 1) {
        $query = $this->baseQuery;$query['contentId'] = $this->clean($id);
        $comments = $this->db->find($query)
            ->skip(($page - 1) * self::PAGE_LIMIT)
            ->sort(array('date' => 1))
            ->limit(self::PAGE_LIMIT);
        
        if (empty($comments)) return 'Invalid id.';
        
        $comments = iterator_to_array($comments);

        foreach ($comments as $key => $comment) {
            $this->resolveUser($comments[$key]['user']);
            $this->resolveUTF8($comments[$key]);
        }
        
        return ($justOne ? reset($comments) : $comments);
    }
    
    /**
     * Gets the number of comments for a piece of content.
     * 
     * @param string $contentId The content id.
     * 
     * @return int The number of comments.
     */
    protected function getCount($contentId) {
        return (int) $this->db->count(array('contentId' => (string) $contentId));
    }
    
    // Content management magic.
    public function validate($contentId, $text, $creating = true) {
        $valid = false;
        foreach ($this->commentableCollections as $collection) {
            $ref = MongoDBRef::create($collection, $this->_toMongoId($contentId));
            $result = MongoDBRef::get($this->mongo, $ref);
            
            if ($result != null) {
                $valid = true;
                break;
            }
        }
        
        if (!$valid) return 'Invalid content id.';
        
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $text = substr($this->clean(preg_replace('/\s{6,}/', "\n\n", preg_replace('/[[:blank:]]+/', ' ', $text))), 0, 1000);
        
        if (empty($text)) return 'Invalid comment.';
        
        $entry = array(
            'contentId' => (string) $contentId, 
            'date' => time(),
            'text' => $text, 
            'user' => $ref,
            'ghosted' => false,
            );
        if (!$creating) unset($entry['reporter'], $entry['status'], 
            $entry['created'], $entry['commentable'], $entry['user']);

        return $entry;
    }
    
    // Content management magic.
    public function authChange($type, $comment) {
        return (CheckAcl::can($type . 'AllComment') || (CheckAcl::can($type . 'Comment') && 
            Session::getVar('username') == $comment['user']['username']));
    }
}
