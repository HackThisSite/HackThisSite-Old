<?php
class news {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;

    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->db = $mongo->$db->content;
    }

    public function getNewPosts($cache = true) {
        $news = $this->realGetNewPosts();
        return $news;
    }

    public function getNews($id, $cache = true) {
        if ($cache && apc_exists('news_' . $id)) return apc_fetch('news_' . $id);

        $news = $this->realGetNews($id);
        if ($cache && !empty($news)) apc_add('news_' . $id, $news, 10);
        return $news;
    }

    public function saveNews($title, $text, $commentable) {
        $forums = new Forums;
        $data = $forums->loginData();
        $id = $data['id'];

        $entry = array('type' => 'news', 'title' => htmlentities($title), 'body' => $text, 'userId' => $id, 'date' => time(), 'commentable' => (bool) $commentable, 'ghosted' => false, 'flaggable' => false);
        $this->db->insert($entry);
    }

    public function editNews($id, $title, $text, $commentable) {
        $this->db->update(array('_id' => $id), array('$set' => array(
            'title' => htmlentities($title), 'body' => $text, 'commentable' => (bool) $commentable)));
    }

    public function deleteNews($id) {
        $idLib = new Id;
        $query = array('type' => 'news', 'ghosted' => false);
        $keys = $idLib->dissectKeys($id, 'news');

        $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + 86400);
        $results = $this->db->find($query);
        $actual = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('title' => $result['title'], 'date' => $result['date']), 'news'))
                continue;

            $actual = $result;
        }

        if (empty($actual)) return false;

        $this->db->remove(array('_id' => $actual['_id']), array('justOne' => true));
        return true;
    }

    public function realGetNewPosts() {
        return $this->db->find(
            array(
                'type' => 'news',
                'ghosted' => false
            )
        )->sort(array('date' => -1))
         ->limit(10);
    }

    public function realGetNews($id) {
        $idLib = new Id;

        $query = array('type' => 'news', 'ghosted' => false);
        $keys = $idLib->dissectKeys($id, 'news');

        $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);

        $results = $this->db->find($query);
        $toReturn = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('ambiguity' => $keys['ambiguity'],
                'reportedDate' => $keys['date'], 'date' => $result['date'],
                'title' => $result['title']), 'news'))
                continue;

            array_push($toReturn, $result);
        }

        return $toReturn;
    }
}
