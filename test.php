<?php
$start = microtime(true);
$iterator = new APCIterator('user');
$count = $iterator->getTotalSize();
$end = microtime(true);
echo $end - $start, "\n", $count, "\n";

sleep(1);
while ($iterator->valid()) {
	print_r($iterator->current());
	$iterator->next();
	sleep(1);
}
?>
