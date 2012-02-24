You finished <?php echo $current; ?>!<br />
<?php if (!empty($next)): ?><a href="<?php echo Url::format('missions/' . $next); ?>">Next</a><?php endif; ?>

<?php Mission::finishMission('basic1', $id); ?>
