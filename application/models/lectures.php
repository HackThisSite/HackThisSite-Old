<?php
class lectures extends mongoBase {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    const ERROR_NONEXISTANT = "No lecture found by that id.";
    const ERROR_NOUPCOMING = "No upcoming lectures.";
    
    const ERROR_INVALIDDATE = "I can't understand the date you chose.";
    const ERROR_INVALIDDURATION = "I can't understand the duration you chose.";
    
    var $db;
    var $mongo;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }
    
    public function get($id) {
        $record = $this->db->findOne(array('_id' => new MongoId($id), 'type' => 'lecture', 'ghosted' => false));
        if (empty($record))
            return self::ERROR_NONEXISTANT;
        
        return $record;
    }
    
    public function getNew() {
        $records = $this->db->find(array('time' => array('$gte' => time()), 'ghosted' => false))->sort(array('time' => -1));
        if ($records->count() == 0)
            return self::ERROR_NOUPCOMING;
        return $records;
    }
    
    public function create($title, $lecturer, $description, $time, $duration) {
        $time = strtotime($time);
        $duration = strtotime($duration) - time();
        if (empty($time))return self::ERROR_INVALIDDATE;
        if (empty($duration)) return self::ERROR_INVALIDDURATION;
        
        $entry = array(
            'type' => 'lecture', 
            'title' => $this->clean($title), 
            'lecturer' => $this->clean($lecturer), 
            'description' => $this->clean($description),
            'time' => $time, 
            'duration' => $duration, 
            'ghosted' => false
            );
        
        $this->db->insert($entry);
        
        $id = $entry['_id'];
        unset($entry['_id'], $entry['time'], $entry['duration']);
        Search::index($id, $entry);
    }
    
    public function edit($id, $title, $lecturer, $description, $time, $duration) {
        $time = strtotime($time);
        $duration = strtotime($duration) - time();
        if (empty($time))return self::ERROR_INVALIDDATE;
        if (empty($duration)) return self::ERROR_INVALIDDURATION;
        
        $entry = array(
            'type' => 'lecture',
            'title' => $this->clean($title), 
            'lecturer' => $this->clean($lecturer), 
            'description' => $this->clean($description),
            'time' => $time, 
            'duration' => $duration,
            'ghosted' => false
            );
            
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => $entry));
        
        $id = $entry['_id'];
        unset($entry['_id'], $entry['time'], $entry['duration']);
        Search::index($id, $entry);
    }
    
    public function delete($id) {
        $entry = $this->db->findOne(array('_id' => $this->_toMongoId($id)));
        if (empty($entry))
            return self::ERROR_NONEXISTANT;
        
        Search::delete($id);
        return $this->db->update(array('_id' => $this->_toMongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }
}

