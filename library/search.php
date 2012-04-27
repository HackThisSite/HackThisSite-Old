<?php
class Search {
    
    const LOCATION = 'localhost:9200';
    const INDEX = 'hackthissite';
    const TYPE = 'content';
    
    public static function index($id, $record) {
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id,
            $record);
    }
    
    public static function query($query, $filter = array('ghosted' => false)) {
        $request = array(
            'query' => array(
                'filtered' => array(
                    'query' => array(
                        'query_string' => array(
                            'query' => $query
                            )
                        ),
                    'filter' => array(
                        'term' => $filter
                        )
                    )
                )
            );
        
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/_search',
            $request);
    }
    
    public static function mlt($id, $type, $fields) {
        $request = array(
            'filter' => array(
                'term' => array('type' => $type)
                ));
        
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id . '/_mlt?mlt_fields=' . $fields . '&search_size=5',
            $request);
    }
    
    public static function delete($id) {
        $ch = curl_init(self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }
    
    /*
    public static function update($id, $update, $script) {
        $request = array(
            'script' => $script,
            'params' => $update
            );
  
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id . '/_update',
            $request);
    }
    */
    
    public static function curl($url, $request) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }
}
