<?php
/**
 * Main BBCode Parser.
 * 
 * @package Library
 */
class BBCode {
    
    /**
     * Parse BBCode into HTML.
     * 
     * @param string $data BBCode string to be converted to HTML.
     * 
     * @return string HTML representation of $data.
     */
    static public function parse($data) {       
        $bbcodedata = array(
            'b' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<b>', 'close_tag' => '</b>'),
            'u' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<u>', 'close_tag' => '</u>'),
            'i' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<i>', 'close_tag' => '</i>'),
            'list' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<ul>', 'close_tag' => '</ul>', 'childs' => 'li'),
            'olist' => array('type' =>BBCODE_TYPE_NOARG, 'open_tag' => '<ol>', 'close_tag' => '</ol>', 'childs' => 'li'),
            'li' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<li>', 'close_tag' => '</li>', 'parents' => 'list'),
            'url' => array('type' => BBCODE_TYPE_OPTARG, 'open_tag' => '<a href="{PARAM}">', 'close_tag' => '</a>', 'default_arg' => '{CONTENT}', 'param_handling' => 'self::cleanUrl'),
            'code' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<pre>', 'close_tag' => '</pre>', 'content_handling' => 'self::noBrs'),
            'h1' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<h2><u>', 'close_tag' => '</u></h2>'),
            'h2' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<h3><u>', 'close_tag' => '</u></h3>'),
            
            'center' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<center>', 'close_tag' => '</center>'),
            'img' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<img src="', 'close_tag' => '" />', 'content_handling' => 'self::cleanContentUrl'),
            'indexlink' => array('type' => BBCODE_TYPE_ARG, 'open_tag' => '<a href="#{PARAM}">', 'close_tag' => '</a>'),
            'index' => array('type' => BBCODE_TYPE_ARG, 'open_tag' => '<a style="padding-top: 50px;" name="{PARAM}">', 'close_tag' => '</a>'),
            //'indexlink' => array('type' => BBCODE_TYPE_
            );
        
        $bbcode = bbcode_create($bbcodedata);
        return "<div align=\"left\">" . str_replace(array('<ul><br />', '</li><br />'), array('<ul>', '</li>'), bbcode_parse($bbcode, nl2br($data))) . "</div>";
     }
     
     static private function cleanUrl($content, $arg) {
         if (!filter_var($arg, FILTER_VALIDATE_URL)) return '/';
         return filter_var($arg, FILTER_SANITIZE_URL);
     }
     
     static private function cleanContentUrl($content, $arg) {
         if (!filter_var($content, FILTER_VALIDATE_URL)) return '/';
         return filter_var($content, FILTER_SANITIZE_URL);
     }
     
     static private function noBrs($content, $arg) {
         return str_replace("<br />", "", $content);
     }
     
}
