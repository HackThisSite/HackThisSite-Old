<h3 style="padding-bottom: 0px;margin-bottom: 0px;"><?php echo $title; ?></h3>
<sub>Posted by: <?php echo $user['username']; ?> on <?php echo Date::dayFormat($date); ?>
<?php if (!empty($department)): ?> from the <?php echo $department; ?> dept<?php endif; ?>
<?php if (CheckAcl::can('editNews')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/edit/' . $_id); ?>">Edit</a><?php endif; ?>
<?php if (CheckAcl::can('deleteNews')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/news/delete/' . $_id); ?>">Delete</a><?php endif; ?></sub>
<p><?php echo wordwrap($body, 150, "<br />\n", true); ?></p>
<hr />
<?php /*
<?php if (!empty($mlt)): ?>
<b><u>More Like This:</u></b><br />
<?php
foreach ($mlt as $fetched) {
    echo '<a href="' . Url::format('news/view/' . Id::create($fetched, 'news')) . '">' . $fetched['title'] . '</a><br />';
}
?>
<hr />
<?php endif; ?>
*/
