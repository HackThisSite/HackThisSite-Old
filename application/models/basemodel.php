<?php
class baseModel extends mongoBase {

    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;
    var $mongo;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }
    
    public function create() {
        $entry = call_user_func_array(array($this, 'validate'), func_get_args());
        if (is_string($entry)) return $entry;
        
        $this->db->insert($entry);
        if ($this->hasSearch) $this->searchIndex($entry['_id'], func_get_args(), true);
        return true;
    }
    
    public function edit() {
        $args = func_get_args();
        $id = array_shift($args);
        $args[] = false;
        
        $entry = call_user_func_array(array($this, 'validate'), $args);
        if (is_string($entry)) return $entry;
        
        if ($this->hasRevisions) {
            $old = $this->get($id, false, true);
            $revision = $this->generateRevision($entry, $old);
            $revision['contentId'] = (string) $id;
        }
        
        $this->db->update(array(
            '_id' => $this->_toMongoId($id)), 
            array('$set' => $entry)
            );
        if ($this->hasRevisions) $this->mongo->revisions->insert($revision);
        
        if ($this->hasSearch) $this->searchIndex($id, $args, false);
        return true;
    }
    
    public function delete($id) { // Insecure
        $entry = $this->db->findOne(array('_id' => $this->_toMongoId($id)));
        if (empty($entry))
            return self::ERROR_NONEXISTANT;
        
        if ($this->hasSearch) Search::delete($id);
        return $this->db->update(array('_id' => $this->_toMongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }
    
    private function searchIndex($id, $args, $append) {
        if ($append) $args[] = false;
        $entry = call_user_func_array(array($this, 'validate'), $args);
        Search::index((string) $id, $entry);
    }
    
    public function resolveUser(&$user) {
        if (is_string($user)) {
            $user = array('username' => $user);
        } else {
            $user = MongoDBRef::get($this->mongo, $user);
        }
    }
    
}
