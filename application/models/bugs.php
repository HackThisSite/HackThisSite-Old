<?php
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
    
    public function getNew($filter = 'open', $page = 1) {
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
    
    public function get($id, $idlib = true, $justOne = false, $fixUTF8 = true) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('ghosted' => false);
            $keys = $idLib->dissectKeys($id, 'bug');
            
            $query['created'] = (int) $keys['time'];
        } else {
            $query = array('_id' => $this->_toMongoId($id));
        }
        
        $results = $this->db->find($query);
        
        if (empty($results)) return 'Invalid id.';
        
        if (!$idlib) {
            $toReturn = iterator_to_array($results);
            
            foreach ($toReturn as $key => $entry) {
                $this->resolveUser($toReturn[$key]['reporter']);
                if ($fixUTF8) $this->resolveUTF8($toReturn[$key]);
            }
            
            return ($justOne ? reset($toReturn) : $toReturn);
        }

        $toReturn = array();
        
        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array(
                '_id' => $result['_id'], 
                'created' => $result['created']), 'bugs')) continue;

            $this->resolveUser($result['reporter']);
            if ($fixUTF8) $this->resolveUTF8($result);
            
            if ($justOne) return $result;
            array_push($toReturn, $result);
        }

        return $toReturn;
    }
    
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
        
        return $entry;
    }
    
    public function alter($id, $diff) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array(
            '$set' => $diff));
        
        return true;
    }

    static public function canView($bug) {
        if ($bug['public'] == true) return true;
        if ((Session::isLoggedIn() && 
            (string) $bug['reporter']['$id'] == (string) Session::getVar('_id')) ||
            CheckAcl::can('viewPrivateBug')) return true;
            
        return false;
    }
    
}
