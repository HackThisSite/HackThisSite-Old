<table border="1" style="width: 90%">
    <tr><td><b><u><?php echo $title; ?></u></b> by <?php echo $lecturer; ?><?php
$access = array();
if (CheckAcl::can('editLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/edit/' . $_id) . '">Edit</a>');
}

if (CheckAcl::can('deleteLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/delete/' . $_id) . '">Delete</a>');
}


if (!empty($access)) {
    echo '<span style="float: right;">' . implode(' - ', $access) . '</span>';
}
?></td></tr>
    <tr><td><p><?php echo $description; ?></p><br />
    <sub><?php echo Date::minuteFormat($time); ?> to 
    <?php echo Date::minuteFormat($time + $duration); ?></sub></td></tr>
</table>
