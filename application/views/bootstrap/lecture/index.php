<?php 
if (!empty($valid) && $valid) {
    foreach ($lectures as $lecture) {
        echo Partial::render('lecture', $lecture);
    }
}
?>
