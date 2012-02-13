<h3 style="padding-bottom: 0px;margin-bottom: 0px;"><?php echo $title; ?></h3>
<sub>Posted by: <?php echo $user['username']; ?> on <?php echo Date::dayFormat($date); ?>
<?php if (CheckAcl::can('editArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/edit/' . $_id); ?>">Edit</a><?php endif; ?>
<?php if (CheckAcl::can('deleteArticle')): ?>&nbsp;-&nbsp;<a href="<?php echo Url::format('/article/delete/' . $_id); ?>">Delete</a><?php endif; ?></sub>
<p><?php echo $body; ?></p>
<hr />
