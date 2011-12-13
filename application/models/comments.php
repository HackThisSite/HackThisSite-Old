<?php
class comments extends mongoBase {
    
    var $db;
    
    public function __construct($connection) {
        $this->db = $connection->{Config::get('mongo:db')};
    }
    
    public function create($id, $text) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        return $this->db->content->insert(array('type' => 'comment', 'contentId' => (string) $id, 
            'ghosted' => false, 'user' => $ref, 'text' => $this->clean($text), 'date' => time()));
    }
    
    public function get($id, $page) {
        $pageLimit = 10;
        $comments = $this->db->content->find(array('type' => 'comment', 'contentId' => $this->clean($id), 'ghosted' => false),
        array('user' => 1, 'date' => 1, 'text' => 1))->skip(($page - 1) * $pageLimit)->limit($pageLimit);
        $comments = iterator_to_array($comments);
        
        foreach ($comments as $key => $comment) {
            $comments[$key]['user'] = MongoDBRef::get($this->db, $comment['user']);
        }
        
        return $comments;
    }
    
    public function getById($id) {
        $comment = $this->db->content->findOne(array('type' => 'comment', '_id' => $this->_toMongoId($id)));
        $comment['user'] = MongoDBRef::get($this->db, $comment['user']);
        return $comment;
    }
    
    public function delete($id) {
        return $this->db->content->update(array('type' => 'comment', '_id' => $this->_toMongoId($id)), array('$set' => array('ghosted' => true)));
    }
}
