<?php
class lectures {
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
    
    public function add($title, $lecturer, $description, $time, $duration) {
        $time = strtotime($time);
        $duration = strtotime($duration) - time();
        if (empty($time))return self::ERROR_INVALIDDATE;
        if (empty($duration)) return self::ERROR_INVALIDDURATION;
        
        return $this->db->insert(array('type' => 'lecture', 'title' => $title, 
            'lecturer' => $lecturer, 'description' => $description,'time' => $time, 
            'duration' => $duration, 'ghosted' => false));
    }
    
    public function edit($id, $title, $lecturer, $description, $time, $duration) {
        $time = strtotime($time);
        $duration = strtotime($duration) - time();
        if (empty($time))return self::ERROR_INVALIDDATE;
        if (empty($duration)) return self::ERROR_INVALIDDURATION;
        
        return $this->db->update(array('_id' => new MongoId($id)), array('$set' => 
            array('title' => $title, 'lecturer' => $lecturer, 'description' => $description,
            'time' => $time, 'duration' => $duration)));
    }
    
    public function delete($id) {
        $entry = $this->db->findOne(array('_id' => new MongoId($id)));
        if (empty($entry))
            return self::ERROR_NONEXISTANT;
        
        return $this->db->update(array('_id' => new MongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }
}

