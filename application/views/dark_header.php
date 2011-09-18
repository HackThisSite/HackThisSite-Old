<?php
$this->load->helper('url');

	$tick = '<img src="'. $this->config->item('data_server') .'/images/tick.gif" alt="#" />';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>Hack This Site!<?php echo (isset($title) && $title != '' ? ' :: '.$title : ''); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="Author" content="HackThisSite.org Crew." />
  <meta name="Description" content="HackThisSite! is a legal and safe network security resource where users test their hacking skills on various challenges and learn about hacking and network security. Also provided are articles, comprehensive and active forums, and guides and tutorials. Learn how to hack!" />
  <meta name="KeyWords" content="challenge, computer, culture, deface, digital, ethics, games, guide, hack, hack forums, hacker, hackers, hacking, hacking challenges, hacking forums, mission, net, programming, radical, revolution, root, rooting, security, site, society, tutorial, tutorials, war, wargame, wargames, web, website" />
  <link rel="icon" href="/favicon.ico" type="image/x-icon" />

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <link href="<?php echo $this->config->item('data_server'); ?>/themes/Dark/Dark.css" rel="stylesheet" type="text/css" />
  <base href="<?php echo base_url();?>" />
</head>

<body>
<div id="topbar" align="center">
<a href="<?php echo base_url();?>" id="active">HackThisSite</a> - <a href="irc://irc.hackthissite.org:+7000/">IRC</a> - <a href="<?php echo base_url();?>/forums">Forums</a> - <a href="https://twitter.com/#!/hackthissite">Twitter</a> - <a href="http://radio.hackthissite.org">Radio</a> - <a href="http://www.cafepress.com/htsstore">Store</a></div>

	<div align="center">
<a href="/"><img src="<?php echo $this->config->item('data_server'); ?>/themes/Dark/images/header.jpg" alt="Header Logo" border="0" /></a>
  	<div align="center" class="radical">
	</div>
  <table width="780" border="0" cellpadding="0" cellspacing="0" class="siteheader">
    <tr>
      <td class="sitetopheader"><blockquote>"In order to become the master, the politician poses as the servant." - Charles de Gaulle</blockquote></td>
    </tr>
    <tr>

      <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="160" valign="top" class="navbar"><div align="center">
            <br />
<?php
$this->load->library('data');

$cookieName = $this->data->get('cookie_name');
$show = true;

if (!empty($_COOKIE[$cookieName . '_sid'])) {
	if (apc_exists(((string) $_COOKIE[$cookieName . '_sid']) . '_session')) {
		$loggedIn = true;
		$array = apc_fetch(((string) $_COOKIE[$cookieName . '_sid']) . '_session');
		extract($array);
	} else {
		$db = $this->load->database('default', true, null);
		
		$this->load->config('forum_config');
		$sessionTable = $this->config->item('prefix') . 'sessions';
		$userTable = $this->config->item('prefix') . 'users';
		$sess = $db->escape($_COOKIE[$cookieName . '_sid']);
		$u = $db->escape($_COOKIE[$cookieName . '_u']);
		
		$query = $db->query('SELECT ' . $userTable . '.username FROM ' . $sessionTable . ',' . $userTable . ' WHERE session_user_id != 1 AND session_id = ' . $sess . ' AND session_user_id = ' . $u . ' AND session_ip = \'' . $_SERVER['REMOTE_ADDR'] . '\' AND user_id = ' . $u);
		$loggedIn = $query->num_rows();
		
		if ($loggedIn) {
			$data = $query->row_array();
			$username = $data['username'];
			
			apc_store(((string) $_COOKIE[$cookieName . '_sid']) . '_session', array('loggedIn' => true, 'username' => $username), 10);
		}
		
		apc_store(((string) $_COOKIE[$cookieName . '_sid']) . '_session', array('loggedIn' => false), 10);
	}

	if ($loggedIn) {
		$show = false;

		echo 'Hello, <a href="' . base_url() . 'forums/ucp.php">', $username, '</a>!<br />';
	}
}

if ($show) {
?>
	<a class="nav" href="<?php echo base_url(); ?>forums/">Login</a><br />
<?php
}

$navigation = $this->data->zRangeGet('navigation', 0, -1);
$first = true;

foreach ($navigation as $link) {
	$link = unserialize($link);
	
	if ($link['type'] == 0) {
		echo ($first ? '' : '</ul>') . '<h4 class="header">' . $link['name'] . '</h4><ul class="navigation">';
		$first = false;
	} else if ($link['type'] == 1) {
		echo '<li><a class="nav" href="' . base_url() . $link['location'] . '">' . $link['name'] . '</a></li>';
	}
}
?>
            </div>
          </td>
          <td valign="top" class="sitebuffer">
	<br />
