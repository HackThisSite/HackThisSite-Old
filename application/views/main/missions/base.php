<?php if (!empty($valid) && $valid): ?>
<center>
<?php
foreach ($missions as $mission) {
     if ($mission['type'] == 'basic') {
?>
<table border="1" style="width: 50%">
    <tr><td><center>
        <b><a href="<?php echo Url::format('missions/' . $mission['type'] . '/' . $mission['sort']); ?>"><?php echo $mission['name']; ?></a></b><br />
        <p><?php echo $mission['description']; ?></p>
        <?php if (!empty($mission['helpLink'])): ?>
        <br /><a href="<?php echo Url::format($mission['helpLink']); ?>">(Get Help)</a>
        <?php endif; ?>
    </center></td></tr>
</table><br /><br />
<?php
     }
}
?>
</center>
<?php endif; ?>
