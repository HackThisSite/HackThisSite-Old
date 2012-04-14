<?php if ($valid): ?>
<div class="page-header"><h1>Current Notices:</h1></div>

<?php if (CheckAcl::can('postNotice')): ?>
<form class="form-inline well" action="<?php echo Url::format('/notice/post/save'); ?>" method="post">
    <strong>New Notice:  </strong> <input type="text" name="text" />&nbsp;
    <input type="submit" value="Post Notice" class="btn btn-primary" />
</form><br />
<?php endif; ?>

<?php if (!empty($notices)): ?>
<table class="table table-striped table-bordered">
	<thead><tr>
        <th style="width: 80%">Text</th>
        <th colspan="2">Actions</th>
    </tr></thead>
    <tbody>
<?php foreach ($notices as $id => $notice): ?>
    <tr>
        <td><?php echo $notice; ?></td>
        <td>
<?php if (CheckAcl::can('editNotice')): ?>
            <a href="<?php echo Url::format('/notice/edit/' . ($id + 1)); ?>" class="btn btn-warning">Edit</a><br />
<?php endif; ?>
		</td>
		<td>
<?php if (CheckAcl::can('deleteNotice')): ?>
            <a href="<?php echo Url::format('/notice/delete/' . ($id + 1)); ?>" class="btn btn-danger">Delete</a>
<?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?>
<center><span>No notices</span></center>
<?php endif;endif; ?>
