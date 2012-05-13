<?php if (!empty($valid) && $valid): ?>

<?php endif; ?>
<?php if (CheckAcl::can('haveConnections')): ?>
<form class="form-horizontal well" action="<?php echo Url::format('/user/connections/save'); ?>" method="post">
    <legend>Manage Connections</legend>
    <div class="control-group">
        <label class="control-label">GitHub Username</label>
        
        <div class="controls">
            <input type="text" name="github" />
        </div>
    </div>
</form>
<?php endif; ?>
