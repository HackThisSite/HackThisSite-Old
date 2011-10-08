<?php
class Main {
	
	var $data;
	var $date;
	
	public function __construct() {
		$this->data = Data::singleton();
	}
	
	private function parse($data, $file) {
		$file = file_get_contents($GLOBALS['maind'] . 'application/layouts/main/' . $file . '.html');
		
		foreach ($data as $key => $value) {
			$file = str_replace('{' . $key . '}', $value, $file);
		}
		
		return $file;
	}
	public function intro($main, $notice) {
		return $this->parse(array('main' => $main, 'notice' => $notice), 'intro');
	}
	
	public function textForm($data, $location) {
		$info = array_merge($data, array('base_url' => $GLOBALS['config']['baseUrl'], 'location' => addslashes($location)));
		$info['title'] = htmlentities($info['title']);
		$info['text'] = htmlentities($info['text']);
		
		return $this->parse($info, 'textForm');
	}
	
	public function newsStart() {
		return $this->parse(array(), 'newsStart');
	}
	
	public function newsEntry($entry) {
		if (empty($this->Date)) $this->Date = new Date;
		$idLib = new Id;
		$id = $idLib->create(array('id' => (string) $entry['_id'], 'date' => $entry['date']), 'news');
		$bbcode = new BBCode;
		$comment = ($entry['commentable'] ? '<a href="/news/view/{id}/#comments">comments (N/a)</a>' : 'comments disabled');
		
		return $this->parse(array('dataServer' => $GLOBALS['config']['dataServer'], 
			'date' => $this->Date->dayFormat($entry['date']), 'title' => $entry['title'], 
			'body' => $bbcode->parse($entry['body'], '#'), 'id' => $id,
			'comment' => $comment), 'newsEntry');
	}
	
	public function newsEnd() {
		return $this->parse(array(), 'newsEnd');
	}
	
	public function showNews($entry) {
		if (empty($this->Date)) $this->Date = new Date;
		$bbcode = new BBCode;
		$id = new Id;
		
		$admin = array();
		if ($GLOBALS['permissions']->check('postNews'))
			array_push($admin, '<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/post_news/edit/' . $id->create(array('id' => (string) $entry['_id'], 'date' => $entry['date']), 'news') . '">Edit</a>');
		if ($GLOBALS['permissions']->check('deleteNews')) 
			array_push($admin, '<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/post_news/delete/' . $id->create(array('id' => (string) $entry['_id'], 'date' => $entry['date']), 'news') . '">Delete</a>');
		
		$realAdmin = '';
		if (!empty($admin))
			$realAdmin = '&nbsp;&nbsp;' . implode(' | ', $admin);
		
		$comments = ($entry['commentable'] ? 'N/a comments' : 'comments disabled');
		return $this->parse(array('title' => $entry['title'], 'date' => $this->Date->minuteFormat($entry['date']),
			'body' => $bbcode->parse($entry['body'], '#'), 'bottom' => $comments . $realAdmin), 'showNews');
	}
	
	public function simpleTableStart() {
		return '<table width="100%">';
	}
	
	public function simpleTableRow($data, $color) {
		return '<tr><td class="' . $color . '-td" style="text-align: left">' . $data . '</td></tr>';
	}
	
	public function simpleTableEnd() {
		return '</table>';
	}
	
	public function styleAdminNav($nav, $score, $id) {
		$return = '<b>Type: </b><i>' . ($nav['type'] == 0 ? 'Header' : 'Link') . '</i><br /><b>Name: </b><code>' . htmlentities($nav['name']) . '</code>';
		
		if (!empty($nav['location'])) {
			$return .= '<br /><b>Location: </b><a href="' . $GLOBALS['config']['baseUrl'] . $nav['location'] . '">' . htmlentities($nav['location']) . '</a>';
		}
		
		$return .= '<br /><b>Access: </b>';
		
		if ($nav['access'] == 'all') {
			$return .= '<b>All Users</b>';
		} else if (empty($nav['access'])) {
			$return .= '<b>Nobody</b>';
		} else {
			foreach ($nav['access'] as $group) {
				$return .= ucwords($group) . ', ';
			}
			
			$return = substr($return, 0, -2);
			
			if (count($nav['access']) == 1) $return .= ' only';
		}
		
		$return .= '<br /><b>Score: </b>' . $score;
		
		if ($GLOBALS['permissions']->check('navigationEdit')) {
			$hash = hash('adler32', serialize($nav));
			$return .= '<span style="float: right;"><a class="nav" href="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation/edit/' . $hash . '"><b>Edit</b></a>&nbsp;&nbsp;-&nbsp;&nbsp;<a class="nav" href="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation/delete/' . $hash . '"><b>Delete</b></a></span>';
		}
		
		return $return;
	}
	
