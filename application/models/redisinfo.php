<?php
class redisInfo extends mongoBase {
    
    private $redis;
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    protected function info() {
        return $this->redis->info();
    }
}
