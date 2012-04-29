<?php
class comments extends baseModel {
    
    const PAGE_LIMIT = 10;
    
    var $cdata = array('text');
    var $hasSearch = false;
    var $hasRevisions = false;
    var $baseQuery = array('ghosted' => false);
    var $commentableCollections = array('articles', 'bugs', 'lectures', 'news', 'users');
    var $collection = 'comments';
    
    public function get($id, $idlib = false, $justOne = true, $fixUTF8 = true) {
        // $justOne is going to be ignored since we're searching by id.
        $query = $this->baseQuery;$query['_id'] = $this->_toMongoId($id);
        $comment = $this->db->findOne($query);
        
        if (empty($comment)) return 'Invalid id.';
        
        $this->resolveUser($comment['user']);
        if ($fixUTF8) $this->resolveUTF8($comment);
        return $comment;
    }
    
    public function getForId($id, $idlib = false, $justOne = true, $page = 1) {
        $query = $this->baseQuery;$query['contentId'] = $this->clean($id);
        $comments = $this->db->find($query)
            ->skip(($page - 1) * self::PAGE_LIMIT)
            ->sort(array('date' => 1))
            ->limit(self::PAGE_LIMIT);
        
        $comments = iterator_to_array($comments);

        foreach ($comments as $key => $comment) {
            $this->resolveUser($comments[$key]['user']);
            $this->resolveUTF8($comments[$key]);
        }
        
        return ($justOne ? reset($comments) : $comments);
    }
    
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
            $entry['created'], $entry['commentable'], $entry['flagged']);

        return $entry;
    }
    
    public function authChange($type, $comment) {
        return (CheckAcl::can($type . 'AllComment') || (CheckAcl::can($type . 'Comment') && 
            Session::getVar('username') == $comment['user']['username']));
    }
}
