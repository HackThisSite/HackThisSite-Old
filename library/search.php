<?php
/**
 * Search management
 * 
 * @package Library
 */
class Search {
    
    const LOCATION = 'localhost:9200';
    const INDEX = 'hackthissite';
    const TYPE = 'content';
    
    /**
     * Index a new piece of content.
     * 
     * @param string $id MongoDB id of the content.
     * @param array $record The piece of content.
     */
    public static function index($id, $record) {
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id,
            $record);
    }
    
    /**
     * Perform a search.
     * 
     * @param string $query Query to search against.
     * @param array $filter Filters to be used.
     * 
     * @return array Search results.
     */
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
    
    /**
     * Return content "More Like This"
     * 
     * @param string $id Id of the main content.
     * @param string $type Type of content.
     * @param string $fields Comma separated list of fields to search against.
     * 
     * @return array Return of the query.
     */
    public static function mlt($id, $type, $fields) {
        $request = array(
            'filter' => array(
                'term' => array('type' => $type)
                ));
        
        return self::curl(
            self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id . '/_mlt?mlt_fields=' . $fields . '&search_size=5',
            $request);
    }
    
    /**
     * Delete an index.
     * 
     * @param string $id Id of the content to remove.
     */
    public static function delete($id) {
        $ch = curl_init(self::LOCATION . '/' . self::INDEX . '/' . self::TYPE . '/' . $id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }
    
    public static function curl($url, $request) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }
}
