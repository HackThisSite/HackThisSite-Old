<?php
class comments {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;

    private function connect() {
        $db    = Config::get(self::KEY_DB);
        $mongo = new Mongo(Config::get(self::KEY_SERVER));

        $this->db = $mongo->$db->content;
    }

    public function totalComments($id) {
        if (apc_exists('numComments_' . $id)) return apc_fetch('numComments_' . $id);

        $comments = $this->realTotalComments($id);
        if (!empty($comments)) apc_add('numComments_' . $id, $comments, 30);
        return $comments;
    }

    public function getComments($id, $page) {
        if (apc_exists('comments_' . $id . '_' . $page)) return apc_fetch('comments_' . $id . '_' . $page);

        $comments = $this->realGetComments($id, $page);
        if (!empty($comments)) apc_add('comments_' . $id . '_' . $page, $comments, 30);
        return $comments;
    }

    private function realTotalComments($id) {
        if (empty($this->db)) $this->connect();
        $num = $this->db->find(array('type' => 'comment', 'contentId' => (string) $id, 'ghosted' => false),
        array('userId' => 1, 'date' => 1, 'body' => 1))->count();
        return $num;
    }

    private function realGetComments($id, $page) {
        if (empty($this->db)) $this->connect();
        $pageLimit = 10;
        $comments = $this->db->find(array('type' => 'comment', 'contentId' => (string) $id, 'ghosted' => false),
        array('userId' => 1, 'date' => 1, 'body' => 1))->skip(($page - 1) * $pageLimit)->limit($pageLimit);
        return iterator_to_array($comments);
    }

}
