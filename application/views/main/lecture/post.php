<?php if (!empty($valid) && $valid): ?>
<h2><u>Post Lecture</u></h2>
<form action="<?php echo Url::format('lecture/post/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" /><br />
    <b>Lecturer:  </b> <input type="text" name="lecturer" /><br />
    <b>Description:  </b><br />
    <textarea name="description"></textarea><br />
    <b>Date:  </b> <input type="text" name="date" /> <sub><i>(Culturally acceptable date format: this Saturday, 12:30am)</i></sub><br />
    <b>Expected Duration:  </b> <input type="text" name="duration" /><sub><i>(Duration in units:  3 hours, 30 minutes)</i></sub><br />
    <input type="submit" name="submit" value="Post Lecture" />
</form>
<?php endif; ?>
