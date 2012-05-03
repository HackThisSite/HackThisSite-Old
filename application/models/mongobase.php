<?php
class mongoBase
{
    // TODO: implement proper type checking and type casting to MongoId
    protected function _toMongoId($id)
    {
        return new MongoId($id);
    }
    
    public function clean($string) {
        return utf8_encode(htmlentities(trim((string) $string), ENT_QUOTES, '', false));
    }
}
