<?php
/**
 * MongoDB Base
 * 
 * @package Model
 */
class mongoBase extends Cache {

    /**
     * Cast a value to a MongoId.
     * 
     * Todo:  Implement proper type checking and type casting to MongoId.
     * 
     * @param mixed Normal form of a MongoId (Number, string?)
     */
    protected function _toMongoId($id) {
        return new MongoId($id);
    }
    
    /**
     * Clean a string, so it's safe to put in MongoDB.
     * 
     * @param string $string The string to be cleaned.
     * 
     * @return string The cleaned string.
     */
    public function clean($string) {
        return utf8_encode(htmlentities(trim((string) $string), ENT_QUOTES, '', false));
    }
    
}
