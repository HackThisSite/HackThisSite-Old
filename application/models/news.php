<?php
/**
 * News
 * 
 * @package Model
 */
class news extends baseModel {

    var $cdata = array('title', 'department', 'body', '@tags');
    var $hasSearch = true;
    var $hasRevisions = true;
    var $collection = 'news';
    var $type = 'news';
    
    const PER_PAGE = 10;

    /**
     * Get new news posts.
     * 
     * @param bool $shortNews True if you're getting short news.
     * 
     * @return array The new news posts as an array.
     */
    protected function getNewPosts($shortNews = false) {
        $posts = $this->db->find(
            array(
                'shortNews' => $shortNews,
                'ghosted' => false
            )
        )->sort(array('date' => -1))
         ->limit(($shortNews ? 5 : 10));
         $posts = iterator_to_array($posts);

        $comments = new comments(ConnectionFactory::get('mongo'));
        
         foreach ($posts as $key => $post) {
             $this->resolveUser($posts[$key]['user']);
             $this->resolveUTF8($posts[$key]);
             $posts[$key]['comments'] = $comments->getCount($post['_id']);
         }
         
         return $posts;
    }

    /**
     * Get a news post.
     * 
     * @param string $id The news id.
     * @param bool $idlib True if the Id library should be used (False for MongoIds)
     * @param bool $justOne True if only one entry should be returned.
     * @param bool $fixUTF8 True if UTF8 should be decoded.
     * 
     * @return mixed The news post as an array, or an error string.
     */
    protected function get($id, $idlib = true, $justOne = false, $fixUTF8 = true, $limit = self::PER_PAGE) {
        $query = array('ghosted' => false);
        if ($idlib) {
            $keys = Id::dissectKeys($id, 'news');
            $query['date'] = array('$gte' => $keys['date'], '$lte' => $keys['date'] + $keys['ambiguity']);
        } else {
            $query['_id'] = $this->_toMongoId($id);
        }

        $results = $this->db->find($query)->sort(array('date' => -1));
        $total = $results->count();
        $valid = array();
        
        if ($limit != null) $results->limit($limit);
        if ($idlib) {
            foreach ($results as $result) {
                if (!Id::validateHash($id, array('ambiguity' => $keys['ambiguity'],
                    'reportedDate' => $keys['date'], 'date' => $result['date'],
                    'title' => $result['title']), 'news'))
                    continue;
                array_push($valid, $result);
            }
        } else { $valid = iterator_to_array($results); }
        if ($justOne) $valid = array(reset($valid));
        
        if (empty($valid)) return array('Invalid id.', 0);
        
        $comments = new comments(ConnectionFactory::get('mongo'));
        foreach ($valid as $key => $entry) {
            $this->resolveUser($valid[$key]['user']);
            if ($fixUTF8) $this->resolveUTF8($valid[$key]);
            $valid[$key]['comments'] = $comments->getCount($entry['_id']);
            $valid[$key]['rating'] = $this->getScore($entry['_id']);
        }
        
        if ($justOne) return reset($valid);
        return array($valid, $total);
    }

    /**
     * Get a new post's rating.
     * 
     * @param string $id The news post's id.
     * 
     * @return int The new post's score, 1-10.
     */
    public function getScore($id) {
        $likes = $this->mongo->newsVotes->count(array(
            'contentId' => (string) $id, 'liked' => true));
        $dislikes = $this->mongo->newsVotes->count(array(
            'contentId' => (string) $id, 'liked' => false));

        return array('likes' => $likes, 'dislikes' => $dislikes);
    }
    
    /**
     * Casts a vote on a news post.
     * 
     * @param string $id The news post's id.
     * @param int $vote The vote, 1-10.
     * 
     * @return mixed Null if successful, or an error string.
     */
    public function castVote($id, $vote) {
        $id = (string) $id;
        $vote = (string) $vote;
        
        if ($vote != 'like' && $vote != 'dislike') return 'Invalid vote.';
        $data = $this->get($id, false, true);
        if (empty($data)) return 'Invalid news id.';
        
        $data = $this->mongo->newsVotes
            ->count(array('contentId' => $id, 'userId' => (string) Session::getVar('_id')));
        if ($data != 0) return 'You\'ve already voted on this!';
        
        $entry = array(
            'contentId' => $id,
            'userId' => (string) Session::getVar('_id'),
            'liked' => ($vote == 'like')
        );
        
        $this->mongo->newsVotes->insert($entry);
        $this->clearCache($id, false);
        return true;
    }

    // Content management magic.
    public function validate($title, $department, $text, $tags, $shortNews, $commentable, $creating = true, $preview = false) {
        $ref = MongoDBRef::create('users', Session::getVar('_id'));
        $func = function($value) { return trim($value); };
        
        $title = substr($this->clean($title), 0, 100);
        $department = substr(str_replace(' ', '-', strtolower($this->clean($department))), 0, 80);
        $body = substr($this->clean($text), 0, 5000);
        if (is_array($tags)) $tags = implode(',', $tags);
        $tags = array_map($func, explode(',', $this->clean($tags)));
        
        if (empty($title)) return 'Invalid title.';
        if (empty($body)) return 'Invalid body.';
        
        $entry = array( 
            'title' => $title, 
            'department' => $department, 
            'body' => $body, 
            'tags' => $tags,
            'user' => $ref, 
            'date' => time(), 
            'shortNews' => (bool) $shortNews, 
            'commentable' => (bool) $commentable, 
            'ghosted' => false
            );
        if (!$creating) unset($entry['user'], $entry['date'], $entry['flaggable']);
        if (!$preview) { self::ApcPurge('getNewPosts', null); }
        return $entry;
    }

    // Content management magic.
    public function generateRevision($update, $old) {
        $titleFD = new FineDiff($update['title'], $old['title']);
        $departmentFD = new FineDiff($update['department'], $old['department']);
        $bodyFD = new FineDiff($update['body'], $old['body']);
        $tagsFD = new FineDiff(serialize($update['tags']), serialize($old['tags']));
        
        $revision = array(
            'title' => $titleFD->getOpcodes(),
            'department' => $departmentFD->getOpcodes(),
            'body' => $bodyFD->getOpcodes(),
            'tags' => $tagsFD->getOpcodes(),
            'shortNews' => $old['shortNews'],
            'commentable' => $old['commentable']
            );
        return $revision;
    }
    
}
