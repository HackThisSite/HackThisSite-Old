<?php
/**
 * Missions
 * 
 * @package Model
 */
class missions extends mongoBase {
    
    var $db;
    
    public function __construct($connection) {
        $this->db = $connection->{Config::get('mongo:db')};
    }
    
    public function getTypes() {
        return $this->db->missionTypes->find()->sort(array('sort' => 1));
    }
    
    public function getMissionsByType($type) {
        return $this->db->missions->find(array('type' => $this->clean($type)))->sort(array('sort' => 1));
    }
    
    public function get($type, $sort) {
        return $this->db->missions->findOne(array('type' => $this->clean($type), 'sort' => (int) $this->clean($sort)));
    }
    
    public function getById($id) {
        return $this->db->missions->findOne(array('_id' => $this->_toMongoId($id)));
    }
    
    public function done($userId, $missionId) {
        $mission = $this->getById($missionId);
        //$inc: {'missions.basic.1' : 1}
        return $this->db->users->update(array('_id' => $userId), 
            array('$inc' => array('missions.' . $this->clean($mission['type']) . '.' . $this->clean($mission['sort']) => 1)));
    }
    
    public function getTimesDone($userId, $missionId) {
        $mission = $this->getById($missionId);
        $user = $this->db->users->findOne(array('_id' => $userId));
        
        if (!empty($user['missions'][$mission['type']][$mission['sort']]))
            return $user['missions'][$mission['type']][$mission['sort']];
        
        return false;
    }
    
}
