<?php
class Template {
	
	public function show($view, $data = array()) {
		$CI =& get_instance();
		
		$template = $CI->config->item('defaultTemplate');
		
		$CI->load->view($template . '_header');
		$CI->load->view('views/' . $view, $data);
		$CI->load->view($template . '_footer');
	}

}
