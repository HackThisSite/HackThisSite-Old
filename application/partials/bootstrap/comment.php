<a name="comments"></a>
<legend>Comments</legend>
<?php
// Comments
// * $id - Content Id
// * $page - Comments page
// * $pageLoc - Where to send new pages to.

$commLib = new comments(ConnectionFactory::get('mongo'));
$commentData = $commLib->getForId($id, $page);
extract($commentData);

$paginationData = array(
    'total' => $total,
    'perPage' => comments::PAGE_LIMIT,
    'page' => $page,
    'url' => $pageLoc,
    'where' => 'comments'
);
$pagination = Partial::render('pagination', $paginationData);

if ($total != 0) echo $pagination;
if (empty($comments))
	echo '<div class="alert">No comments!</div>';

foreach ($comments as $comment) {
?>
<table class="table table-bordered">
	<tr>
		<td style="width: 20%">
			<a href="<?php echo Url::format('/user/view/' . $comment['user']['username']); ?>">
				<?php echo $comment['user']['username']; ?>
			</a><br />
			
			<a class="thumbnail" class="display: block">
				<img src="https://secure.gravatar.com/avatar/<?php echo md5(strtolower(trim((empty($comment['user']['email']) ? 'guest@hts.org' : $comment['user']['email'])))); ?>?d=identicon&r=pg" />
			</a>
			
			<small title="<?php echo Date::minuteFormat($comment['date']); ?>">
<?php
if (time() - $comment['date'] <= 172800) { // Two days
	echo Date::durationFormat(time() - $comment['date']) . ' ago';
} else {
	echo Date::dayFormat($comment['date']);
}
?>
			</small><br />
		</td>
		<td>
			<p>
				<div class="pull-right">
<?php
$edit = false;
$delete = false;

if (CheckAcl::can('editAllComment') || (CheckAcl::can('editComment') && Session::getVar('username') == $comment['user']['username']))
	$edit = true;
if (CheckAcl::can('deleteAllComment') || (CheckAcl::can('deleteComment') && Session::getVar('username') == $comment['user']['username']))
	$delete = true;

if ($edit): ?>
					<a class="btn btn-primary" href="<?php echo Url::format('/comment/edit/' . $comment['_id']); ?>">Edit</a>&nbsp;
<?php endif;
if ($delete): ?>
					<a class="btn btn-danger" href="<?php echo Url::format('/comment/delete/' . $comment['_id']); ?>">Delete</a>
<?php endif; ?>
				</div>
				<?php echo nl2br($comment['text']); ?>
			</p>
		</td>
	</tr>
</table>
<?php
}
if ($total != 0) echo $pagination;

if (CheckAcl::can('postComment')):
?>
<legend>New Comment</legend>
<form class="well form-vertical" action="<?php echo Url::format('/comment/post/save'); ?>" method="post">
    <input type="hidden" name="contentId" value="<?php echo $id; ?>" />
    <textarea name="text" rows="7" style="width: 100%"></textarea><br />
    <input type="submit" class="btn btn-primary" value="Post Comment" />
</form>
<?php endif; ?>
