<?php
foreach ($notices as $notice) {
?>
<center><span style="font-weight: bold;color: red"><?php echo $notice; ?></span></center>
<?php
}

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
	<b><u>Most Recent Users:</u></b><br />
<?php 
$links = array();
foreach ($onlineUsers as $user) {
	array_push($links, '<a href="' . Url::format('/user/view/' . $user) . '">' . $user . '</a>');
}
echo implode('&nbsp;-&nbsp;', $links);
?>
</div>
