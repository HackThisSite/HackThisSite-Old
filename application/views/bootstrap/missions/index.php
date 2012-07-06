<?php if (!empty($valid) && $valid): ?>
<center>
<?php foreach ($missions as $mission): ?>
<div class="well">
    <center>
        <h3><a href="<?php echo Url::format('missions/' . strtolower($mission['name'])); ?>"><?php echo ucwords($mission['name']); ?> Missions</a></h3>
        <p><?php echo $mission['description']; ?></p>
    </center>
</div>

<?php endforeach; ?>
</center>
<?php endif; ?>
