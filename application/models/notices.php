<?php
/**
 * Notices
 * 
 * @package Model
 */
class notices extends mongoBase {

    const KEY = 'notices';
    var $redis;
    
    /**
     * Creates a new instance.
     * 
     * @param resource $redis A Redis connection.
     */
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    /**
     * Get all notices.
     * 
     * @return array Array of notices.
     */
    public function getAll() {
        return $this->redis->sMembers(self::KEY);
    }
    
    /**
     * Get a notice by it's id.
     * 
     * @param int $id The notice id.
     * 
     * @return mixed The notice as an array, or an error string.
     */
    public function get($id) {
        --$id;
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        return array('id' => $id, 'notice' => $notices[$id]);
    }
    
    /**
     * Create a new notice.
     * 
     * @param string $text The notice text to create.
     * 
     * @return mixed True on success, or an error string.
     */
    public function create($text) {
        $validate = $this->validate($text);
        if (is_string($validate)) return $validate;
        
        $this->redis->sAdd(self::KEY, $text);
        return true;
    }
    
    /**
     * Edit a notice.
     * 
     * @param int $id The notice id.
     * @param string $text The text to change the notice to.
     * 
     * @return mixed Null on success, or an error string.
     */
    public function edit($id, $text) {
        --$id;
        $validate = $this->validate($text);
        if (is_string($validate)) return $validate;
        
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        $notice = $notices[$id];
        
        $this->redis->sRem(self::KEY, $notice);
        $this->redis->sAdd(self::KEY, $text);
    }
    
    /**
     * Delete a notice.
     * 
     * @param int $id The notice id.
     * 
     * @return mixed Null on success, or an error string.
     */
    public function delete($id) {
        --$id;
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        $notice = $notices[$id];
        
        $this->redis->sRem(self::KEY, $notice);
    }
    
    // Content management magic.
    private function validate(&$text) {
        $text = $this->clean($text);
        
        if (empty($text)) return 'No notice was found.';
        return true;
    }
    
}
