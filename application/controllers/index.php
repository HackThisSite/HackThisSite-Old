<?php
class controller_index extends Controller
{
    public function index()
    {
        $news = new news(ConnectionFactory::get('mongo'));
        $this->view['news'] = $news->getNewPosts(false);
    }
}
