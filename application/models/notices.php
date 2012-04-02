<?php
class notices extends mongoBase {

    const KEY = 'notices';
    var $redis;
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    public function getAll() {
        return $this->redis->sMembers(self::KEY);
    }
    
    public function get($id) {
        --$id;
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        return array('id' => $id, 'notice' => $notices[$id]);
    }
    
    public function create($text) {
        $validate = $this->validate($text);
        if (is_string($validate)) return $validate;
        
        $this->redis->sAdd(self::KEY, $text);
        return true;
    }
    
    public function edit($id, $text) {
        --$id;
        $validate = $this->validate($text);
        if (is_string($validate)) return $validate;
        
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        $notice = $notices[$id];
        
        $this->redis->sRem(self::KEY, $notice);
        $this->redis->sAdd(self::KEY, $text);
    }
    
    public function delete($id) {
        --$id;
        $notices = $this->redis->sMembers(self::KEY);
        if (empty($notices[$id])) return 'Invalid id.';
        $notice = $notices[$id];
        
        $this->redis->sRem(self::KEY, $notice);
    }
    
    private function validate(&$text) {
        $text = $this->clean($text);
        
        if (empty($text)) return 'No notice was found.';
        return true;
    }
    
}
