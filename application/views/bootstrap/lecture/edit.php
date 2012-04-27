<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Edit Lecture</h1></div>
<form class="well form-horizontal" action="<?php echo Url::format('lecture/edit/' . $post['_id'] . '/save'); ?>" method="post">
    <label>Title:  </label>
    <input type="text" name="title" value="<?php echo $post['title']; ?>" /><br />
    
    <label>Lecturer:  </label> 
    <input type="text" name="lecturer" value="<?php echo $post['lecturer']; ?>" /><br />
    
    <label>Description:  </label>
    <textarea name="description"><?php echo $post['description']; ?></textarea><br />
    
    <label>Date:  </label> 
    <input type="text" name="date" value="<?php echo Date::computerFormat($post['time']); ?>" /> 
    <span class="help-inline">(Culturally acceptable date format: this Saturday, 12:30am)</span><br />
    
    <label>Expected Duration:  </label>
    <input type="text" name="duration" value="<?php echo Date::durationFormat($post['duration']); ?>" />
    <span class="help-inline">(Duration in units:  3 hours, 30 minutes)</span><br />
    
    <input type="submit" name="submit" value="Edit Lecture" class="btn btn-primary" />
</form>
<?php endif; ?>
