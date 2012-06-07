<?php if (!empty($valid) && $valid): ?>

<?php endif; ?>
<?php if (CheckAcl::can('haveConnections')): ?>
<form class="form-horizontal well" action="<?php echo Url::format('/user/connections'); ?>" method="post">
    <legend>Manage Connections</legend>
    <div class="control-group">
        <label class="control-label">GitHub Username</label>
        
        <div class="controls">
            <input type="text" name="github"<?php echo (!empty($github) ? 'value="' . $github . '"' : ''); ?> />
        </div>
    </div>
    
    <input type="submit" value="Save" class="btn btn-primary" />
</form>
<?php endif; ?>
