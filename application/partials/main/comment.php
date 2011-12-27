<?php
// Comments
// * $id - Content Id
// * $page - Comments page

$commLib = new comments(ConnectionFactory::get('mongo'));
$comments = $commLib->get($id, $page);

if (empty($comments))
	echo "No comments!";

foreach ($comments as $comment) {
?>
<table border="1" width="90%">
    <tr>
        <td width="33%"><?php echo $comment['user']['username']; ?><br />
        <?php echo Date::minuteFormat($comment['date']); ?></td>
        <td><?php echo $comment['text']; ?></td>
<?php if (CheckAcl::can('deleteComment') || (CheckAcl::can('deleteOwnComment') && Session::getVar('username') == $comment['username'])): ?>
        <td width="5%"><a href="<?php echo Url::format('/comment/delete/' . $comment['_id']); ?>">Delete</a></td>
<?php endif; ?>
    </tr>
</table>
<?php
}

if (CheckAcl::can('postComment')):
?>
<h4><u>New Comment</u></h4>
<form action="<?php echo Url::format('/comment/post/save/' . $id); ?>" method="post">
    <textarea name="text"></textarea><br />
    <input type="submit" name="submit" value="Post Comment" />
</form>
<?php endif; ?>
