<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Statistics</h1></div>

<legend>Cache</legend>
<table class="table table-bordered table-rounded">
    <tr>
        <th style="width: 20%">No. of Keys</th>
        <td><?php echo $apcNoKeys; ?></td>
    </tr>
    <tr>
        <th>Total Size</th>
        <td><?php echo round($apcSize / 1024, 1); ?> Kb</td>
    </tr>
</table>

<legend>Redis</legend>
<table class="table table-bordered table-rounded">
    <tr>
        <th style="width: 20%">Version</th>
        <td><?php echo $redisVersion; ?></td>
    </tr>
    <tr>
        <th>Save In Progress?</th>
        <td><?php echo ($redisSIP == 1 ? 'Yes' : 'No'); ?></td>
    </tr>
    <tr>
        <th>No. of Channels</th>
        <td><?php echo $redisNoChans; ?></td>
    </tr>
    <tr>
        <th>Used Memory</th>
        <td><?php echo round($redisMem / 1024, 1); ?> Kb</td>
    </tr>
    <tr>
        <th>Last Save Time</th>
        <td><?php echo Date::minuteFormat($redisLastSave); ?></td>
    </tr>
</table>
<?php endif; ?>
