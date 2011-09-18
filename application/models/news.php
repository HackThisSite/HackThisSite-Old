<?php
class News extends CI_Model {
	
	var $db;
	
	public function __construct() {
		parent::__construct();
		
		$ip = $this->config->item('mongo');
		$db = $this->config->item('mongoDb');
		
		$mongo = new Mongo($ip);
		$this->db = $mongo->$db->content;
	}
	
	public function getNewPosts() {
		$query = array('type' => 'news', 'ghosted' => false);
		$results = $this->db->find($query)->sort(array('date' => -1))->limit(10);
		return iterator_to_array($results);
	}
	
}
