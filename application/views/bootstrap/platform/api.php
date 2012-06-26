<?php
$data['errors'] = Error::getAllErrors();
echo json_encode($data);
?>
