<center>
<?php
$links = array();

if ($currCategory == 'new') {
    array_push($links, '<strong>Newest</strong>');
} else {
    array_push($links, '<a href="' . Url::format('/article/') . '">New</a>');
}

foreach ($categories as $short => $category) {
    if ($currCategory == $short) {
        array_push($links, '<strong>' . $category . '</strong>');
    } else {
        array_push($links, ' <a href="' . Url::format('/article/index/' . $short) . '">' . $category . '</a>');
    }
}

echo implode('&nbsp;-&nbsp;', $links);
?>
</center>
<?php
if ($currCategory != 'new') {
    $pageData = array(
        'total' => $total, 
        'perPage' => articles::PER_PAGE, 
        'page' => $page,
        'url' => '/article/index/' . $currCategory . '/'
    );

    echo Partial::render('pagination', $pageData);
}

foreach ($articles as $article) {
    echo Partial::render('articleQuickView', $article);
}

if ($currCategory != 'new') {
    echo Partial::render('pagination', $pageData);
}
?>
