<?php
if (!isset($bad) || (isset($bad) && !$bad)):
?>
<h1>Administrator Panel</h1>

<ol>
	<li><a href="<?php echo $GLOBALS['config']['baseUrl']; ?>admin/data">Data Store Statistics</a></li>
	<li><a href="<?php echo $GLOBALS['config']['baseUrl']; ?>admin/navigation">Navigation Management</a></li>
	<li><a href="<?php echo $GLOBALS['config']['baseUrl']; ?>admin/access">Access Management</a></li>
	<li><a href="<?php echo $GLOBALS['config']['baseUrl']; ?>admin/post_news">Post News</a></li>
</ol>
<?php
endif;
