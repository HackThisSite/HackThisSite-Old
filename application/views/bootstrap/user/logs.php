<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Account Activity</h1></div>

<div class="row">
    <div class="span4"><div class="well">
<legend>Logins</legend>

<dl>
<?php foreach ($logins as $login): ?>
    <dt>Logged in on: <?php echo Date::minuteFormat($login['time']); ?></dt>
    <dd>
        <em>(<?php echo Date::durationFormat(time() - $login['time']); ?> ago)</em><br />
        <em>I.P. Address:</em>  <?php echo $login['ipAddress']; ?><br />
        <em>User Agent:</em>  <?php echo $login['userAgent']; ?><br /><br /></dd>
<?php endforeach; ?>
</dl>
    </div></div>
    <div class="span5"><div class="well">
<?php foreach ($activity as $entry): ?>
    <em><?php echo $entry['message']; ?></em>
<?php if (empty($entry['reference'])): ?>
    <em class="pull-right">(No ref.)</em>
<?php else: ?>
    <a class="pull-right" href="<?php echo Url::format($entry['reference']); ?>">Reference</a>
<?php endif; ?>
    <br />
<?php endforeach; ?>
    </div></div>
</div>
<?php endif; ?>
