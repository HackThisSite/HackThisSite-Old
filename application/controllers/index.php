<?php
class controller_index extends Controller
{
    public function index()
    {
        $news = new news();
        $this->view['news'] = $news->getNewPosts(false);
    }
}
