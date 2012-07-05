<?php
/**
 * Bugs
 * 
 * @package Model
 */
class bugs extends baseModel {
    
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
        
    var $cdata = array('title', 'description', 'reproduction');
    var $hasSearch = false;
    var $hasRevisions = false;
    var $collection = 'bugs';
    var $type = 'bug';
    
    const PER_PAGE = 10;
    
    /**
     * Gets new bugs.
     * 
     * @param string $filter Status to show.
     * @param int $page The page to get.
     * 
     * @return array The new bugs.
     */
    protected function getNew($filter = 'open', $page = 1) {
        if (!isset(self::$filters[$filter])) $filter = 'open';
        if (intval($page) < 1) $page = 1;
        $pageLimit = 15;
        
        $query = array(
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
        
        foreach ($rows as $key => $row) {
            $this->resolveUTF8($rows[$key]);
        }
        
        $return = array(
            'total' => $total, 
            'count' => $count, 
            'pages' => ceil($total / $pageLimit), 
            'bugs' => $rows
            );
        return $return;
    }
    
    /**
     * Gets a bug.
     * 
     * @param string $id Bug id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The bug/bugs as an array, or an error string.
     */
    protected function get($id, $idlib = true, $justOne = false, $fixUTF8 = true, $page = 1, $limit = self::PER_PAGE) {
        $query = array('ghosted' => false);
        if ($idlib) {
            $keys = Id::dissectKeys($id, 'bugs');
            $query['created'] = (int) $keys['time'];
        } else {
            $query['_id'] = $this->_toMongoId($id);
        }
        
        $results = $this->db->find($query)->skip(($page - 1) * self::PER_PAGE);
        $total = $results->count();
        $valid = array();
        
        if ($limit != null) $results->limit($limit);
        if ($idlib) {
            foreach ($results as $result) {
            if (!Id::validateHash($id, array(
                '_id' => $result['_id'], 
                'created' => $result['created']), 'bugs')) continue;
                array_push($valid, $result);
            }
        } else { $valid = iterator_to_array($results); }
        if ($justOne) $valid = array(reset($valid));
        
        if (empty($valid) || $total == 0) return array('Invalid id.', 0);
        
        foreach ($valid as $key => $entry) {
            $this->resolveUser($valid[$key]['reporter']);
            if ($fixUTF8) $this->resolveUTF8($valid[$key]);
        }
        
        if ($justOne) return reset($valid);
        return array($valid, $total);
    }
    
    // Content management magic.
    public function validate($title, $category, $description, $reproduction, $public, $creating = true) {
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
        if (!$creating) unset($entry['reporter'], $entry['status'], 
            $entry['created'], $entry['commentable'], $entry['flagged']);
        self::ApcPurge('getNew', null);
        return $entry;
    }
    
    /**
     * Alter a bug
     * 
     * @param string $id The bug id.
     * @param array $diff The diff to apply.
     */
    public function alter($id, $diff) {
        $diff = array_merge($diff, array('lastUpdate' => time()));
        $this->db->update(array('_id' => $this->_toMongoId($id)), array(
            '$set' => $diff));
        
        self::ApcPurge('getNew', null);
        $this->clearCache($id);
        return true;
    }

    // Content management magic.
    static public function canView($bug) {
        if ($bug['public'] == true) return true;
        if ((Session::isLoggedIn() && 
            (string) $bug['reporter']['$id'] == (string) Session::getVar('_id')) ||
            CheckAcl::can('viewPrivateBug')) return true;
            
        return false;
    }
    
}
