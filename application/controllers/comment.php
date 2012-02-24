<?php
class controller_comment extends Content {
    
    var $name = 'comment';
    var $model = 'comments';
    var $db = 'mongo';
    var $permission = 'Comment';
    var $createForms = array('contentId', 'text');
    var $dnr = true;
    
}
