<?php
/**
 * Base Model
 * 
 * @package Model
 */
class baseModel extends mongoBase {

    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;
    var $mongo;
    
    /**
     * Creates a new base model instance.
     * 
     * @param resource $mongo A MongoDB connection.
     */
    public function __construct($mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->{$this->collection};
    }
    
    /**
     * Create a new piece of content.
     * 
     * @return resource MongoDB id of the new content, or error string.
     */
    public function create() {
        $entry = call_user_func_array(array($this, 'validate'), func_get_args());
        if (is_string($entry)) return $entry;
        
        $this->db->insert($entry);
        if ($this->hasSearch) $this->searchIndex($entry['_id'], func_get_args(), true);
        return $entry;
    }
    
    /**
     * Edits a piece of content.
     * 
     * @return mixed Error string, or true if successful.
     */
    public function edit() {
        $args = func_get_args();
        $id = array_shift($args);
        $args[] = false;
        
        $entry = call_user_func_array(array($this, 'validate'), $args);
        if (is_string($entry)) return $entry;
        
        if ($this->hasRevisions) {
            $old = $this->get($id, false, true, false);
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
    
    /**
     * Delets a piece of content.
     * 
     * @param string $id The MongoDB id of the content.
     */
    public function delete($id) { 
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
        $entry['type'] = $this->type;
        Search::index((string) $id, $entry);
    }
    
    /**
     * Resolves user references.
     * 
     * @param array &$user User reference.
     */
    public function resolveUser(&$user) {
        if (isset($user['$id']) && !$user['$id']) {
            $user = array('username' => 'Anonymous');
        } else if (is_string($user)) {
            $user = array('username' => $user);
        } else {
            $user = MongoDBRef::get($this->mongo, $user);
        }
        
        if (empty($user)) $user = array('username' => 'Unknown');
    }
    
    /**
     * Resolves encoded UTF-8.
     * 
     * @param array &$entry The piece of encoded content.
     */
    public function resolveUTF8(&$entry) { // utf8_decode
        foreach ($this->cdata as $key) {
            if ($key[0] == '@') {
                $key = substr($key, 1);
                
                foreach ($entry[$key] as $k => $v) {
                    $entry[$key][$k] = utf8_decode($entry[$key][$k]);
                }
            } else {
                $entry[$key] = utf8_decode($entry[$key]);
            }
        }
    }
    
}
