<?php
class lectures {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    const ERROR_NONEXISTANT = "No lecture found by that id.";
    const ERROR_NOUPCOMING = "No upcoming lectures.";
    
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
        return $this->db->insert(array('title' => $title, 'lecturer' => $lecturer, 'description' => $description,
            'time' => $time, 'duration' => $duration));
    }
    
    public function edit($id, $title, $lecturer, $description, $time, $duration) {
        return $this->db->update(array('_id' => new MongoId($id)), array('$set' => 
            array('title' => $title, 'lecturer' => $lecturer, 'description' => $description,
            'time' => $time, 'duration' => $duration)));
    }
    
    public function delete($id) {
        return $this->db->update(array('_id' => new MongoId($id)), 
            array('$set' => array('ghosted' => true)));
    }
}

