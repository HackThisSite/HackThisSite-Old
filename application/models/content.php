<?php
class content extends mongoBase {
    
    var $db;
    
    public function __construct(Mongo $connection) {
        $this->db = $connection->{Config::get('mongo:db')};
    }
    
    public function getById($id) {
        return $this->db->content->findOne(array('_id' => $this->_toMongoId($id)));
    }
    
}
