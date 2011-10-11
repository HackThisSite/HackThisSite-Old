<?php
class controller_index extends Controller
{
    public function index()
    {
        $news = new News;
        $this->view['news'] = $news->getNewPosts(false);
    }
}
