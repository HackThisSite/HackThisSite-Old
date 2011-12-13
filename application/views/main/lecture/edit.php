<?php if (!empty($valid) && $valid): ?>
<h2><u>Edit Lecture</u></h2>
<form action="<?php echo Url::format('lecture/edit/' . $post['_id'] . '/save'); ?>" method="post">
    <b>Title:  </b> <input type="text" name="title" value="<?php echo $post['title']; ?>" /><br />
    <b>Lecturer:  </b> <input type="text" name="lecturer" value="<?php echo $post['lecturer']; ?>" /><br />
    <b>Description:  </b><br />
    <textarea name="description"><?php echo $post['description']; ?></textarea><br />
    <b>Date:  </b> <input type="text" name="date" value="<?php echo Date::computerFormat($post['time']); ?>" /> <sub><i>(Culturally acceptable date format: this Saturday, 12:30am)</i></sub><br />
    <b>Expected Duration:  </b> <input type="text" name="duration" value="<?php echo Date::durationFormat($post['duration']); ?>" /><sub><i>(Duration in units:  3 hours, 30 minutes)</i></sub><br />
    <input type="submit" name="submit" value="Edit Lecture" />
</form>
<?php endif; ?>
