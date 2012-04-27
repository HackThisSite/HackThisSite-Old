<div class="well">
    <h1><?php echo $title; ?></h1>
    <small>By <?php echo $lecturer; ?>
<?php
$access = array();
if (CheckAcl::can('editLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/edit/' . $_id) . '">Edit</a>');
}

if (CheckAcl::can('deleteLectures')) {
    array_push($access, '<a href="' . Url::format('lecture/delete/' . $_id) . '">Delete</a>');
}


if (!empty($access)) {
    echo ' - ' . implode(' - ', $access);
}
?>
</small>

    <p><?php echo BBCode::parse($description); ?></p>
    <em><?php echo Date::minuteFormat($time); ?> to 
    <?php echo Date::minuteFormat($time + $duration); ?></em>
</div>
