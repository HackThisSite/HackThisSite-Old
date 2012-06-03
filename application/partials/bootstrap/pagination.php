<?php
/*
Parameters:
$total     -- Total number of items.
$perPage   -- Total number of items per page.
$page      -- Current page number.
$url       -- Where to redirect.
*/

$pages = ceil($total / $perPage);
?>
<center>
<div class="pagination">
    <ul>
<?php for($i = 1;$i <= $pages;++$i): ?>
        <li<?php if ($page == $i): ?> class="active"<?php endif; ?>><a href="<?php echo Url::format($url . $i) . (!empty($where) ? '#' . $where : ''); ?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
    </ul>
</div></center>
<br />
