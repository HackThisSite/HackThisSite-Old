<table border="0" style="width: 100%;margin: 0px;">
<tr><td style="width: 66%;padding:10px;" valign="top">
    <img class="center" src="<?php echo Url::format('/themes/GridDark/images/training.jpg', true); ?>"><br />
    Hack This Site is a free, safe and legal training ground for 
    hackers to test and expand their hacking skills. More than just 
    another hacker wargames site, we are a living, breathing community 
    with many active projects in development, with a vast selection of 
    hacking articles and a huge forum where users can discuss hacking, 
    network security, and just about everything. Tune in to the hacker 
    underground and get involved with the project.<br /><br />
    
    <div id="notice">
    First timers should read the <a href="/pages/info/guide.php">HTS 
    Project Guide</a> and <a href="/user/create/">create an account</a> 
    to get started.  All users are also required to read and adhere 
    to our <a href="/pages/info/legal/">Legal Disclaimer</a>.<br />

    <strong>Get involved on our IRC server: irc.hackthissite.org SSL 
    port 7000 #hackthissite or our <a href="/forums">web forums</a>
    </strong>.<br />
    </div>
</td><td rowspan="2" valign="bottom">
    <div class="widget">
        <strong><img src="http://2.static.htscdn.org/images/tick.gif" alt="#" /> SHORT NEWS:</strong><br />
        &nbsp;09/27: <a href="/news/view/600" title="Security Advisory: SSL/TLS &#039;BEAST&#039; Exploit">Security Advisory: S...</a><br />
        &nbsp;01/26: <a href="/news/view/584" title="UPDATE: Concerning our port to LDAP">UPDATE: Concerning o...</a><br />
        &nbsp;12/12: <a href="/news/view/579" title="Forums &amp; Downtime">Forums &amp; Downtim...</a><br />
        &nbsp;06/10: <a href="/news/view/576" title="Mail fixed">Mail fixed</a><br />
        &nbsp;08/17: <a href="/news/view/571" title="DB Backup/Mission Points">DB Backup/Mission Po...</a><br /><br />

        <strong><img src="http://2.static.htscdn.org/images/tick.gif" alt="#" /> LATEST ARTICLES:</strong><br />
        &nbsp;<a href="/articles/read/1098" title="Information Vulnerability in Everyday Life - Are You Safe?">Information Vulnerability in...</a><br />
        &nbsp;<a href="/articles/read/1097" title="Bypassing DNS Filters For Fun and Freedom">Bypassing DNS Filters For Fu...</a><br />
        &nbsp;<a href="/articles/read/1096" title="Why SOPA and PIPA Suck, v1.0">Why SOPA and PIPA Suck, v1.0</a><br />
        &nbsp;<a href="/articles/read/1095" title="Programming 8 - create your own IRC bot using Java">Programming 8 - create your ...</a><br />
        &nbsp;<a href="/articles/read/1094" title="Brute force without a dictionary using john">Brute force without a dictio...</a><br /><br />

        <strong> <img src="http://2.static.htscdn.org/images/tick.gif" alt="#" /> RSS FEEDS:</strong><br />
        &nbsp;<a href="http://www.securityfocus.com/columnists/504?ref=rss">Mark Rasch: Lazy Workers May...</a><br />
        &nbsp;<a href="http://www.pheedcontent.com/click.phdo?i=8429e7d33c3c4b72a5e140c3135f67c5">An inside look into OWASP?s ...</a><br />
        &nbsp;<a href="http://www.social-engineer.org/interesting-se-articles/defcon-hackers-steal-data-from-oracle-really/">Defcon Hackers Steal Data fr...</a><br />
        &nbsp;<a href="http://rss.slashdot.org/~r/Slashdot/slashdot/~3/popcu4cWt7U/speech-jamming-gun-silences-from-30-meters">Speech-Jamming Gun Silences ...</a><br />
        &nbsp;<a href="http://feeds.acunetix.com/~r/acunetixwebapplicationsecurityblog/~3/G_bATmb_HM8/">Full Disclosure ? 20 high pr...</a><br />
    </div>
    
    <div class="widget text-center">
        <br />
        <a href="http://www.cafepress.com/htsstore"><img src="http://0.static.htscdn.org/images/htsstore.jpg" alt="HackThisSite Store" border="0" /></a><br /><br />
        <!-- Commented out because domain has expired <a href="http://www.hacktivist.net"><img src="/pages/index/menu-hacktivist.jpg" alt="hacktivist.net" border="0" /></a><br /><br /> -->
        <!--<a href="http://www.rootthisbox.org"><img src="/pages/index/menu-rtb.jpg" alt="RootThisBox" border="0" /></a><br /><br />-->
        <a href="https://hackbloc.org/zine"><img src="http://0.static.htscdn.org/images/hackthiszine.jpg" alt="HackThisZine" border="0" /></a><br /><br />
    </div>
    
    <div class="widget" style="margin-bottom: 0">
        <strong><img src="http://0.static.htscdn.org/images/tick.gif" alt="#" />CONTRIBUTE:</strong><br />
        &raquo; <a href="irc://irc.hackthissite.org:+7000/">IRC</a> / <a href="http://www.hackthissite.org/forums">Forums</a> - Discussion<br />
        &raquo; <a href="http://redmine.hackthissite.org">Redmine</a> - Project Management<br />
        <!-- &raquo; <strike>Wiki</strike> - Coming Soon -->
    </div>
</td></tr><tr><td valign="bottom">
    <div class="widget" style="margin-bottom: 0">
        <img src="http://0.static.htscdn.org/images/tick.gif" alt="#" /> NOW PLAYING ON 
        <a href="http://radio.hackthissite.org">HACKTHISSITE RADIO</a>:</strong><br />
        &raquo; HackThisSite Radio - Electronic Music, We Got It All (128kbps, 2 listeners)
    </div>
</td></tr></table>
<table border="0" width="100%"><tr><td width="50%"><div class="widget">
    <strong><img src="http://1.static.htscdn.org/images/tick.gif" alt="#" />
    LATEST FORUM POSTS:</strong><br />
    
    <center>Please login to see this feature.</center>
</div></td><td width="50%"><div class="widget">
    <strong><img src="http://1.static.htscdn.org/images/tick.gif" alt="#" />
    LATEST IRC LINES:</strong><br />
    
    <center>Please login to see this feature.</center>
</div></td></tr></table><br /><br />

<?php
foreach($news as $post):
echo Partial::render('newsShort', $post);
endforeach;
?>
