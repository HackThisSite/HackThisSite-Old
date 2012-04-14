<?php if (!empty($valid) && $valid): ?>
<center>
<?php
foreach ($missions as $mission) {
     if ($mission['type'] == 'basic') {
?>
<div class="well" style="width: 40%">
	<b><a href="<?php echo Url::format('missions/' . $mission['type'] . '/' . $mission['sort']); ?>"><?php echo $mission['name']; ?></a></b><br />
	<p><?php echo nl2br(htmlentities($mission['description'], ENT_QUOTES, 'ISO8859-15', false)); ?></p>
	<?php if (!empty($mission['helpLink'])): ?>
	<br /><a href="<?php echo Url::format($mission['helpLink']); ?>">(Get Help)</a>
	<?php endif; ?>
</div>
<?php
     }
}
?>
</center>
<?php endif; ?>
