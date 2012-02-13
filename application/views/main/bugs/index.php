<?php if (CheckAcl::can('postBugs')): ?>
<a href="<?php echo Url::format('/bugs/post'); ?>">Submit Bug</a> || 
<?php endif; ?>
Show 
<a href="<?php echo Url::format('/bugs/index/all'); ?>">All</a> - 
<a href="<?php echo Url::format('/bugs/index/open'); ?>">Open</a> - 
<a href="<?php echo Url::format('/bugs/index/unclosed'); ?>">Unclosed</a> - 
<a href="<?php echo Url::format('/bugs/index/unresolved'); ?>">Unresolved</a> - 
<a href="<?php echo Url::format('/bugs/index/new'); ?>">New</a> - 
<a href="<?php echo Url::format('/bugs/index/sysadmin'); ?>">Sysadmin</a>  Bugs<br />
<?php
$array = array();

for ($i = 1;$i <= $pages;++$i) {
    if ($i == $page) {
        array_push($array, $i);
    } else {
        array_push($array, '<a href="' . Url::format('/bugs/index/' . $filter . '/' . $i) . '">' . $i . '</a>');
    }
}

echo implode(' - ', $array);
?><br />

<?php
// Copied into view.php
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
<center><table border="0" width="90%">
    <tr>
        <th width="5%">F</th>
        <th width="30%">Category</th>
        <th width="15">Updated</th>
        <th width="20%">Status</th>
        <th width="30%">Title</th>
    </tr>
<?php foreach($bugs as $bug): if (!bugs::canView($bug)) continue;?>
    <tr style="background-color: <?php echo $colors[$bug['status']]; ?>">
        <td><?php echo ($bug['flagged'] ? 'F' : ''); ?></td>
        <td><?php echo ucwords(bugs::$category[$bug['category']]); ?></td>
        <td><?php echo Date::dayFormat($bug['lastUpdate']); ?></td>
        <td><?php echo ucwords(bugs::$status[$bug['status']]); ?></td>
        <td>
            <a href="<?php echo Url::format('/bugs/view/' . Id::create($bug, 'bug')); ?>">
                <?php echo $bug['title']; ?>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
</table></center>
