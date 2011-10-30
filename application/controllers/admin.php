<?php
class controller_admin extends Controller {

    var $bad = false;

    public function index()
    {
        //
    }

    public function data($arguments)
    {
        $apc = new APCIterator('user');
        $info = apc_cache_info();
        $this->view['count'] = $apc->getTotalCount();
        $this->view['hits'] = $apc->getTotalHits();
        $this->view['misses'] = $info['num_misses'];
        $this->view['size'] = $apc->getTotalSize();
    }

    public function navigation($arguments)
    {
        // replaces with new nav backend
    }

    public function post_news($arguments)
    {
        //
    }
}
