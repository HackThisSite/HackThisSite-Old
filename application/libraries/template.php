<?php
class Template extends CI_Controller {
	
	public function show($view, $data = array()) {
		//$parent = parent::$this;
		$this->load->config('config');
		$template = $this->config->item('defaultTemplate');
		
		$this->load->view($template . '_header');
		$this->load->view('views/' . $view, $data);
		$this->load->view($template . '_footer');
	}

}
