<?php
if (!isset($bad) || (isset($bad) && !$bad)):
?>
<center><h1>Data Store Statistics</h1></center>

<h2>APC</h2>

<b>Object Count: </b> <?php echo $count; ?><br />
<b>Total Cache Hits: </b> <?php echo $hits; ?><br />
<b>Total Cache Size: </b> <?php echo $size; ?><br />
<?php
if ($GLOBALS['permissions']->check('apcClear')) {
?>
<br />
<a href="<?php echo Config::get("other:baseUrl"); ?>admin/data/clear">Clear APC</a><br />
<?php
}
?>
<br />

<h2>Redis</h2>

<b>Version: </b> <?php echo $redisVersion; ?><br />
<b>Architecture Bits: </b> <?php echo $arch_bits; ?><br />
<b>Days Up: </b> <?php echo $uptime; ?><br />
<b>Number of Connections: </b> <?php echo $numClients; ?><br />
<b>Changes Since Last Save: </b> <?php echo $changes; ?><br />
<b>Save In Progress?: </b> <?php echo $bgSave; ?><br />
<b>Total Connections Ever: </b> <?php echo $totalConnecRecv; ?><br />
<b>Total Commands Processed: </b> <?php echo $totalCmdsPrcsd; ?><br />
<b>Number of Keys: </b> <?php echo $redisSize; ?><br />
<b>Last Save: </b> <?php echo $lastSave; ?><br />
<?php
if ($GLOBALS['permissions']->check('redisBgSave')) {
?>
<br />
<a href="<?php echo Config::get("other:baseUrl"); ?>admin/data/bgsave">Start Background Save</a><br />
<?php
}
endif;
?>
