<?php
class controller_search extends Controller {
    
    public function index($arguments) {
        Layout::set('title', 'Search');
        
        if (empty($_POST['query']))
            return Error::set('No search query found.');
        
        $query = substr(trim(htmlentities($_POST['query'], ENT_QUOTES, 'ISO8859-1', false)), 0, 250);
        $results = Search::query($query);
        
        if ($results['hits']['total'] == 0)
            return Error::set('No results found.');

        $this->view['results'] = array();
        
        $news = new news(ConnectionFactory::get('mongo'));
        $articles = new articles(ConnectionFactory::get('mongo'));
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        
        
        $i = 1;
        foreach ($results['hits']['hits'] as $result) {
            $entry = $result['_source'];
            
            switch ($entry['type']) {
                case 'news':
                    $post = $news->get($result['_id'], false, true);
                    if (empty($post)) continue;
                    $post['type'] = 'news';
                    
                    array_push($this->view['results'], $post);
                    break;
                
                case 'article':
                    $article = $articles->get($result['_id'], false, true);
                    if (empty($article)) continue;
                    $article['type'] = 'article';
                    
                    array_push($this->view['results'], $article);
                    break;
                
                case 'lecture':
                    $lecture = $lectures->get($result['_id'], false, true);
                    if (empty($lecture)) continue;
                    $lecture['type'] = 'lecture';
                    
                    array_push($this->view['results'], $lecture);
                    break;
            }
            
            if ($i == 5) break;
            ++$i;
        }
    }
    
}