	public function navigationNew() {
		$data = Data::singleton();

		$return = '<form method="POST" action="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation/save/new">
	<b>Type: </b> <input type="radio" name="type" value=0 /> Header&nbsp;&nbsp;<input type="radio" name="type" value=1 /> Link<br />
	<b>Name: </b> <input type="text" name="name" value="" /><br />
	<b>Location: </b> <input type="text" name="location" value="" /> (do not fill out if header)<br />
	<b>Access: </b><br />
	<select name="access[]" multiple="multiple">';
	
		$info = $data->query('SELECT group_name FROM ' . $GLOBALS['config']['forums']['prefix'] . 'groups WHERE 1 = 1');
		
		foreach ($info['rows'] as $row) {
			$name = strtolower($row['group_name']);
			$return .= '		<option value="' . $name . '">' . ucwords(str_replace('_', ' ', $name)) . '</option>' . "\n";
		}
		
		$return .= '	</select><br />
	<b>Score: </b> <input type="text" name="score" value="" /><br />
	<input type="submit" name="submit" value="Save" />&nbsp;&nbsp;<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation">Cancel</a>';
		
		return $return;
	}
	
	public function navigationEdit($score, $entry) {
		$data = Data::singleton();

		$return = '<form method="POST" action="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation/save/' . hash('adler32', serialize($entry)) . '">
	<b>Type: </b> <input type="radio" name="type" value=0' . ($entry['type'] == 0 ? ' checked=checked"' : '') . ' /> Header&nbsp;&nbsp;<input type="radio" name="type" value=1' . ($entry['type'] == 1 ? ' checked="checked"' : '') . ' /> Link<br />
	<b>Name: </b> <input type="text" name="name" value="' . htmlentities($entry['name']) . '" /><br />
	<b>Location: </b> <input type="text" name="location" value="' . htmlentities((!empty($entry['location']) ? $entry['location'] : '')) . '" /> (do not fill out if header)<br />
	<b>Access: </b><br />
	<select name="access[]" multiple="multiple">';
	
		$info = $data->query('SELECT group_name FROM ' . $GLOBALS['config']['forums']['prefix'] . 'groups WHERE 1 = 1');
		
		foreach ($info['rows'] as $row) {
			$name = strtolower($row['group_name']);
			$return .= '		<option value="' . $name . '"' . ($entry['access'] == 'all' || in_array($name, $entry['access']) ? '  selected="selected"' : '') . '>' . ucwords(str_replace('_', ' ', $name)) . '</option>' . "\n";
		}
		
		$return .= '	</select><br />
	<b>Score: </b> <input type="text" name="score" value="' . $score . '" /><br />
	<input type="submit" name="submit" value="Save" />&nbsp;&nbsp;<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/navigation">Cancel</a>';
		
		return $return;
	}
	
