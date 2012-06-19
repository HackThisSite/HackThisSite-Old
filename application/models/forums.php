<?php
class forums extends mongoBase {
    
    private $redis;
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    public function getNew() {
        return unserialize($this->redis->get('forum_new'));
    }
    
}
