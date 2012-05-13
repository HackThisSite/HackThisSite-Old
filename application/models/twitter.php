<?php
class twitter extends mongoBase {
    
    private $redis;
    
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    protected function getOfficialTweets() {
        return $this->redis->zRange('hts_tweets', 0, -1);
    }
}
