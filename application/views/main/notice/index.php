<?php if ($valid): ?>
<h3>Current Notices:</h3>

<?php if (CheckAcl::can('postNotice')): ?>
<u>Post New Notice:</u><br />
<form action="<?php echo Url::format('/notice/post/save'); ?>" method="post">
    <b>Text:  </b> <input type="text" name="text" />&nbsp;
    <input type="submit" name="submit" value="Post Notice" />
</form><br />
<?php endif; ?>

<?php if (!empty($notices)): ?>
<table border="1" style="width: 100%">
    <tr>
        <th style="width: 80%">Text</th>
        <th>-</th>
    </tr>
<?php foreach ($notices as $id => $notice): ?>
    <tr>
        <td><?php echo $notice; ?></td>
        <td><center>
<?php if (CheckAcl::can('editNotice')): ?>
            <a href="<?php echo Url::format('/notice/edit/' . ($id + 1)); ?>">Edit</a><br />
<?php endif; 
if (CheckAcl::can('deleteNotice')): ?>
            <a href="<?php echo Url::format('/notice/delete/' . ($id + 1)); ?>">Delete</a>
<?php endif; ?>
        </center></td>
    </tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<center><span>No notices</span></center>
<?php endif;endif; ?>
