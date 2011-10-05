<?php
class News {
	
	var $db;
	
	public function __construct() {
		extract($GLOBALS['config']['mongo']);
		$mongo = new Mongo($ip);
		$this->db = $mongo->$db->content;
	}
	
	public function getNewPosts($cache = true) {
		if ($cache && apc_exists('top_news')) return apc_fetch('top_news');
		
		$news = $this->realGetNewPosts();
		if ($cache && !empty($news)) apc_add('top_news', $news, 10);
		return $news;
	}
	
	public function getNews($id, $cache = true) {
		if ($cache && apc_exists('news_' . $id)) return apc_fetch('news_' . $id);
		
		$news = $this->realGetNews($id);
		if ($cache && !empty($news)) apc_add('news_' . $id, $news, 10);
		return $news;
	}
	
	public function realGetNewPosts() {
		$query = array('type' => 'news', 'ghosted' => false);
		$results = $this->db->find($query)->sort(array('date' => -1))->limit(10);
		return iterator_to_array($results);
	}
	
	public function realGetNews($id) {
		$idLib = new Id;
		
		$query = array('type' => 'news', 'ghosted' => false);
		$query = array_merge($idLib->dissectKeys($id, 'news'), $query);
		$results = $this->db->find($query);
		
		foreach ($results as $result) {
			if (!$idLib->validateHash($id, array('id' => (string) $result['_id'], 'date' => $result['date']), 'news'))
				continue;
			
			return $result;
		}
	}
	
}
