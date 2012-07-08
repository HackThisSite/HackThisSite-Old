<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1><?php echo $name; ?></h1></div>

<?php
echo call_user_func(array($basic, 'mission' . $num));
?>
<?php elseif (!empty($good) && $good): ?>
<div class="alert alert-success">
<strong>Well done!</strong>  You've successfully completed basic 
mission <?php echo $num; ?>, <em><?php echo $name; ?></em>
</div>

<div class="hero-unit">
    <h1>Congratulations!</h1><br />

    <p><?php echo call_user_func(array($basic, 'explainMission' . $num)); ?><br /></p>
    
<?php if ($next): ?>
    <p>
        <a class="btn btn-large btn-success pull-right" href="<?php echo Url::format('missions/basic/' . ($num + 1)); ?>">
            Next Mission!
        </a>
    </p>
<?php endif; ?>
</div>
<?php endif; ?>
