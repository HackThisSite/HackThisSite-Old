<?php
class articles extends baseModel {

    var $cdata = array('title', 'body', '@tags');
    var $hasSearch = true;
    var $hasRevisions = true;
    var $collection = 'articles';
    var $type = 'article';
    static $categories = array(
        'web-hacking' => 'Web Hacking',
        'programming' => 'Programming',
        'os-specific' => 'OS Specific',
        'protecting-yourself' => 'Protecting Yourself',
        'political-activism' => 'Political Activism',
        'hts-tuts' => 'HTS Tutorials',
        'ethics' => 'Ethics',
        'other' => 'Other',
        'reverse-engineering' => 'Reverse Engineering'
    );
    
    const PER_PAGE = 10;
    
    public function getNewPosts($category = 'new', $page = 1, $limit = self::PER_PAGE) {
        $query = array(
            'ghosted' => false,
            'published' => true
        );
        
        if (empty(self::$categories[$category])) $category = 'new';
        
        if ($category != 'new') {
            $query['category'] = $category;
        }
        
        $page = (int) $page;
        if ($page < 1) $page = 1;
        
        $posts = $this->db->find($query)
            ->sort(array('date' => -1));
        $total = $posts->count();
        $posts->limit($limit)->skip(($page - 1) * self::PER_PAGE);
         
         $toReturn = array();
         
         foreach ($posts as $post) {
            $this->resolveUser($post['user']);
            $this->resolveUTF8($post);
            array_push($toReturn, $post);
        }
         
         return array(
            'total' => $total, 
            'page' => $page, 
            'articles' => $toReturn,
            'categories' => self::$categories,
            'currCategory' => $category
        );
    }

    public function get($id, $idlib = true, $justOne = false, $fixUTF8 = true) {
        if ($idlib) {
            $idLib = new Id;

            $query = array('ghosted' => false, 'published' => true);
            $keys = $idLib->dissectKeys($id, 'news');

            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query = array('_id' => $this->_toMongoId($id), 'published' => true, 'ghosted' => false);
        }

        $results = $this->db->find($query);
        
        if (empty($results)) return 'Invalid id.';
        
        if (!$idlib) {
            $toReturn = iterator_to_array($results);
            
            foreach ($toReturn as $key => $entry) {
                $this->resolveUser($toReturn[$key]['user']);
                if ($fixUTF8) $this->resolveUTF8($toReturn[$key]);
            }
            
            return ($justOne ? reset($toReturn) : $toReturn);
        }
            
        $toReturn = array();

        foreach ($results as $result) {
            if (!$idLib->validateHash($id, array('ambiguity' => $keys['ambiguity'],
                'reportedDate' => $keys['date'], 'date' => $result['date'],
                'title' => $result['title']), 'news'))
                continue;
            
            $result['rating'] = $this->getScore($result['_id']);
            $this->resolveUser($result['user']);
            if ($fixUTF8) $this->resolveUTF8($result);
            if ($justOne) return $result;
            array_push($toReturn, $result);
        }

        return $toReturn;
    }
    
    public function getScore($articleId) {
        $data = $this->mongo->articleVotes->findOne(array(
            'articleId' => (string) $articleId));

        return round($data['total'] / $data['count']);
    }
    
    public function castVote($articleId, $vote) {
        if ($vote < 1 || $vote > 10) return 'Invalid vote.';
        $data = $this->mongo->articleVoters->findOne(array(
            'userId' => (string) Session::getVar('_id'),
            'articleId' => (string) $articleId));
        if (!empty($data)) return 'You have already voted on this!';
        
        $data = $this->get($articleId, false, true);
        if (empty($data)) return 'Invalid article id.';
        
        $data = $this->mongo->articleVotes->findOne(array('articleId' => (string) $articleId));
        
        if (empty($data)) {
            $this->mongo->articleVotes->insert(array('articleId' => (string) $articleId, 
                'total' => (int) $vote, 'count' => 1));
        } else {
            $this->mongo->articleVotes->update(array('articleId' => (string) $articleId),
                array('$inc' => array('total' => (int) $vote, 'count' => 1)));
        }
        $this->mongo->articleVoters->insert(array('articleId' => (string) $articleId,
            'userId' => (string) Session::getVar('_id'), 'vote' => (int) $vote));
    }
    
    public function getForUser($userId) {
        $ref = MongoDBRef::create('users', $userId);
        
        $query = array(
            'user' => $ref,
            'ghosted' => false, 
            'published' => true
        );
        
        return iterator_to_array($this->db->find($query));
    }
    
    public function getNextUnapproved() {
        $record = $this->db->findOne(array('published' => false, 'ghosted' => false));
        
        if (!empty($record)) {
            $this->resolveUser($record['user']);
            $this->resolveUTF8($record);
        }
        
        return $record;
    }

    public function validate($title, $category, $description, $text, $tags, $creating = true) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $description = substr($this->clean($description), 0, 500);
        $body = substr($this->clean($text), 0, 7000);
        $tags = array_map($func, explode(',', $this->clean($tags)));
        
        if (empty($title)) return 'Invalid title.';
        if (empty($description)) return 'Invalid description';
        if (empty($body)) return 'Invalid body.';
        if (empty(self::$categories[$category])) return 'Invalid category.';
        
        $entry = array( 
            'title' => $title,
            'category' => $category,
            'description' => $description, 
            'body' => $body, 
            'tags' => $tags,
            'user' => $ref, 
            'date' => time(), 
            'commentable' => true, 
            'published' => false, 
            'ghosted' => false, 
            );
        
        if (!$creating) unset($entry['user'], $entry['date'], 
            $entry['commentable'], $entry['published']);
        
        return $entry;
    }
    
    public function generateRevision($update, $old) {
        $titleFD = new FineDiff($update['title'], $old['title']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $revision = array(
            'title' => $titleFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'published' => $old['published'],
            'flaggable' => $old['flaggable'],
            'commentable' => $old['commentable']
            );
        return $revision;
    }

    public function approve($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => array('published' => true)));
        return true;
    }
    
    public static function categories() {
        return self::$categories;
    }
}
