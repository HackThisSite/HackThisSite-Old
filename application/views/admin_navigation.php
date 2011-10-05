<?php
if (!isset($bad) || (isset($bad) && !$bad)):
	if (!empty($saved) && $saved) echo '<center><b>Entry Saved.</b></center><br />';
	if (!empty($removed) && $removed) echo '<center><b>Entry Removed.</b></center><br />';
	
	if ($mode == 'new') {
?>
<h1>New Naviation Entry</h1>
<?php
		echo $template->navigationNew();
	} else if ($mode == 'edit') {
?>
<h1>Edit Navigation Entry</h1>
<?php
		echo $template->navigationEdit($info['score'], unserialize($info['serialized']));
	} else {
?>
<a href="<?php echo $GLOBALS['config']['baseUrl']; ?>admin/navigation/new">New Entry</a><br />
<?php
		echo $template->simpleTableStart();
		
		$id = 0;
		foreach ($navigation as $entry => $score) {
			$entry = unserialize($entry);
			$style = $template->styleAdminNav($entry, $score, $id);
			echo $template->simpleTableRow($style, ($entry['type'] == 0 ? 'dark' : 'light'));
			
			++$id;
		}
		
		echo $template->simpleTableEnd();
	}
	
endif;
