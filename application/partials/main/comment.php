<?php
// Comments
// * $id - Content Id
// * $page - Comments page

$commLib = new comments(ConnectionFactory::get('mongo'));
$comments = $commLib->get($id, false, false, false, $page);

if (empty($comments))
	echo "No comments!";

foreach ($comments as $comment) {
?>
<table border="1" width="90%">
    <tr>
        <td width="33%"><?php echo $comment['user']['username']; ?><br />
        <?php echo Date::minuteFormat($comment['date']); ?></td>
        <td><?php echo $comment['text']; ?></td>
        <td width="5%">
<?php if (CheckAcl::can('editAllComment') || (CheckAcl::can('editComment') && Session::getVar('username') == $comment['user']['username'])): ?>
            <a href="<?php echo Url::format('/comment/edit/' . $comment['_id']); ?>">Edit</a>
<?php endif; ?>
<?php if (CheckAcl::can('deleteAllComment') || (CheckAcl::can('deleteComment') && Session::getVar('username') == $comment['user']['username'])): ?>
            <a href="<?php echo Url::format('/comment/delete/' . $comment['_id']); ?>">Delete</a>
<?php endif; ?>
        </td>
    </tr>
</table>
<?php
}

if (CheckAcl::can('postComment')):
?>
<h4><u>New Comment</u></h4>
<form action="<?php echo Url::format('/comment/post/save'); ?>" method="post">
    <input type="hidden" name="contentId" value="<?php echo $id; ?>" />
    <textarea name="text"></textarea><br />
    <input type="submit" name="submit" value="Post Comment" />
</form>
<?php endif; ?>
