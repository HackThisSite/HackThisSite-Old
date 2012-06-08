<?php
class github extends mongoBase {
    
    private $redis;
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    protected function get($userId) {
        $toReturn = $this->redis->get('github_' . $userId);
        if ($toReturn == null) return $toReturn;
        return unserialize($toReturn);
    }
    
}
