<?php
/**
 * Revisions
 * 
 * @package Model
 */
class revisions extends mongoBase {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";
    
    const ERROR_NONEXISTANT = 'That revision was not found.';
    
    var $db;
    var $mongo;
    
    /**
     * Create a new instance.
     * 
     * @param resource $mongo A MongoDB connection.
     */
    public function __construct($mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->revisions;
    }
    
    /**
     * Get revisions for a piece of content.
     * 
     * @param string $id The content id.
     * 
     * @return array The revisions.
     */
    public function getForId($id) {
        $data = $this->db->find(array('contentId' => (string) $id))
            ->sort(array('_id' => -1));
        return iterator_to_array($data);
    }

    // Document later.
    public function getById($id, $for, $diffdFields) {
        $data = $this->db->find(array(
            '_id' => array('$gte' => $this->_toMongoId((string) $id))))->sort(array('_id' => -1));
        $data = iterator_to_array($data);

        if (empty($data[(string) $id])) return self::ERROR_NONEXISTANT;
        $data = self::resolve($for, $data, $diffdFields, false);
        return end($data);
    }
    
    /**
     * Resolve diffs.
     * 
     * @param array $current The current content as an array.
     * @param array[] $revisions An array of revisions of the content.
     * @param array $diffdFields The fields that are diff'd.
     * @param bool $merge Merge the current and revision to return 1 entry.  (Cite)
     */
    public static function resolve($current, $revisions, $diffdFields, $merge = true) {
        $return = array();
        
        foreach ($revisions as $revision) {
            $nCurrent = ($merge ? array_merge($current, $revision) : $revision);
            
            foreach ($diffdFields as $field) {
                if ($field[0] == '$') {
                    $s = true;
                    $field = substr($field, 1);
                    $current[$field] = serialize($current[$field]);
                }
                
                $nCurrent[$field] = FineDiff::renderToTextFromOpcodes($current[$field], 
                    $nCurrent[$field]);
            }
            
            $current = $nCurrent;
            if ($s) $current[$field] = unserialize($current[$field]);
            
            array_push($return, $current);
        }
        
        return $return;
    }
    
}
