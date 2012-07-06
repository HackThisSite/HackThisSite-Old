<?php if (!empty($valid) && $valid): ?>
<?php
foreach ($missions as $mission) {
     if ($mission['type'] == 'basic') {
?>
<div class="well" style="text-align: center;width: 40%">
    <h3><a href="<?php echo Url::format('missions/' . $mission['type'] . '/' . $mission['sort']); ?>"><?php echo ucwords($mission['type']); ?> Mission <?php echo $mission['sort']; ?></a></h3>
	<i><?php echo $mission['name']; ?></i>
</div>
<?php
     }
}
?>
<?php endif; ?>
