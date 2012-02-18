<?php 
if (!empty($results)) {
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
}
?>
