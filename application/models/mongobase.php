<?php
class MongoBase
{
    // TODO: implement proper type checking and type casting to MongoId
    protected function _toMongoId($id)
    {
        return $id;
    }
}