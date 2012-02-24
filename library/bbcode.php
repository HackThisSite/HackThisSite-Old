<?php
class BBCode {
	
	static public function parse($data) {       
        $bbcodedata = array(
            '' => array('type'=>BBCODE_TYPE_ROOT,  'childs'=>'!i'),
            'b' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<b>', 'close_tag' => '</b>'),
            'u' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<u>', 'close_tag' => '</u>'),
            'i' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<i>', 'close_tag' => '</i>'),
            'list' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<ul>', 'close_tag' => '</ul>', 'childs' => 'li'),
            'olist' => array('type' =>BBCODE_TYPE_NOARG, 'open_tag' => '<ol>', 'close_tag' => '</ol>', 'childs' => 'li'),
            'li' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<li>', 'close_tag' => '</li>', 'parents' => 'list'),
            'url' => array('type' => BBCODE_TYPE_OPTARG, 'open_tag' => '<a href="{PARAM}">', 'close_tag' => '</a>', 'default_arg' => '{CONTENT}', 'param_handling' => 'self::cleanUrl'),
            'code' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<code>', 'close_tag' => '</code>'),
            'h1' => array('type' => BBCODE_TYPE_NOARG, 'open_tag' => '<h3><u>', 'close_tag' => '</u></h3>')
            //'indexlink' => array('type' => BBCODE_TYPE_
            );
        
        $bbcode = bbcode_create($bbcodedata);
        return "<div align=\"left\">" . str_replace(array('<ul><br />', '</li><br />'), array('<ul>', '</li>'), nl2br(bbcode_parse($bbcode, $data))) . "</div>";
        
		//$data = htmlentities($data, ENT_QUOTES, 'UTF-8', false);
	 /*
		$bbcodedata = array(

			 'Heading' => array(
			  'start' => array('[h1]', '\[h1\](.*?)', '<h2>\\1'),
			  'end' => array('[/h1]', '\[\/h1\]', '</h2>'),
			 ),
		
			 'Sub_Heading' => array(
			  'start' => array('[h2]', '\[h2\](.*?)', '<h3>\\1'),
			  'end' => array('[/h2]', '\[\/h2\]', '</h3>'),
			 ),

		 'bold' => array(
		  'start' => array('[b]', '\[b\](.*?)', '<b>\\1'),
		  'end' => array('[/b]', '\[\/b\]', '</b>'),
		 ),
		
		'sys' => array(
		 'start' => array('[sys:]','\[sys:\](.*?)','<table><tr><td>'),
		 'end' => array('[/sys]','\[\/sys\]','</td></tr></table>'),
		),

		 
		 'underline' => array(
		  'start' => array('[u]', '\[u\](.*?)', '<u>\\1'),
		  'end' => array('[/u]', '\[\/u\]', '</u>'),
		 ),
		 
		 'italic' => array(
		  'start' => array('[i]', '\[i\](.*?)', '<i>\\1'),
		  'end' => array('[/i]', '\[\/i\]', '</i>'),
		 ),
		 
		 'image' => array(
		  'start' => array('[img]', '\[img\](http:\/\/|ftp:\/\/)(.*?)(.jpg|.jpeg|.bmp|.gif|.png)', '<img src=\'\\1\\2\\3\' />'),
		  'end' => array('[/img]', '\[\/img\]', ''), 
		 ),
		 
		 'url1' => array(
		  'start' => array('[url]', '\[url\](https?:\/\/|ftp:\/\/|irc:\/\/|telnet:\/\/)(.*?)', '<a href=\'\\1\\2\'>\\1\\2'),
		  'end' => array('[/url]', '\[\/url\]', '</a>'),
		 ),
		 
		 'url2' => array(
		  'start' => array('[url]', '\[url=(https?:\/\/|ftp:\/\/|irc:\/\/|telnet:\/\/)(.*?)\](.*?)', '<a href=\'\\1\\2\'>\\3'), 
		  'end' => array('[/url]', '\[\/url\]', '</a>'),
		 ),

		 'index' => array(
		  'start' => array('[index]', '\[index=(.*?)\](.*?)', '<a name=\'\\1\'>\\2'), 
		  'end' => array('[/index]', '\[\/index\]', '</a>'),
		 ),

		 'index_link' => array(
		  'start' => array('[indexlink]', '\[indexlink=(.*?)\](.*?)', '<a href=\''. $link .'#\\1\'>\\2'), 
		  'end' => array('[/indexlink]', '\[\/indexlink\]', '</a>'),
		 ),
		 
		 'code' => array(
		  'start' => array('[code]', '\[code\](.*?)', 'CODE : <br /><div class="code"><tt>\\1'),
		  'end' => array('[/code]', '\[\/code\]', '</tt></div>'),
		 ),

		 'spoiler' => array(
		  'start' => array('[spoiler]', '\[spoiler\](.*?)', '<br /> <b>spoiler:</b> <br /><div class="spoiler">\\1'),
		  'end' => array('[/spoiler]', '\[\/spoiler\]', '</div>'),
		 ),

		 'center' => array(
		  'start' => array('[center]', '\[center\](.*?)', '<div align="center">\\1'),
		  'end' => array('[/center]', '\[\/center\]', '</div>'),
		 ),

		 'left' => array(
		  'start' => array('[left]', '\[left\](.*?)', '<div align="left">\\1'),
		  'end' => array('[/left]', '\[\/left\]', '</div>'),
		 ),

		 'right' => array(
		  'start' => array('[right]', '\[right\](.*?)', '<div align="right">\\1'),
		  'end' => array('[/right]', '\[\/right\]', '</div>'),
		 ),
         'bulletList' => array(
            'start' => array('[bulletList]', '\[bulletList\](.*)', '<ul>\\1'),
            'end' => array('[/bulletList]', '\[\/bulletList\]', '</ul>')
         ),
         'a' => array(
            'start' => array('[a]', '\[a\](.*)', '<li>\\1'),
            'end' => array('[/a]', '\[\/a\]', '</li>')
         )
		 
		);
		
		foreach( $bbcodedata as $k => $v )
		 {
		   $data = preg_replace("`".$bbcodedata[$k]['start'][1].$bbcodedata[$k]['end'][1]."`is", $bbcodedata[$k]['start'][2].$bbcodedata[$k]['end'][2], $data);
		 }
		 
		 return "<div align=\"left\">" . nl2br($data) . "</div>";
         */
	 }
     
     static public function cleanUrl($content, $arg) {
         if (!filter_var($arg, FILTER_VALIDATE_URL)) return '/';
         return filter_var($arg, FILTER_SANITIZE_URL);
     }
	 
}
