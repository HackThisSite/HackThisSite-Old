<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>Hack This Site!<?php if (isset($title)): ?> | <?php echo $title; ?><?php endif; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="Author" content="HackThisSite.org Crew." />
  <meta name="Description" content="HackThisSite! is a legal and safe network security resource where users test their hacking skills on various challenges and learn about hacking and network security. Also provided are articles, comprehensive and active forums, and guides and tutorials. Learn how to hack!" />
  <meta name="KeyWords" content="challenge, computer, culture, deface, digital, ethics, games, guide, hack, hack forums, hacker, hackers, hacking, hacking challenges, hacking forums, mission, net, programming, radical, revolution, root, rooting, security, site, society, tutorial, tutorials, war, wargame, wargames, web, website" />
  <link rel="icon" href="/favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <link href="<?php echo Config::get("static:cdn"); ?>/themes/Dark/Dark.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo Config::get("other:baseUrl"); ?>static/css/main.css" rel="stylesheet" type="text/css" />
  <base href="<?php echo Config::get("other:baseUrl"); ?>" />
</head>

<body>
<div id="topbar" align="center">
<a href="<?php echo Config::get("other:baseUrl"); ?>" id="active">HackThisSite</a> - <a href="irc://irc.hackthissite.org:+7000/">IRC</a> - <a href="<?php echo Config::get("other:baseUrl"); ?>forums">Forums</a> - <a href="https://twitter.com/#!/hackthissite">Twitter</a> - <a href="http://radio.hackthissite.org">Radio</a> - <a href="http://www.cafepress.com/htsstore">Store</a></div>

    <div align="center">
<a href="/"><img src="<?php echo Config::get("static:cdn"); ?>/themes/Dark/images/header.jpg" alt="Header Logo" border="0" /></a>
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
            <a class="nav" href="<?php echo Config::get("other:baseUrl"); ?>forums/">Login</a>
            <br />
            <?php foreach ($leftNav as $title => $section): ?>
                <?php echo Partial::render("navSection", array("title" => $title, "links" => $section)); ?>
            <?php endforeach; ?>
            </div>
          </td>
          <td valign="top" class="sitebuffer">
            <?php echo $content; ?>
</td>
        </tr>
      </table></td>
    </tr>
 <tr>
      <td class="sitebottomheader"><img src="<?php echo Config::get("static:cdn"); ?>/themes/Dark/images/hts_bottomheadern.jpg" alt="End Footer" width="780" height="60" /></td>
    </tr>
  </table>
  <br />
<div align="center" style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; color:#CCCCCC">This site is the collective work of the
HackThisSite staff. Please don\'t reproduce in part or whole without permission.<br />
</div>
</div>
<div align="center">
  <p>
   <a href="http://validator.w3.org/check?uri=referer"><img src="<?php echo Config::get("static:cdn"); ?>/images/xhtml10.png" width="80" height="15" border="0" alt="" /></a>&nbsp;
   <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="<?php echo Config::get("static:cdn"); ?>/images/css.png" width="80" height="15" border="0" alt="" /></a>
   <a href="http://www.php.net/"> <img src="<?php echo Config::get("static:cdn"); ?>/images/phppow.gif" width="80" height="15" border="0" alt="" /></a>
   <a href="http://www.freebsd.org/"> <img src="<?php echo Config::get("static:cdn"); ?>/images/freebsd.png" width="80" height="15" border="0" alt="" /></a>
  </p>

  <div align="center" style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; color:#CCCCCC">
  Page rendered in <strong><?php echo $pageExecutionTime; ?></strong> seconds.
  </div>
</div>
    </body>
</html>