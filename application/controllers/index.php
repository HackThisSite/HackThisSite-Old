<?php
class index_controller extends Controller
{
    public function index()
    {
        $news = new News;
        $this->view['news'] = $news->getNewPosts(false);
    }
}
