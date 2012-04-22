<?php if (!empty($valid) && $valid): ?>

<div class="page-header"><h1>View Bug</h1></div>

<form class="form-inline well pull-right" action="<?php echo Url::format('bugs/changeStatus'); ?>" method="post">
	<label>Change Status:</label>
		
	<select name="status">
<?php foreach (bugs::$status as $status): ?>
		<option value="<?php echo $status; ?>"><?php echo ucwords($status); ?></option>
<?php endforeach; ?>
		<option value="public">Public</option>
		<option value="private">Private</option>
		<option value="delete">Delete</option>
	</select>
	<input type="hidden" name="id" value="<?php echo $bug['_id']; ?>" />
	<input type="submit" value="Go" class="btn" />
</form>

<span>
<i title="Reporter" class="icon-user"></i>&nbsp;<?php echo $bug['reporter']['username']; ?><br />
<i title="Dates" class="icon-calendar"></i>&nbsp;Submitted <?php echo Date::dayFormat($bug['created']); ?><br />
<?php if ($bug['public']): ?>
<i title="Public / Private" class="icon-ok-circle"></i>&nbsp;Public<br />
<?php else: ?>
<i title="Public / Private" class="icon-ban-circle"></i>&nbsp;Private<br />
<?php endif; ?>
<i title="Category" class="icon-folder-open"></i>&nbsp;<?php echo ucwords(bugs::$category[$bug['category']]); ?><br />
<i title="Status" class="icon-signal"></i>&nbsp;<?php echo ucwords(bugs::$status[$bug['status']]); ?><br /><br />
</span>

<em>(Last updated <?php echo Date::dayFormat($bug['lastUpdate']); ?>)</em>
<table class="table table-bordered">
	<tbody>
	<tr>
		<th>Title</th>
		<td><span><?php echo $bug['title']; ?></span></td>
	</tr>
	<tr>
		<th>Description</th>
		<td><p><?php echo BBCode::parse($bug['description'], '#'); ?></p></td>
	</tr>
	<tr>
		<th>Steps to Reproduce</th>
		<td><p><?php echo BBCode::parse($bug['reproduction'], '#'); ?></p></td>
	</tr>
	</tbody>
</table>
<!--
<table class="table table-bordered">
	<thead>
    <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Date Submitted</th>
        <th>Last Update</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php echo Id::create($bug, 'bug'); ?></td>
        <td><?php echo ucwords(bugs::$category[$bug['category']]); ?></td>
        <td><?php echo Date::dayFormat($bug['created']); ?></td>
        <td><?php echo Date::dayFormat($bug['lastUpdate']); ?></td>
    </tr>
    <tr>
        <th>Reporter</th>
        <td><?php echo $bug['reporter']['username']; ?></td>
        <th>View Status</th>
        <td style="background-color: <?php echo $colors[$bug['status']]; ?>">
            <?php echo ucwords(bugs::$status[$bug['status']]); ?>
        </td>
    </tr>
    <tr>
        <th>Public</th>
        <td><?php var_export($bug['public']); ?></td>
    </tr>
    <tr>
        <th>Title</th>
        <td colspan="3"><?php echo $bug['title']; ?></td>
    </tr>
    <tr>
        <th>Description</th>
        <td colspan="3"><?php echo wordwrap(BBCode::parse($bug['description'], '#'), 150, "<br />\n", true); ?></td>
    </tr>
    <tr>
        <th>Steps to Reproduce</th>
        <td colspan="3"><?php echo wordwrap(BBCode::parse($bug['reproduction'], '#'), 150, "<br />\n", true); ?></td>
    </tr>
    </tbody>
</table>
-->
<hr />
<?php echo Partial::render('comment', array('id' => $bug['_id'], 'page' => 1)); ?>
<?php endif; ?>
