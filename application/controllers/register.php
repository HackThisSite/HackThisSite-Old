<?php
class controller_register extends Controller
{
    public function index()
    {
        // nothing dynamic to do
        echo Post::string("username");
        exit;
    }

    public function submit()
    {
        echo Post::string("username");
        exit;
    }
}
