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
<table border="1" width="100%">
	<tr>
		<td>
			<b><u>Most Recent Users:</u></b><br />
<?php 
$links = array();
foreach ($onlineUsers as $user) {
	array_push($links, '<a href="' . Url::format('/user/view/' . $user) . '">' . $user . '</a>');
}
echo implode('&nbsp;-&nbsp;', $links);
?></td>
	</tr>
</table>
