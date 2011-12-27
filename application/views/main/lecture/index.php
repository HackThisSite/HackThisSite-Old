<?php if (!empty($valid) && $valid):
foreach ($lectures as $lecture): ?>
<table border="1" style="width: 90%">
    <tr><td><b><u><?php echo $lecture['title']; ?></u></b> by <?php echo $lecture['lecturer']; ?><?php
$access = array();
if (CheckAcl::can('editLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/edit/' . $lecture['_id']) . '">Edit</a>');
}

if (CheckAcl::can('deleteLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/delete/' . $lecture['_id']) . '">Delete</a>');
}


if (!empty($access)) {
    echo '<span style="float: right;">' . implode(' - ', $access) . '</span>';
}
?></td></tr>
    <tr><td><p><?php echo $lecture['description']; ?></p><br />
    <sub><?php echo Date::minuteFormat($lecture['time']); ?> to 
    <?php echo Date::minuteFormat($lecture['time'] + $lecture['duration']); ?></sub></td></tr>
</table>
<?php endforeach;
endif; ?>
