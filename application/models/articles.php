<?php
/**
 * Articles
 * 
 * @package Model
 */
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
        'reverse-engineering' => 'Reverse Engineering',
        'uncategorized' => 'Uncategorized',
    );
    
    const PER_PAGE = 10;
    
    /**
     * Gets new articles.
     * 
     * @param string $category Article category.
     * @param int $page Page number.
     * @param int $limit Limit of entries per page.
     * 
     * @return array New articles.
     */
    protected function getNewPosts($category = 'new', $page = 1, $limit = self::PER_PAGE) {
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

    /**
     * Get an article(s).
     * 
     * @param string $id Article id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The article/articles as an array, or an error string.
     */
    protected function get($id, $idlib = true, $justOne = false, $fixUTF8 = true) {
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
    
    /**
     * Get an articles rating.
     * 
     * @param string $articleId The article's id.
     * 
     * @return int The articles score, 1-10.
     */
    public function getScore($articleId) {
        $likes = $this->mongo->articleVotes->count(array(
            'contentId' => (string) $articleId, 'liked' => true));
        $dislikes = $this->mongo->articleVotes->count(array(
            'contentId' => (string) $articleId, 'liked' => false));

        return array('likes' => $likes, 'dislikes' => $dislikes);
    }
    
    /**
     * Casts a vote on an article.
     * 
     * @param string $articleId The article's id.
     * @param int $vote The vote, 1-10.
     * 
     * @return mixed Null if successful, or an error string.
     */
    public function castVote($articleId, $vote) {
        $articleId = (string) $articleId;
        $vote = (string) $vote;
        
        if ($vote != 'like' && $vote != 'dislike') return 'Invalid vote.';
        $data = $this->get($articleId, false, true);
        if (empty($data)) return 'Invalid article id.';
        
        $data = $this->mongo->articleVotes
            ->count(array('contentId' => $articleId, 'userId' => (string) Session::getVar('_id')));
        if ($data != 0) return 'You\'ve already voted on this!';
        
        $entry = array(
            'contentId' => $articleId,
            'userId' => (string) Session::getVar('_id'),
            'liked' => ($vote == 'like')
        );
        
        $this->mongo->articleVotes->insert($entry);
        $this->clearCache($articleId, false);
        return true;
    }
    
    /**
     * Gets articles a user has posted.
     * 
     * @param string $userId The user's id.
     * 
     * @return array Articles the user has written.
     */
    protected function getForUser($userId) {
        $ref = MongoDBRef::create('users', $userId);
        
        $query = array(
            'user' => $ref,
            'ghosted' => false, 
            'published' => true
        );
        
        return iterator_to_array($this->db->find($query));
    }
    
    /**
     * Gets the new unapproved article.
     * 
     * @return mixed Null if no more, or the next article.
     */
    public function getNextUnapproved() {
        $record = $this->db->findOne(array('published' => false, 'ghosted' => false));
        
        if (!empty($record)) {
            $this->resolveUser($record['user']);
            $this->resolveUTF8($record);
        }
        
        return $record;
    }

    // Content management magic.
    public function validate($title, $category, $description, $text, $tags, $creating = true) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $description = substr($this->clean($description), 0, 500);
        $body = substr($this->clean($text), 0, 7000);
        if (is_array($tags)) $tags = implode(',', $tags);
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
        
        self::ApcPurge('getNewPosts', null);
        return $entry;
    }
    
    // Content management magic.
    public function generateRevision($update, $old) {
        $titleFD = new FineDiff($update['title'], $old['title']);
        $descriptionFD = new FineDiff($update['description'], $old['description']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $revision = array(
            'title' => $titleFD->getOpcodes(),
            'description' => $descriptionFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'published' => $old['published'],
            'commentable' => $old['commentable']
            );
        return $revision;
    }

    /**
     * Marks an article as approved.
     * 
     * @param string $id The article id.
     */
    public function approve($id) {
        $this->db->update(array('_id' => $this->_toMongoId($id)), array('$set' => array('published' => true)));
        self::ApcPurge('getNewPosts', null);
        return true;
    }
    
}
