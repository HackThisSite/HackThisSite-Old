<center><h1>Data Store Statistics</h1></center>

<h2>APC</h2>

<b>Object Count: </b> <?php echo $count; ?><br />
<b>Total Cache Hits: </b> <?php echo $hits; ?><br />
<b>Total Cache Size: </b> <?php echo $size; ?><br />
<br />
<a href="<?php echo Config::get("other:baseUrl"); ?>admin/data/clear">Clear APC</a><br />
<br />
