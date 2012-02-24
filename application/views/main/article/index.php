<?php
foreach ($articles as $article) {
    echo Partial::render('articleQuickView', $article);
}
?>
