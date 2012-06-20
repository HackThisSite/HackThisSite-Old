<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Submit Bug</h1></div>

<form class="form-horizontal well" action="<?php echo Url::format('/bugs/post/save'); ?>" method="post">
<fieldset>
	<div class="control-group">
		<label class="control-label">Title:</label>
		
		<div class="controls">
			<input type="text" name="title" />
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label">Category:</label>
		
		<div class="controls">
			<select name="category">
<?php foreach (bugs::$category as $key => $cat): ?>
				<option value="<?php echo $key; ?>"><?php echo $cat; ?></option>
<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">Description:</label>
		
		<div class="controls">
			<textarea rows="10" style="width: 100%" name="description"></textarea>
		</div>
	</div>
    
    <div class="control-group">
		<label class="control-label">Steps to Reproduce:</label>
		
		<div class="controls">
			<textarea rows="10" style="width: 100%" name="reproduction"></textarea>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label">Public:</label>
		
		<div class="controls">
			<input type="checkbox" name="public" value="1" checked="checked" />
		</div>
	</div>
    
    <input type="submit" value="Post Bug" class="btn btn-primary" />
</fieldset>
</form>
<?php else: ?>
<a href="<?php echo Url::format('/bugs/view/' . Id::create($info, 'bug')); ?>">Read</a>
<?php endif; ?>
