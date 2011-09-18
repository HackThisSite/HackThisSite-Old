<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {
	
	public function index()
	{
		$data = array();
		
		$this->load->model('News', 'News');
		$data['news'] = $this->News->getNewPosts();
		$this->template->show('home', $data);
	}
}
