<?php

class Post extends Filter
{
    static protected $input = null;

    static protected function import()
    {
        parent::import($_POST);
    }
}