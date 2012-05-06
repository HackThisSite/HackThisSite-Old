<center><blockquote><?php echo $randomQuote; ?></blockquote></center>
<div class="row">
    <div class="span3"><div class="well">
        <h4>Short News</h4>
<?php 
foreach ($shortNews as $entry) {
    $link = Url::format('/news/view/' . Id::create($entry, 'news'));
    $fullTitle = $entry['title'];
    
    $shortTitle = html_entity_decode($fullTitle, ENT_QUOTES);
    $longer = (strlen($shortTitle) > 30);
    $shortTitle = substr($shortTitle, 0, 30);
    $shortTitle = htmlentities($shortTitle, ENT_QUOTES) . ($longer ? '&hellip;' : '');
    
    echo '<a style="font-size: 11px;" title="' . $fullTitle . '" href="' . $link . '">' . $shortTitle . '</a><br />';
}
?>
    </div></div>
    <div class="span6"><div class="well">
        <h4>IRC Lines</h4>
        <em>Not implemented yet.</em>
    </div></div>
</div>

<div class="row">
    <div class="span3"><div class="well">
        <h4>Latest Articles</h4>
<?php 
foreach ($newArticles['articles'] as $entry) {
    $link = Url::format('/article/view/' . Id::create($entry, 'news'));
    $fullTitle = $entry['title'];
    
    $shortTitle = html_entity_decode($fullTitle, ENT_QUOTES);
    $longer = (strlen($shortTitle) > 30);
    $shortTitle = substr($shortTitle, 0, 30);
    $shortTitle = htmlentities($shortTitle, ENT_QUOTES) . ($longer ? '&hellip;' : '');
    
    echo '<a style="font-size: 11px;" title="' . $fullTitle . '" href="' . $link . '">' . $shortTitle . '</a><br />';
}
?>
    </div></div>
    <div class="span6"><div class="well">
        <h4>Latest Forum Posts</h4>
        <em>Not implemented yet.</em>
    </div></div>
</div>
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
