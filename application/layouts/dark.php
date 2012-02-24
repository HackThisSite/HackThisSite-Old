<head>
  <title>Hack This Site!<?php if (isset($title)): ?> :: <?php echo $title; ?><?php endif; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="Author" content="HackThisSite.org Crew." />
  <meta name="Description" content="HackThisSite! is a legal and safe network security resource where users test their hacking skills on various challenges and learn about hacking and network security. Also provided are articles, comprehensive and active forums, and guides and tutorials. Learn how to hack!" />
  <meta name="KeyWords" content="challenge, computer, culture, deface, digital, ethics, games, guide, hack, hack forums, hacker, hackers, hacking, hacking challenges, hacking forums, mission, net, programming, radical, revolution, root, rooting, security, site, society, tutorial, tutorials, war, wargame, wargames, web, website" />
  <link rel="icon" href="/favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <link href="<?php echo Url::format('/themes/Dark/Dark.css', true); ?>" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="topbar" align="center">
<a href="<?php echo Url::format('/'); ?>" id="active">HackThisSite</a> - <a href="irc://irc.hackthissite.org:+7000/">IRC</a> - <a href="<?php echo BASE_HREF;?>/forums">Forums</a> - <a href="https://twitter.com/#!/hackthissite">Twitter</a> - <a href="http://radio.hackthissite.org">Radio</a> - <a href="http://www.cafepress.com/htsstore">Store</a><?php /*<a href="http://www.rootthisbox.org">RootThisBox</a>*/ ?>
</div>
	<div align="center">
<a href="/"><img src="<?php echo Url::format('/themes/Dark/images/header.jpg', true); ?>" alt="Header Logo" border="0" /></a>
  	<div align="center" class="radical">
	</div>
  <table width="780" border="0" cellpadding="0" cellspacing="0" class="siteheader">
    <tr>
      <td class="sitetopheader"><blockquote><?php echo $randomQuote; ?></blockquote></td>
    </tr>
    <tr>
      <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="160" valign="top" class="navbar"><div align="center">
            <br />
<?php if (Session::isLoggedIn()): ?>
	<div>Hello, <a href="/user/view/<?php echo Session::getVar('username'); ?>/"><?php echo Session::getVar('username'); ?></a><br />
		<a href="<?php echo Url::format('/user/settings/'); ?>">Settings</a> - <a href="<?php echo Url::format('/user/logout'); ?>">Logout</a><br /><br />
	<!--<a class="nav" href="/user/themes/"> Skin Chooser</a><br /><br />-->
	<!--<a class="nav" href="/pages/messages/msys/">HTS Messages Center</a><br />-->
	</div>

	<?php else: ?>
	<!-- login form start -->
	<div id="ourlogin">
		<span id="loginclick"><a class="nav" href="/user/login">Login</a></span> (or <a class="nav" href="/user/create">Register</a>):<br />
		<form id="loginform" method="post" action="/user/login">
		<div id="innerlogin">
		<script type="text/javascript">var userclicked=0; var passclicked=0;</script>
		<p><input type="text" name="username" class="login" value="" onclick="if(userclicked==0){this.value='';userclicked=1;};" title="Username" /></p>
		<p><input type="password" name="password" class="login" value="" onclick="if(passclicked==0){this.value='';passclicked=1;};" title="Password" /></p>
		<p><input type="submit" value="Login" name="btn_submit" class="submit-button" /></p>
		</div>
	</form>
	<!-- login form end -->
	<a href="/user/resetpass">Lost Your Password?</a><br />
	<?php endif; ?>
	</div>

                <?php foreach ($leftNav as $title => $section): ?>
<h4 class="header">Challenges</h4>
	<ul class="navigation">
                <?php foreach ($section as $name => $location): ?>
                <li><a class="nav" href="<?php echo Url::format($location); ?>"><?php echo $name; ?></a></li>
                <?php endforeach; ?>
                <br />
                <?php endforeach; ?>
    </ul>


<a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=0&amp;t=218"><img border="0" alt="Firefox 3" title="Firefox 2" src="http://sfx-images.mozilla.org/affiliates/Buttons/firefox2/ff2o80x15.gif"/></a><br /><br />
<a href="/"><img src="<?php echo DATA_SERVER; ?>/images/hts_80x15.gif" width="80" height="15" border="0" alt="" /></a><br />
<a class="nav" href="<?php echo BASE_HREF; ?>/pages/info/linktous.php">Link to us!</a>

<h4 class="header">Partners</h4><br />
	<a class="nav" href="http://www.hackbloc.org/"><img src="<?php echo DATA_SERVER; ?>/images/linkhb.gif" border="0" alt="Hackbloc" width="88" height="31" /></a><br />
	<a class="nav" href="http://www.hellboundhackers.org/"><img src="<?php echo DATA_SERVER; ?>/images/hbhlogo.jpg" width="88" height="31" border="0" alt="Hellbound Hackers" /></a><br />
	<a class="nav" href="http://wigle.net/"><img src="<?php echo DATA_SERVER; ?>/images/wigle-g-banner.gif" alt="WiGLE.net" width="88" height="31" border="0" /></a><br />
	<a class="nav" href="http://www.acunetix.com/blog"><img src="<?php echo DATA_SERVER; ?>/images/acunetixblog.gif" alt="Acunetix Security Blog" width="88" height="31" border="0" /></a><br />
	<a class="nav" href="http://phoenix-network.org">phoenix free shells</a><br />
	<a class="nav" href="http://hackergames.net/in.php?ID=199">hackergames.net</a><br />
</div>

          </td>
          <td valign="top" class="sitebuffer">
	<br />
    <?php echo $content; ?>
