<?php
$main = 'Hack This Site is a free, safe and legal training ground for hackers to test and expand
their hacking skills. More than just another hacker wargames site, we are a living, breathing community with many active 
projects in development, with a vast selection of hacking articles and a huge forum where users can discuss hacking, 
network security, and just about everything. Tune in to the hacker underground and get involved with the project.';

$notice = 'First timers should read the <a href="/pages/info/guide.php">HTS Project Guide</a> and <a href="/user/create/">create an account</a> 
to get started.  All users are also required to read and adhere to our <a href="/pages/info/legal/">Legal Disclaimer</a>.
<br />

<strong>Get involved on our IRC server: irc.hackthissite.org SSL port 7000 #hackthissite or our 
<a href="http://www.hackthissite.org/forums">web forums</a></strong>.';

echo $template->intro($main, $notice);
echo $template->newsStart();

foreach ($news as $entry) {
	echo $template->newsEntry($entry);
}

echo $template->newsEnd();
