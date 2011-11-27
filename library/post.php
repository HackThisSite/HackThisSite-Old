<?php

class Post extends Filter
{
    public function __construct()
    {
        parent::__construct($_POST);
    }
}