<?php
class bugs extends mongoBase {
    const KEY_SERVER = "mongo:server";
    const KEY_DB     = "mongo:db";

    var $db;
    var $mongo;
    static $category = array(
        'Other',
        'News',
        'Articles',
        'Lectures',
        'Typo'
        );
    static $status = array(
        'new',
        'feedback',
        'acknowledged',
        'confirmed',
        'assigned',
        'resolved',
        'closed',
        'sysadmin',
        'fixed in git',
        );
    static $filters = array(
        'all' => array(),
        'open' => array('$nor' => array(
                    array('status' => 5),
                    array('status' => 6),
                    array('status' => 8)
                )),
        'unclosed' => array('status' => array('$ne' => 6)),
        'unresolved' => array('status' => array('$ne' => 5)),
        'new' => array('status' => 0),
        'sysadmin' => array('status' => 7)
        );
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->content;
    }
    
    public function getNew($filter = 'open', $page = 1) {
        $pageLimit = 3;
        
        $query = array(
            'type' => 'bug', 
            'ghosted' => false
            );
           
        $query = array_merge($query, self::$filters[$filter]);
        $sort = array(
            'flagged' => -1, 
            'created' => -1
            );
        
        $results = $this->db->find($query);
        $total = $results->count();
        $results = $results->sort($sort)->skip(($page - 1) * $pageLimit)->limit($pageLimit);
        $count = $results->count();
        
        $rows = iterator_to_array($results);
        
        $return = array(
            'total' => $total, 
            'count' => $count, 
            'pages' => ceil($total / $pageLimit), 
            'bugs' => $rows
            );
        return $return;
    }
    
    public function get($id, $idlib = true) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('type' => 'bug', 'ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'bug');
            
            $query['created'] = (int) $keys['time'];
        } else {
            $query = array('_id' => $this->_toMongoId($id), 'type' => 'bug');
        }
        
        $results = $this->db->find($query);
        
        if (!$idlib)
            return iterator_to_array($results);

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array(
                '_id' => $result['_id'], 
                'created' => $result['created']), 'bugs')) continue;

            return $result;
        }

        return false;
    }
    
    public function create($title, $category, $description, $reproduction, $public) {
        $entry = $this->validate($title, $category, $description, $reproduction, $public);
        if (is_string($entry)) return $entry;
        
        $this->db->insert($entry);
        return true;
    }
    
    public function edit($id, $title, $category, $description, $reproduction, $public) {
        $entry = $this->validate($title, $category, $description, $reproduction, $public);
        if (is_string($entry)) return $entry;
        
        $this->db->update(array('_id' => $this->_toMongoId($id)), $entry);
        return true;
    }
    
    public function alter($id, $diff) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array(
            '$set' => $diff));
        
        return true;
    }
    
    public function delete($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), 
            array('$set' => array('ghosted' => true)));
        return true;
    }
    
    private function validate($title, $category, $description, $reproduction, $public) {
        $title = $this->clean($title);
        $description = $this->clean($description);
        $reproduction = $this->clean($reproduction);
        $public = (bool) $public;
        
        if (empty($title)) return 'Invalid title.';
        if (empty($description)) return 'Invalid description.';
        if (empty($reproduction)) return 'Invalid steps for reproduction.';
        if (empty(self::$category[(int) $category])) return 'Invalid category.';
        if ($public != false && $public != true) return 'Invalid public.';
        
        $entry = array(
            'type' => 'bug',
            'title' => substr($title, 0, 100),
            'reporter' => MongoDBRef::create('users', Session::getVar('_id')),
            'category' => (int) $category,
            'status' => 0,
            'description' => substr($description, 0, 5000),
            'reproduction' => substr($reproduction, 0, 5000),
            'created' => time(),
            'lastUpdate' => time(),
            'public' => $public,
            'commentable' => true,
            'flagged' => true,
            'ghosted' => false
            );
        
        return $entry;
    }

    static public function canView($bug) {
        if ($bug['public'] == true) return true;
        if ((Session::isLoggedIn() && 
            (string) $bug['reporter']['$id'] == (string) Session::getVar('_id')) ||
            CheckAcl::can('viewPrivateBug')) return true;
            
        return false;
    }
}
