<?php if (!empty($valid) && $valid): ?>
<div class="page-header"><h1>Manage IRC Links</h1></div>

<div style="width: 49%;" class="pull-left">
	<center><h2>Your Nicks</h2></center>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Nick</th>
			<th style="width: 1%">Actions</th>
		</tr>
	</thead>
	<tbody>
<?php if (!empty($nicks)): ?>
<?php foreach($nicks as $key => $nick): ?>
		<tr>
			<td><?php echo $nick; ?></td>
			<td><a href="<?php echo Url::format('/user/link/delA/' . $key); ?>" class="btn btn-danger">Delete</a></td>
		</tr>
<?php endforeach; ?>
<?php else: ?>
		<tr>
			<td colspan="2">
				<div class="alert alert-error">No accepted nicks.</div>
			</td>
		</tr>
<?php endif; ?>
	</tbody>
</table></div>


<div style="width: 49%;" class="pull-right">
	<center><h2>Pending Nicks</h2></center>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Nick</th>
			<th style="width: 1%" colspan="2">Actions</th>
		</tr>
	</thead>
	<tbody>
<?php if (!empty($pending)): ?>
<?php foreach($pending as $key => $nick): ?>
		<tr>
			<td><?php echo $nick; ?></td>
			<td><a href="<?php echo Url::format('/user/link/add/' . $key); ?>" class="btn btn-success">Link</a></td>
			<td><a href="<?php echo Url::format('/user/link/delP/' . $key); ?>" class="btn btn-danger">Delete</a></td>
		</tr>
<?php endforeach; ?>
<?php else: ?>
		<tr>
			<td colspan="3">
				<div class="alert alert-error">No pending nicks.</div>
			</td>
		</tr>
<?php endif; ?>
	</tbody>
</table></div>
<?php endif; ?>
