<?php
if (Session::isLoggedIn()) {
    echo 'Hello, ', Session::getVar('username'), '!';
} else {
    echo 'Hello stranger!';
}
?>
<br />
<br />
<?php
foreach($news as $post):
echo Partial::render('newsShort', $post);
endforeach;
?>
<br />
<div class="well">
	<strong>Most Recent Users:</strong><br />
<?php 
$links = array();
foreach ($onlineUsers as $user) {
	array_push($links, '<a href="' . Url::format('/user/view/' . $user) . '">' . $user . '</a>');
}
echo implode('&nbsp;-&nbsp;', $links);
?><br />
	
	<strong>Users on IRC: (<?php echo $ircOnline['unknown']; ?> unknown users)</strong><br />
<?php
$links = array();
if (!empty($ircOnline['usernames'])) {
	foreach ($ircOnline['usernames'] as $user) {
		array_push($links, '<a href="' . Url::format('/user/view/' . $user) . '">' . $user . '</a>');
	}
}
echo implode('&nbsp;-&nbsp;', $links);
?>
</div>
