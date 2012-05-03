<?php if (!empty($results)): ?>
<div class="page-header"><h1>Search</h1></div>
<?php
foreach ($results as $result) {
    switch ($result['type']) {
        case 'news':
            echo Partial::render('newsShort', $result);
            break;
        
        case 'article':
            echo Partial::render('articleQuickView', $result) . '<hr />';
            break;
        
        case 'lecture':
            echo Partial::render('lecture', $result);
            break;
    }
}
endif;
?>
