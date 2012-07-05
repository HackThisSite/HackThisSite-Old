<div class="page-header"><h1>Submitted Bugs</h1></div>

<div class="btn-toolbar">
<?php if (CheckAcl::can('postBugs')): ?>
	<div class="btn-group">
		<a href="<?php echo Url::format('/bugs/post'); ?>" class="btn btn-primary">Submit Bug</a>
	</div>
<?php endif; ?>

	<div class="btn-group">
		<a href="<?php echo Url::format('/bugs/index/all'); ?>" class="btn<?php echo ($filter == 'all' ? ' disabled' : ''); ?>">All</a>
		<a href="<?php echo Url::format('/bugs/index/open'); ?>" class="btn<?php echo ($filter == 'open' ? ' disabled' : ''); ?>">Open</a>
		<a href="<?php echo Url::format('/bugs/index/unclosed'); ?>" class="btn<?php echo ($filter == 'unclosed' ? ' disabled' : ''); ?>">Unclosed</a>
		<a href="<?php echo Url::format('/bugs/index/unresolved'); ?>" class="btn<?php echo ($filter == 'unresolved' ? ' disabled' : ''); ?>">Unresolved</a>
		<a href="<?php echo Url::format('/bugs/index/new'); ?>" class="btn<?php echo ($filter == 'new' ? ' disabled' : ''); ?>">New</a>
		<a href="<?php echo Url::format('/bugs/index/sysadmin'); ?>" class="btn<?php echo ($filter == 'sysadmin' ? ' disabled' : ''); ?>">Sysadmin</a>
	</div>

	<div class="btn-group">
<?php
$array = array();

for ($i = 1;$i <= $pages;++$i) {
        array_push($array, '<a href="' . Url::format('/bugs/index/' . $filter . '/' . $i) . '" class="btn' . ($i == $page ? ' disabled' : '') . '">' . $i . '</a>');
}

echo implode($array);
?>
	</div>
</div>

<?php if (!empty($bugs)): ?>
<table class="table table-striped table-bordered table-condensed">
	<thead>
    <tr>
        <th style="width: 1%">&nbsp;</th>
        <th style="width: 5%">Category</th>
        <th style="width: 20%">Updated</th>
        <th style="width: 5%">Status</th>
        <th>Title</th>
    </tr>
    </thead>
    <tbody>
<?php foreach($bugs as $bug): if (!bugs::canView($bug)) continue; ?>
    <tr>
        <td><?php echo ($bug['flagged'] ? '<i class="icon-exclamation-sign"></i>' : '&nbsp;'); ?></td>
        <td><?php echo ucwords(bugs::$category[$bug['category']]); ?></td>
        <td><?php echo Date::dayFormat($bug['lastUpdate']); ?></td>
        <td><?php echo ucwords(bugs::$status[$bug['status']]); ?></td>
        <td>
            <a href="<?php echo Url::format('/bugs/view/' . Id::create($bug, 'bugs')); ?>">
                <?php echo $bug['title']; ?>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
