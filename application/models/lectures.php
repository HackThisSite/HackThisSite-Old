<?php
/**
 * Lectures
 * 
 * @package Model
 */
class lectures extends baseModel {
    
    var $cdata = array('title', 'lecturer', 'description');
    var $hasSearch = false;
    var $hasRevisions = false;
    var $collection = 'lectures';
    var $type = 'lecture';
    
    /**
     * Get a lecture.
     * 
     * @param string $id The lecture id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The lecture as an array, or an error string.
     */
    protected function get($id, $idlib = false, $justOne = true, $fixUTF8 = true) {
        $record = $this->db->findOne(array(
            '_id' => new MongoId($id), 
            'ghosted' => false
            ));
            
        if (empty($record)) return 'Invalid id.';
        if ($fixUTF8) $this->resolveUTF8($record);
        
        return $record;
    }
    
    /**
     * Get new lectures.
     * 
     * @return mixed The new lectures as an array, or an error string.
     */
    protected function getNew() {
        $records = $this->db->find(array(
            'time' => array('$gte' => time()), 
            'ghosted' => false
            ))
            ->sort(array('time' => -1));
        if ($records->count() == 0) return 'No upcoming lectures.';
        $records = iterator_to_array($records);
        
        foreach ($records as $key => $record) {
            $this->resolveUTF8($records[$key]);
        }
        
        return $records;
    }
    
    /**
     * Get lectures a user has given.
     * 
     * @param string $username The username to use.
     * 
     * @return array The lectures this user have given.
     */
    protected function getForUser($username) {
        $query = array(
            'lecturer' => $username,
            'ghosted' => false
        );
        
        return iterator_to_array($this->db->find($query));
    }
    
    // Content management magic.
    public function validate($title, $lecturer, $description, $time, $duration, $creating = true) {
        $title = substr($this->clean($title), 0, 100);
        $lecturer = substr($this->clean($lecturer), 0, 80);
        $description = substr($this->clean($description), 0, 2000);
        $time = strtotime($time);
        $duration = strtotime($duration) - time();
        
        if (empty($title)) return 'Invalid title.';
        if (empty($lecturer)) return 'Invalid lecturer';
        if (empty($description)) return 'Invalid lecturer';
        if (empty($time))return 'I can\'t understand the date you chose.';
        if (empty($duration)) return 'I can\'t understand the duration you chose.';
        
        $entry = array(
            'title' => $title, 
            'lecturer' => $lecturer, 
            'description' => $description,
            'time' => $time, 
            'duration' => $duration, 
            'ghosted' => false
            );
        
        return $entry;
    }
    
}
