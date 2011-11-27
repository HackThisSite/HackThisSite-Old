<?php
class controller_login extends Controller
{
    public function index()
    {
        $news = new news(ConnectionFactory::get('mongo'));
        $this->view['news'] = $news->getNewsPosts();
    }
}
