<?php if (!empty($_GET['callback'])): ?>
<?php echo $_GET['callback']; ?>(<?php echo json_encode($data); ?>)
<?php
else:
    echo json_encode($data);
endif;
?>
