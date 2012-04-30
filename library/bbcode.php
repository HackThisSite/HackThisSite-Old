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
            'code' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<code>', 'close_tag' => '</code>'),
            'h1' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<h3><u>', 'close_tag' => '</u></h3>'),
            'center' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<center>', 'close_tag' => '</center>'),
            'img' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<img src="', 'close_tag' => '" />'),
            //'indexlink' => array('type' => BBCODE_TYPE_
            );
        
        $bbcode = bbcode_create($bbcodedata);
        return "<div align=\"left\">" . str_replace(array('<ul><br />', '</li><br />'), array('<ul>', '</li>'), nl2br(bbcode_parse($bbcode, $data))) . "</div>";
	 }
     
     static private function cleanUrl($content, $arg) {
         if (!filter_var($arg, FILTER_VALIDATE_URL)) return '/';
         return filter_var($arg, FILTER_SANITIZE_URL);
     }
	 
}
