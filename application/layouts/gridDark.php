<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>HackThisSite</title>
	<link rel="stylesheet" href="<?php echo Url::format('/themes/GridDark/css/custom.css', true); ?>" type="text/css" media="screen" />
</head>
<body>
    <!-- Start header -->
    <div id="topbar">
        <!-- Start header links -->
        <a href="http://www.hackthissite.org" id="active">HackThisSite</a> - 
        <a href="irc://irc.hackthissite.org:+7000/">IRC</a> - 
        <a href="http://www.hackthissite.org/forums">Forums</a> - 
        <a href="https://twitter.com/#!/hackthissite">Twitter</a> - 
        <a href="http://radio.hackthissite.org">Radio</a> - 
        <a href="http://www.cafepress.com/htsstore">Store</a>
        <!-- End header links -->
    
        <!-- Start header image -->
        <a href="/"><img class="center" src="http://0.static.htscdn.org/themes/Dark/images/header.jpg" alt="Header Logo" border="0" /></a>
        <!-- End header image -->
    
        <!-- Start blockquote -->
        <blockquote>
<?php echo $randomQuote; ?>
        </blockquote>
        <!-- End blockquote -->
    </div>
    <!-- End header -->
    
    <!-- Start Content -->
    <table border="0" class="container"><tr><td class="navigation" valign="top">
        <!-- Start navigation -->
        <h4 class="header">Donate</h4><p><a href="http://www.hackthissite.org/donate/"><img src="http://2.static.htscdn.org/images/donate.png" border="0" title="Donate to HackThisSite.org" alt="Donate to HackThisSite.org" /></a><br />
        HTS costs up to $300 a month to operate. We <strong>need</strong> your help!</p>


        <h4 class="header">About HTS</h4>
        <ul class="navigation">
            <li><a class="nav" href="/pages/info/guide/">About the Project</a></li>
            <li><a class="nav" href="/pages/info/billofrights/">Bill of Rights</a></li>
            <li><a class="nav" href="/pages/info/legal/">Legal Disclaimer</a></li>
            <li><a class="nav" href="/pages/info/privacy/">Privacy Statements</a></li>
            <li><a class="nav" href="/pages/info/staff/">Meet the Staff</a></li>
            <li><a class="nav" href="/advertise">Advertise with HTS</a></li>
            <li><a class="nav" href="/hof">Hall of Fame</a></li>
        </ul>


        <br />
        <a href="/"><img src="http://2.static.htscdn.org/images/hts_80x15.gif" width="80" height="15" border="0" alt="" /></a><br />
        <a class="nav" href="http://www.hackthissite.org/pages/info/linktous.php">Link to us!</a>

        <h4 class="header">Partners</h4><br />
            <a href="http://affiliates.mozilla.org/link/banner/8528"><img src="http://affiliates.mozilla.org/media/uploads/banners/ac502446d8392cea778bcdaf8b3e07f8958a0216.png" alt="Download Firefox" width="88" /></a><br />
            <a class="nav" href="http://www.hackbloc.org/"><img src="http://2.static.htscdn.org/images/linkhb.gif" border="0" alt="Hackbloc" width="88" height="31" /></a><br />
            <a class="nav" href="http://www.hellboundhackers.org/"><img src="http://2.static.htscdn.org/images/hbhlogo.jpg" width="88" height="31" border="0" alt="Hellbound Hackers" /></a><br />
            <a class="nav" href="http://wigle.net/"><img src="http://2.static.htscdn.org/images/wigle-g-banner.gif" alt="WiGLE.net" width="88" height="31" border="0" /></a><br />
            <a class="nav" href="http://www.acunetix.com/blog"><img src="http://2.static.htscdn.org/images/acunetixblog.gif" alt="Acunetix Security Blog" width="88" height="31" border="0" /></a><br />
            <a class="nav" href="http://phoenix-network.org">phoenix free shells</a><br />
            <a class="nav" href="http://hackergames.net/in.php?ID=199">hackergames.net</a><br />
        <!-- End navigation -->
    </td><td class="content" valign="top">
            <!-- Start content -->
<?php echo $content; ?>
    </td></tr>
    <tr><td colspan="2">
        <!-- Start footer -->
        <img class="center" src="http://1.static.htscdn.org/themes/Dark/images/hts_bottomheadern.jpg" alt="End Footer" />
        
        <div class="footer">
            HackThisSite is is the collective work of the HackThisSite staff, 
            licensed under a <a rel="license" 
            href="http://creativecommons.org/licenses/by-nc/3.0/">CC BY-NC</a> 
            license.<br />
            We ask that you inform us upon sharing or distributing.<br /><br />
            
            <sub>
            Page loaded in <?php echo $pageExecutionTime; ?> seconds<br />
            </sub>
        </div>
        <!-- End footer -->
    </td></tr></table>
</body>
</html>
