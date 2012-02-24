<?php
// Comments
// * $id - Content Id
// * $page - Comments page

$commLib = new comments(ConnectionFactory::get('mongo'));
$comments = $commLib->get($id, false, false, false, $page);

if (empty($comments)) {
	echo "No comments!";
} else {
?>
<h3 id="comments">Responses</h3>

<ol class="commentlist">
<?php
    $alt = false;
    foreach ($comments as $comment) {
        $alt = !$alt;
?>
    <li<?php ($alt ? 'class="alt"' : ''); ?> id="comment">
        
        <cite>
            <img alt="" src="images/gravatar.jpg" class="avatar" height="40" width="40" />			
            <a href="index.html">Erwin</a> Says: <br />				
            <span class="comment-data"><a href="#comment-63" title="">July 20th, 2008 at 8:08 am</a> </span>
        </cite>
                
        <div class="comment-text">
            <p>Comments are great!</p>
        </div>		
        
    </li>
</table>
<?php
    }
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
