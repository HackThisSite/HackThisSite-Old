<?php

class Get extends Filter
{
    public function __construct()
    {
        parent::__construct($_GET);
    }
}