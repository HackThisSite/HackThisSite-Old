<?php
class controller_stats extends Controller {
    
    public function index() {
        if (!CheckAcl::can('viewStats')) 
            return Error::set('You are not allowed to view stats!');
        
        $info = new APCIterator('user');
        $redis = new redisInfo(ConnectionFactory::get('redis'));
        $redisInfo = $redis->info();
        
        $this->view['apcNoKeys'] = $info->getTotalCount();
        $this->view['apcSize'] = $info->getTotalSize();
        
        $this->view['redisVersion'] = $redisInfo['redis_version'];
        $this->view['redisSIP'] = $redisInfo['bgsave_in_progress'];
        $this->view['redisNoChans'] = $redisInfo['pubsub_channels'];
        $this->view['redisMem'] = $redisInfo['used_memory'];
        $this->view['redisLastSave'] = $redisInfo['last_save_time'];
        $this->view['valid'] = true;
    }
}
