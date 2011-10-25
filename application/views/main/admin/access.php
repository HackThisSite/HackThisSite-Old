<?php
if ($mode == 'list') {
?>
<h1>Access Management</h1>

<?php if (!empty($saved) && $saved) echo '<center><b>Entry Saved.</b></center>'; ?>
<ul>
<?php
	foreach ($permissions as $name => $groups) {
?>
	<li><?php echo ucwords($name); ?>:&nbsp;&nbsp;<?php
		$array = explode(',', $groups);

		end($array);
		$lastKey = key($array);
		$can = $GLOBALS['permissions']->check('accessEdit');

		if (empty($groups)) {
			echo '<b>Nobody</b>';
		}

		foreach ($array as $key => $group) {
			echo ucwords(str_replace('_', ' ', $group)), ($key != $lastKey ? ', ' : '');
		}

		if ($can)
			echo '&nbsp;&nbsp;<a href="' . Config::get("other:baseUrl") . 'admin/access/edit/' . $name . '">(edit)</a>';

?>
</li>
<?php
	}
?>
</ul>
<?php
} else if ($mode == 'edit') {
?>
<h1>Edit Access</h1>

<b><u><?php echo $id; ?> Access:</u></b><br />

<form action="<?php echo Config::get("other:baseUrl"); ?>admin/access/save/<?php echo $id; ?>" method="post">
	<select name="access[]" multiple="multiple">
<?php
	$data = Data::singleton();
	$list = explode(',', $permissions[$id]);
	$info = $data->query('SELECT group_name FROM ' . Config::get("forums:prefix") . 'groups WHERE 1 = 1');

	foreach ($info['rows'] as $row) {
		$name = strtolower($row['group_name']);
?>
		<option value="<?php echo $name; ?>"<?php echo ($permissions[$id] == 'all' || in_array($name, $list) ? ' selected="selected"' : '') ?>><?php echo ucwords(str_replace('_', ' ', $name)); ?></option>
<?php
	}
?>
	</select><br />

	<input type="submit" name="submit" value="Save" />&nbsp;&nbsp;<a href="<?php echo Config::get("other:baseUrl"); ?>admin/access">Cancel</a>
</form>
<?php
}
?>
