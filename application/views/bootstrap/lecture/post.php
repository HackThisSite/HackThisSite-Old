<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Post Lecture</h1></div>
<form class="form-vertical well" action="<?php echo Url::format('lecture/post/save'); ?>" method="post">
    <label>Title:  </label>
    <input type="text" name="title" /><br />
    
    <label>Lecturer:  </label>
    <input type="text" name="lecturer" /><br />
    
    <label>Description:</label>
    <textarea name="description"></textarea><br />
    
    <label>Date:  </label>
    <input type="text" name="date" /> 
    <span class="help-inline">(Culturally acceptable date format: this Saturday, 12:30am)</span><br />
    
    <label>Expected Duration:  </label>
    <input type="text" name="duration" />
    <span class="help-inline">(Duration in units:  3 hours, 30 minutes)</span><br />
    
    <input type="submit" value="Post Lecture" class="btn btn-primary" />
</form>
<?php endif; ?>
