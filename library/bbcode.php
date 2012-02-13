<?php
class BBCode {
	
	static public function parse($data, $link) {
		$data = str_replace("javascript", 'java<b></b>script', $data);
		$data = htmlentities(html_entity_decode($data));
	 
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
		 
		);
		
		foreach( $bbcodedata as $k => $v )
		 {
		   $data = preg_replace("`".$bbcodedata[$k]['start'][1].$bbcodedata[$k]['end'][1]."`is", $bbcodedata[$k]['start'][2].$bbcodedata[$k]['end'][2], $data);
		 }
		 
		 return "<div align=\"left\">" . nl2br($data) . "</div>";
	 }
	 
}
