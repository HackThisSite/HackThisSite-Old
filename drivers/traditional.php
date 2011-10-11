<?php
class traditional_view_driver
{
	public function parse($view, $data, $widget)
	{
		if (!$widget) require $GLOBALS['maind'].'application/layouts/'.$GLOBALS['config']['layout'] . '.php';
		$template = new $GLOBALS['config']['layout']();
		
		if (apc_exists(genKey('all')) || apc_exists(genKey('unique'))) {
			$apc = apc_fetch(genKey('all'));
			if ($apc == false) $apc = apc_fetch(genKey('unique'));
			
			if ($apc['what'] == 'v') {
				$parsed = $apc['data'];
			} else {
				$parsed = $this->view($view, $data, $template);
			}
		} else {
			$parsed = $this->view($view, $data, $template);
		}
		
		// Cache
		if (!empty($GLOBALS['cache']) && $GLOBALS['cacheData']['what'] == 'v')
			$GLOBALS['cacheData']['data'] = $parsed;
		
		if ($widget) return $parsed;
		
        return $template->template($parsed);
	}
	
	private function view($view, $data, $template) {
		// localize all the view variables.
        extract($data);

		// Start capturing a new output buffer, load the view
		// and apply all the display logic to the localized data
		// and save the results in $parsed.
		ob_start();
		require $view;
		$parsed = ob_get_clean();
		return $parsed;
	}
}
