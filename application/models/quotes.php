<?php

class quotes extends mongoBase
{
    const KEY_ID    = "_id";
    const KEY_QUOTE = "quote";

    const DB_NAME = "mongo:db";

    const CACHE_KEY_COUNT = "cache:quotes:count";
    const CACHE_PREFIX    = "cache:quotes:i:";

    private $mongo;
    private $quotes;

    public function __construct($mongo)
    {
        $db           = Config::get(self::DB_NAME);
        $this->mongo  = $mongo->$db;
        $this->quotes = $mongo->$db->quotes;
    }

    public function add($quote)
    {
        $this->quotes->insert(array(self::KEY_QUOTE => $quote));
        $this->_invalidateCache();
        $this->_populateCache();
    }

    public function remove($id)
    {
        $id = $this->_toMongoId($id);
        $this->quotes->remove(array(self::KEY_ID => $id));
        $this->_invalidateCache();
        $this->_populateCache();
    }

    public function getRandom()
    {
        $count = apc_fetch(self::CACHE_KEY_COUNT);
        if (!$count) { return false; }

        return apc_fetch(self::CACHE_PREFIX . $count);
    }

    public function getAllFromDb()
    {
        return $this->quotes->find();
    }

    private function _invalidateCache()
    {
        $count = apc_fetch(self::CACHE_KEY_COUNT);
        if ($count === false) { return false; }

        $i = 0;
        while ($i < $count) { apc_delete(self::CACHE_PREFIX . $i++); }
        apc_delete(self::CACHE_KEY_COUNT);
    }

    private function _populateCache()
    {
        $i = 0;
        foreach ($this->getAllFromDb() as $quote)
        {
            apc_store(self::CACHE_PREFIX . $i++);
        }
        apc_store(self::CACHE_KEY_COUNT, --$i);
    }
}