	public function template($page_content) {
		$tick = '<img src="'. $GLOBALS['config']['dataServer'] .'/images/tick.gif" alt="#" />';
		$return = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>Hack This Site!' . (/* fix later */ isset($title) && $title != '' ? ' :: '.$title : '') . '</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="Author" content="HackThisSite.org Crew." />
  <meta name="Description" content="HackThisSite! is a legal and safe network security resource where users test their hacking skills on various challenges and learn about hacking and network security. Also provided are articles, comprehensive and active forums, and guides and tutorials. Learn how to hack!" />
  <meta name="KeyWords" content="challenge, computer, culture, deface, digital, ethics, games, guide, hack, hack forums, hacker, hackers, hacking, hacking challenges, hacking forums, mission, net, programming, radical, revolution, root, rooting, security, site, society, tutorial, tutorials, war, wargame, wargames, web, website" />
  <link rel="icon" href="/favicon.ico" type="image/x-icon" />

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <link href="' . $GLOBALS['config']['dataServer'] . '/themes/Dark/Dark.css" rel="stylesheet" type="text/css" />
  <base href="' . $GLOBALS['config']['baseUrl'] . '" />
</head>

<body>
<div id="topbar" align="center">
<a href="' . $GLOBALS['config']['baseUrl'] . '" id="active">HackThisSite</a> - <a href="irc://irc.hackthissite.org:+7000/">IRC</a> - <a href="' . $GLOBALS['config']['baseUrl'] . 'forums">Forums</a> - <a href="https://twitter.com/#!/hackthissite">Twitter</a> - <a href="http://radio.hackthissite.org">Radio</a> - <a href="http://www.cafepress.com/htsstore">Store</a></div>

	<div align="center">
<a href="/"><img src="' . $GLOBALS['config']['dataServer'] . '/themes/Dark/images/header.jpg" alt="Header Logo" border="0" /></a>
  	<div align="center" class="radical">
	</div>
  <table width="780" border="0" cellpadding="0" cellspacing="0" class="siteheader">
    <tr>
      <td class="sitetopheader"><blockquote>' . $this->data->sRandMember('quotes') . '</blockquote></td>
    </tr>
    <tr>

      <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="160" valign="top" class="navbar"><div align="center">
            <br />';
        
		$forums = new Forums;
		extract($forums->loginData());

		if ($loggedIn) {
			$return .= 'Hello, <a href="' . $GLOBALS['config']['baseUrl'] . 'forums/ucp.php">' . $username . '</a>!<br />';
		} else {
			$return .= '<a class="nav" href="' . $GLOBALS['config']['baseUrl'] . 'forums/">Login</a><br />';
		}

		$navigation = $this->data->zRangeGet('navigation', 0, -1);
		$first = true;

		foreach ($navigation as $link) {
			$link = unserialize($link);
			
			if ($link['access'] != 'all' && !in_array($group, $link['access'])) continue;
			
			if ($link['type'] == 0) {
				$return .= ($first ? '' : '</ul>') . '<h4 class="header">' . $link['name'] . '</h4><ul class="navigation">';
				$first = false;
			} else if ($link['type'] == 1) {
				$return .= '<li><a class="nav" href="' . $GLOBALS['config']['baseUrl'] . $link['location'] . '">' . $link['name'] . '</a></li>';
			}
		}
		
		$return .= '
            </div>
          </td>
          <td valign="top" class="sitebuffer">
	<br />';
	
		foreach ($GLOBALS['errors'] as $error) {
			$return .= '<center><div style="width:80%"><div class="dark-td"><h2>Error!</h2></div><div class="light-td">' . $error . '<br /></div></div></center>';
		}
			
		$return .= $page_content . '


</td>
        </tr>
      </table></td>
    </tr>
 <tr>
      <td class="sitebottomheader"><img src="' . $GLOBALS['config']['dataServer'] . '/themes/Dark/images/hts_bottomheadern.jpg" alt="End Footer" width="780" height="60" /></td>
    </tr>
  </table>
  <br />
<div align="center" style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; color:#CCCCCC">This site is the collective work of the 
HackThisSite staff. Please don\'t reproduce in part or whole without permission.<br />
</div>
</div>
<div align="center">
  <p>
   <a href="http://validator.w3.org/check?uri=referer"><img src="' . $GLOBALS['config']['dataServer'] . '/images/xhtml10.png" width="80" height="15" border="0" alt="" /></a>&nbsp;
   <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="' . $GLOBALS['config']['dataServer'] . '/images/css.png" width="80" height="15" border="0" alt="" /></a> 
   <a href="http://www.php.net/"> <img src="' . $GLOBALS['config']['dataServer'] . '/images/phppow.gif" width="80" height="15" border="0" alt="" /></a>
   <a href="http://www.freebsd.org/"> <img src="' . $GLOBALS['config']['dataServer'] . '/images/freebsd.png" width="80" height="15" border="0" alt="" /></a>
  </p>
  
  <div align="center" style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; color:#CCCCCC">
  Page rendered in <strong>' . substr((string) (microtime(true) - $GLOBALS['startTime']), 0, 6) . '</strong> seconds.
  </div>
</div>
    </body>
</html>';

		return $return;
	}

}
