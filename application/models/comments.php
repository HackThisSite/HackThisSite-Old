<?php
class comments extends baseModel {
    
    const PAGE_LIMIT = 10;
    
    var $hasSearch = false;
    var $hasRevisions = false;
    var $baseQuery = array('type' => 'comment', 'ghosted' => false);
    
    public function get($id, $idlib = false, $justOne = true) {
        // $justOne is going to be ignored since we're searching by id.
        $query = $this->baseQuery;$query['_id'] = $this->_toMongoId($id);
        $comment = $this->db->findOne($query);
        
        $this->resolveUser($comment['user']);
        return $comment;
    }
    
    public function getForId($id, $idlib = false, $justOne = true, $page = 1) {
        $query = $this->baseQuery;$query['contentId'] = $this->clean($id);
        $comments = $this->db->find($query)
            ->skip(($page - 1) * self::PAGE_LIMIT)
            ->limit(self::PAGE_LIMIT);
        
        $comments = iterator_to_array($comments);

        foreach ($comments as $key => $comment) {
            $this->resolveUser($comments[$key]['user']);
        }
        
        return ($justOne ? reset($comments) : $comments);
    }
    
    public function validate($contentId, $text, $creating = true) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $text = substr($this->clean($text), 0, 1000);
        
        if (empty($text)) return 'Invalid comment.';
        
        $entry = array(
            'type' => 'comment', 
            'contentId' => (string) $contentId, 
            'date' => time(),
            'text' => $this->clean($text), 
            'user' => $ref,
            'ghosted' => false,
            );
        if (!$creating) unset($entry['reporter'], $entry['status'], 
            $entry['created'], $entry['commentable'], $entry['flagged']);

        return $entry;
    }
    
    public function authChange($type, $comment) {
        return (CheckAcl::can($type . 'AllComment') || (CheckAcl::can($type . 'Comment') && 
            Session::getVar('username') == $comment['user']['username']));
    }
}
