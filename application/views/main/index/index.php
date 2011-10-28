<table border="0" width="100%" cellspacing="5" cellpadding="0">
  <tr>
    <td width="66%" rowspan="3" valign="top" class="normal-td">
      <center>
        <div class="training"></div>
      </center>
      <br />
      <div class="mainintro">
        Hack This Site is a free, safe and legal training ground for hackers
        to test and expand their hacking skills. More than just another hacker
        wargames site, we are a living, breathing community with many active
        projects in development, with a vast selection of hacking articles and
        a huge forum where users can discuss hacking, network security, and
        just about everything. Tune in to the hacker underground and get
        involved with the project.
        <br />
        <br />
      </div>
      <div id="notice">
        First timers should read the <a href="/pages/info/guide.php">HTS Project
        Guide</a> and <a href="/user/create/">create an account</a> to get
        started.  All users are also required to read and adhere to our
        <a href="/pages/info/legal/">Legal Disclaimer</a>.
        <br />
        <strong>Get involved on our IRC server: irc.hackthissite.org SSL port
        7000 #hackthissite or our <a href="http://www.hackthissite.org/forums">
        web forums</a></strong>.
      </div>
      <br />
    </td>
  </tr>
</table>
<div style="border-bottom: 1px dashed #000000;">
  Latest site news: <a href="/pages/hts.rss.php" title="HTS RSS feed"><img src="http://1.static.htscdn.org/images/feed-icon.png" alt="RSS!" border="0" /></a>
</div>
<br />
<div>
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td class="normal-td" style="font-size: 16px;"></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <?php foreach($news as $entry): ?>
      <?php echo Partial::render("newsEntry", $entry); ?>
    <?php endforeach; ?>
    <tr>
      <td>&nbsp;</td>
    </tr>
  </table>
</div>

