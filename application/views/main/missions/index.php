<?php if (!empty($valid) && $valid): ?>
<center>
<?php foreach ($missions as $mission): ?>
    <table border="1" style="width: 50%">
        <tr><td>
            <center><b><u><a href="<?php echo Url::format('missions/' . strtolower($mission['name'])); ?>"><?php echo $mission['name']; ?> Missions</a></u></b><br />
            <p><?php echo $mission['description']; ?></p></center>
        </td></tr>
    </table><br /><br />

<?php endforeach; ?>
</center>
<?php endif; ?>
