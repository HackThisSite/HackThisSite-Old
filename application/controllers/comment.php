<?php
class controller_comment extends Controller {
    
    public function post($arguments) {
        if (!CheckAcl::can('postComment'))
            return Error::set('You can not post comments!');
        if (empty($arguments[0]))
            return Error::set('Content ID is required.');
        if (empty($_POST['text']))
            return Error::set('No comment was found.');
            
        $content = new content(ConnectionFactory::get('mongo'));
        $entry = $content->getById($arguments[0]);
        
        if (empty($entry))
            return Error::set('Invalid content ID.');
        if (empty($entry['commentable']))
            return Error::set('Is not commentable.');
            
        $comments = new comments(ConnectionFactory::get('mongo'));
        $comments->create($arguments[0], $_POST['text']);
        
        Error::set('Comment posted!', true);
        if (!empty($_SERVER['HTTP_REFERER']))
            header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    public function delete($arguments) {
        if (empty($arguments[0]))
            return Error::set('Content ID is required.');
            
        $comments = new comments(ConnectionFactory::get('mongo'));
        $comment = $comments->getById($arguments[0]);
        
        if (empty($comment))
            return Error::set('Invalid comment Id.');
        if (!CheckAcl::can('deleteComment') && !(CheckAcl::can('deleteOwnComment') && Session::getVar('username') == $comment['user']['username']))
            return Error::set('You are not allowed to delete this comment!');
        
        $comments->delete($arguments[0]);
        Error::set('Comment deleted!', true);
        if (!empty($_SERVER['HTTP_REFERER']))
            header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
}
