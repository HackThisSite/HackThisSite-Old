    <p style="margin-top: 20px"><br />
<?php if ($rating == 0): ?>
        <em>No ratings yet!</em>
<?php else:
$html = array();

if ($rating['likes'] != 0)
    $html[] = $rating['likes'] . ' like' . ($rating['likes'] == 1 ? '' : 's');
if ($rating['dislikes'] != 0)
    $html[] = $rating['dislikes'] . ' dislike' . ($rating['dislikes'] == 1 ? '' : 's');

$html = implode(', ', $html);
if (empty($html))
    $html = 'No votes!';
endif; ?>
        <em><?php echo $html; ?></em>
<?php if (CheckAcl::can('voteOn' . $type)): ?>
        <a href="<?php echo Url::format('/' . $where . '/vote/' . $_id . '/like'); ?>" 
        class="btn btn-small">
            <i class="icon-plus"></i>
            Like
        </a>&nbsp;
        <a href="<?php echo Url::format('/' . $where . '/vote/' . $_id . '/dislike'); ?>" 
        class="btn btn-inverse btn-small">
            <i class="icon-minus icon-white"></i>
            Dislike
        </a>
<?php endif; ?>
    </p>
