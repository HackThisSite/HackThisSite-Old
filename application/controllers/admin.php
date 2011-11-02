<?php
class controller_admin extends Controller {

    var $bad = false;

    public function index()
    {
        //
    }

    public function info()
    {
        phpinfo();
        exit;
    }

    public function data($arguments)
    {
        if (count($arguments) && $arguments[0] == "clear")
        {
            // clear file cache
            apc_clear_cache();

            // clear user cache
            apc_clear_cache("user");

            // reload the configs for the rest of the request cycle
            Config::forceReload();
        }

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
