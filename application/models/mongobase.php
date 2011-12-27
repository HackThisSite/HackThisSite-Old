<?php
class mongoBase
{
    // TODO: implement proper type checking and type casting to MongoId
    protected function _toMongoId($id)
    {
        return new MongoId($id);
    }
    
    protected function clean($string) {
        return htmlentities(utf8_encode((string) $string), ENT_QUOTES, 'ISO8859-15', false);
    }
}
