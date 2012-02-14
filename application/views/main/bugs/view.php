<?php if (!empty($valid) && $valid): ?>
<?php
// Copied into index.php
$colors = array(
    '#ffa0a0',
    '#ff50a8',
    '#ffd850',
    '#ffffb0',
    '#c8c8ff',
    '#cceedd',
    '#e8e8e8',
    '#C0C0C0',
    '#cceedd'
);
?>
<h3 style="margin: 0;padding: 0;"><u>View Bug</u></h3>
<div style="margin: 0;padding: 0;float: right">
    <form action="<?php echo Url::format('bugs/changeStatus'); ?>" method="post">
        Change Status:  
        <select name="status">
<?php foreach (bugs::$status as $status): ?>
            <option value="<?php echo $status; ?>"><?php echo ucwords($status); ?></option>
<?php endforeach; ?>
            <option value="public">Public</option>
            <option value="private">Private</option>
            <option value="delete">Delete</option>
        </select>
        <input type="hidden" name="id" value="<?php echo $bug['_id']; ?>" />
        <input type="submit" name="submit" value="Go" />
    </form>
</div>
<center><table width="90%" border="1">
    <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Date Submitted</th>
        <th>Last Update</th>
    </tr>
    <tr>
        <td><?php echo Id::create($bug, 'bug'); ?></td>
        <td><?php echo ucwords(bugs::$category[$bug['category']]); ?></td>
        <td><?php echo Date::dayFormat($bug['created']); ?></td>
        <td><?php echo Date::dayFormat($bug['lastUpdate']); ?></td>
    </tr>
    <tr>
        <th>Reporter</th>
        <td><?php echo $bug['username']; ?></td>
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
</table></center>
<br /><hr /><br />
<?php echo Partial::render('comment', array('id' => $bug['_id'], 'page' => 1)); ?>
<?php endif; ?>
