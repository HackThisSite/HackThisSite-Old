<div style="margin-bottom: 20px">
	To register on the hackthissite irc server, enter the following commands:<br />
	/nick &lt;username&gt;<br />
	/msg nickserv register &lt;password&gt; &lt;email&gt;<br />
	/join #help<br />
	!link &lt;hackthissite username&gt;<br />
	Then, go to the <a href="#underConstruction">link page</a> and confirm the linking.<br />
	Every time you login, either enter the following commands, or review your irc client's documentation to learn how to make it automatically send them for you.<br />
	/nick &lt;username&gt;<br />
	/msg nickserv identify &lt;password&gt;<br />
	/join #help<br />
</div>
Original version from: <a href="http://www.irchelp.org/irchelp/new2irc.html">http://www.irchelp.org/irchelp/new2irc.html</a><P>

<a href="/irc/ssl">Using SSL on HTS IRC</a><br /><br />
<a name="contents"><h2>Contents</h2>
</a><ol>
<li> <a href="#what">What is IRC, and how does it work?</a>
<li> <a href="#detail">Some details</a>
<li> <a href="#talking">Talking, and entering commands</a>
<li> <a href="#where">Where to go</a>
<li> <a href="#smiley">Some smileys and jargon</a>
<li> <a href="#advice">Some advice</a>
<li> <a href="#server">IRC server problems, and choosing a server</a>
<li> <a href="#help">More detailed help</a>
<li> <a href="#warning">A word of warning</a>
</ol>
<hr>

<a name="what"><h2>1. What is IRC, and how does it work?</h2>
</a>
<blockquote>

IRC (Internet Relay Chat) provides a way of communicating in real
time with people from all over the world.  It consists of various
separate <a href="http://www.irchelp.org/irchelp/networks/">networks</a> (or
&quot;nets&quot;) of IRC servers, machines that allow users to
connect to IRC.  The largest nets are <a
href="http://www.efnet.net/">EFnet</a> (the original IRC net, often
having more than 32,000 people at once), <a
href="http://www.undernet.org/">Undernet</a>, <a
href="http://www.funet.fi/~irc/">IRCnet</a>, <a
href="http://www.dal.net/">DALnet</a>, and <a
href="http://www.newnet.net/">NewNet</a>.<p>

Generally, the user (such as you) runs a program (called a
&quot;client&quot;) to connect to a server on one of the <a
href="http://www.irc.help.org/irchelp/networks/">IRC nets</a>.  The server relays
information to and from other servers on the same net.  Recommended
clients:

<!-- Jolo nixed PIRCH link, still need to do same to non-US vers -->
<ul>
   <li>UNIX/shell: <a href="http://www.irchelp.org/irchelp/ircii/">ircII</a>
   <li>Windows: <a href="http://www.irchelp.org/irchelp/mirc/">mIRC</a>
   <li><a href="http://www.irchelp.org/irchelp/mac/">Macintosh clients</a>
</ul><p>

Be sure to read the documentation for your client!<P>

Once connected to an IRC server on an IRC network, you will usually
join one or more &quot;channels&quot; and converse with others there.
 On <a href="http://www.efnet.net/">EFnet</a>, there often are more
than 12,000 <a href="http://www.irchelp.org/irchelp/chanlist/">channels</a>, each devoted
to a different topic. Conversations may be public (where everyone in
a channel can see what you type) or private (messages between only
two people, who may or may not be on the same channel). IRC is not a
&quot;game&quot;, and I highly recommend you treat people you meet on
IRC with the same courtesy as if you were talking in person or on
the phone, or there may be serious consequences.<P>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="detail"><h2>2. Some details</h2>
</a>
<blockquote>

Channel names usually begin with a #, as in #irchelp .  The same
channels are shared among all IRC servers on the same net, so you do
not have to be on the same IRC server as your friends. (There are
also channels with names beginning with a & instead of a #.  These
channels are not shared by all servers on the net but exist locally
on that server only.)<p>

Each user is known on IRC by a &quot;nick&quot;, such as
<i>smartgal</i> or <i>FunGuy</i>. To avoid conflicts with other
users, it is best to use a nick that is not too common, e.g.,
&quot;john&quot; is a poor choice. On some nets, nicks do not belong
to anyone, nor do channels.  This can lead to conflict, so, if you
feel strongly about ownership of such things, you may prefer
networks with &quot;services&quot; like <a
href="http://www.undernet.org/">Undernet</a>, <a
href="http://www.dal.net/">DALnet</a>, or other <a
href="http://www.irchelp.org/irchelp/networks/">smaller networks</a>.<p>

Channels are run by channel operators, or just &quot;ops&quot; for
short, who can control the channel by choosing who may join (by
&quot;banning&quot; some users), who must leave (by
&quot;kicking&quot; them out), and even who may speak (by making the
channel &quot;moderated&quot;)! Channel ops have complete control
over their channel, and their decisions are final. If you are banned
from a channel, send a /msg to a channel op and ask nicely to be let
in (see the /who command in the next section to learn how to find
ops). If they ignore you or /who gives no response because the
channel is in secret mode (+s), just go somewhere else where you are
more welcome.<P>

<a href="http://www.irchelp.org/irchelp/ircd/">IRC servers</a> are run by IRC admins and
by <a href="http://www.irchelp.org/irchelp/ircd/ircopguide.html">IRC operators</a>, or
&quot;IRC ops&quot;.  IRC ops manage the servers themselves and, on
EFnet and many other networks, do not get involved in personal
disputes, channel takeovers, restoring lost ops, etc. They are
<i>not</i> &quot;IRC cops.&quot; <P>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="talking"><h2>3. Talking, and entering commands</h2>
</a>
<blockquote>

Commands and text are typed in the same place.  By default, commands
begin with the character / .  If you have a graphical client such as
<a href="http://www.irchelp.org/irchelp/mirc/">mIRC</a> for Windows, many commands can be
executed by clicking on icons with the mouse pointer.  It is,
however, highly recommended that you learn to type in the basic IRC
commands first.  When entering commands, pay close attention to
spacing and capitalization.  The basic commands work on all the good
clients.<p>

Some examples are given below.  In these, suppose your nick is
&quot;yournick&quot;, and that you are on the channel #coolness.<P>

Your friend &quot;MaryN&quot; is in #coolness with you, and your
friend &quot;Tomm&quot; is on IRC but is not on a channel with you.
You can apply these examples in general by substituting the relevant
nick or channel names.<p>

<center>
<table width=85%>
<tr>
<th align=left width=25%>What you type<br />
<th align=left width=75%>What happens<p>

<tr>
<td valign=top><font color=#47B6FF>/join #coolness</font><br />
<td valign=top>You join the channel #coolness.<p>

<tr>
<td valign=top><font color=#47B6FF>/who #coolness</font><br />
<td valign=top>Gives some info on users in the channel.<br />
@ = channel op, while * means IRC op.<p>

<tr>
<td valign=top><font color=#47B6FF>hello everyone</font><br />
<td valign=top>Everyone on #coolness sees <i>&lt;yournick&gt; hello everyone</i>. (You need not type in your own nick.)<p>

<tr>
<td valign=top><font color=#47B6FF>/me is a pink bunny</font><br />
<td valign=top>Everyone in #coolness sees <i>* yournick is a pink bunny</i><p>

<tr>
<td valign=top><font color=#47B6FF>/part #coolness</font><br />
<td valign=top>You leave the channel.<p>

<tr>
<td valign=top><font color=#47B6FF>/whois Tomm</font><br />
<td valign=top>You get some info about Tomm or whatever nickname you entered.<p>

<tr>
<td valign=top><font color=#47B6FF>/whois yournick</font><br />
<td valign=top>This is some info others see about you.<p>

<tr>
<td valign=top><font color=#47B6FF>/nick newnick</font><br />
<td valign=top>Changes your nick to &quot;newnick&quot;<p>

<tr>
<td valign=top><font color=#47B6FF>/msg Tomm hi there.</font><br />
<td valign=top>Only Tomm sees your message (you don't need to be on the same channel for this to work).<p>

<tr>
<td valign=top><font color=#47B6FF>/ping #coolness</font><br />
<td valign=top>Gives information on the delay (round-trip) between you and everybody on #coolness.<p>

<tr>
<td valign=top><font color=#47B6FF>/ping Tomm</font><br />
<td valign=top>Gives information on the delay (round-trip) between you and just Tomm.<p>

<tr>
<td valign=top><font color=#47B6FF>/dcc chat MaryN</font><br />
<td valign=top>This sends MaryN a request for a dcc chat session. MaryN types <font color="#47B6FF">/dcc chat yournick</font> to complete the connection.
DCC chat is faster (lag free) and more secure than /msg.<p>

<tr>
<td valign=top><font color=#47B6FF>/msg =MaryN Hi there!</font><br />
<td valign=top>Once a DCC connection has been established, use the <font color="#47B6FF">/msg =nick message</font> format to exchange messages (note the = sign). DCC does not go through servers, so it are unaffected by server lag, net splits, etc.<p>
						   
<tr>
<td valign=top><font color=#47B6FF>/help</font><br />
<td valign=top>This works in many clients.	 Try it! <p>

<tr>
<td valign=top><font color=#47B6FF>/quit good night!</font><br />
<td valign=top>You quit IRC completely, with the parting comment so that others see &quot;*** Signoff: yournick (good night!)&quot;.<p>

</table>
</center>
<b>NOTE:</b>
When you are not in a named channel, lines not beginning with a /
have no effect, and many commands work differently or fail to work
altogether.<p>
       
<small>[ <a href="#contents">Contents</A> ]</SMALL><P>

</blockquote>

<hr>
<a name="where"><h2>4. Where to go</h2>
</a>
<blockquote>

You can learn a lot by joining a channel and just listening and
talking for a while.  For starters, try these channels: #new2irc,
#newuser, #newbies, or #chatback. Busier alternatives include:
#chat, and #ircbar.<p>

For help with the <a href="http://www.irchelp.org/irchelp/mirc/">mIRC</a> client, try
joining <a
href="http://www.mirc.co.uk/chat/n2mircef.chat">#new2mirc</a> or <a
href="http://www.mirc.co.uk/chat/mircheef.chat">#mirchelp</a>. For
help with general IRC questions, join #irchelp.<p>

To form your own channel with the name #mychannel (if #mychannel
does not already exist), type <font color=#47B6FF>/join #mychannel</font>. The channel
is created and you are automatically made an op.<p>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="smiley"><h2>5. Some smileys and jargon</h2>
</a>
<blockquote>
<font color=#47B6FF>:-)</font> is a smiley face, tilt your head to the left to see it.
Likewise, <font color=#47B6FF>:-(</font> is a frown.
<font color=#47B6FF>;-)</font> is a wink.
<font color=#47B6FF>:~~(</font>  is crying, while
 <font color=#47B6FF>:-P</font> is someone sticking their tongue out. <FONT COLOR=#47B6FF>:-P ~~</font> is drooling. 
<font color=#47B6FF>(-:</font> a lefty's smile, etc.
There are hundreds of these faces.<p>

Here are some common acronyms used in IRC:

<pre>
brb =  be right back                     bbiaf = be back in a flash
bbl =  be back later                     ttfn = ta ta for now
np  =  no problem                        imho = in my humble opinion
lol =  laughing out loud                 j/k = just kidding
re  =  hi again, as in 're hi'           wb = welcome back
wtf =  what the f--k                     rtfm = read the f--king manual
rofl = rolling on the floor laughing
</pre>
<p>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="advice"><h2>6. Some advice</h2>
</a>
<blockquote>

<dl>
<dt><b>Etiquette</b>

<dd>Typing in all caps, LIKE THIS, is considered &quot;shouting&quot;
	and should be avoided. Likewise, do not repeat yourself or
	otherwise &quot;flood&quot; the channel with many lines of text
	at once. Be sure to use correct terminology, e.g.,
	&quot;channel&quot;, not &quot;chat room&quot;, and
	&quot;nick&quot;, not &quot;handle&quot;.<p>

While in a channel, follow the lead of the channel ops there. If you
	antagonize them, you may be &quot;kicked&quot; off the channel
	forcibly and possibly &quot;banned&quot; from returning.  On the
	other hand, some channel ops are power-hungry and may kick or
	ban for no good reason.  If this happens, or if someone on a
	channel is bothering you, simply leave the channel -- there are
	thousands of others.<P>

<dt><b>Disconnected by /list?</b>
<dd>If you get disconnected when using the /list command, try
	switching servers, or else recent channel lists are available on
	the WWW at &lt;<a href="http://www.irchelp.org/irchelp/chanlist/">http://www.irchelp.org/irchelp/chanlist/</a>&gt;.<p>

<dt><b>Harassment and attacks</b>
<dd>If someone starts harassing or flooding you, leave the channel
	or use the /ignore command. For more details, <a
	href="http://www.irchelp.org/irchelp/mirc/">mIRC</a> users see our <a
	href="http://www.irchelp.org/irchelp/mirc/flood.html">flood protection</a> page, <a
	href="http://www.irchelp.org/irchelp/ircii/">ircII</a> users type <font
	color=#47B6FF><a href="http://www.irchelp.org/irchelp/ircii/commands/IGNORE">/help
	ignore</a></font>. It is a good idea to set your user mode to +i
	(invisible) to avoid unsolicited messages and harrassment -- if
	you are &quot;invisible&quot; generally only users on a channel
	with you can determine what nick you are using.<p>

If somebody else is crashing or disconnecting you, see our <a
	href="http://www.irchelp.org/irchelp/nuke/">Denial of Service or &quot;Nuke&quot;
	Attacks</a> page. You can also <a
	href="http://www.irchelp.org/irchelp/misc/irclog.html">log and report abuse</a> when
	it violates server rules, which you can read by typing /motd.<p>

</dl>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="server"><h2>7. IRC server problems, and choosing a server</h2>
</a>
<blockquote>
At this point, you are ready to &quot;chat&quot; on IRC.  For the most part, the commands above should suffice for beginners, but things can go wrong in IRC.<p>

<dl>

<dt><b>Net splits</b>
<dd><a href="http://www.irchelp.org/irchelp/networks/">Networks</a> can become divided
	(called a &quot;net split&quot;), thus separating you from users
	you had been speaking with.  These splits are often relatively
	short, though common some days.<P>

<dt><b>Lag</b>
<dd>A more frequent problem is &quot;lag&quot;, where there is a
	noticeable delay between the time you type something in and
	someone else reads it.  <a
	href="http://www.irchelp.org/irchelp/networks/servermap.html">Choosing a server</a>
	near you is one way to try to lessen lag. Lag can be measured by
	using the /ping command (see the commands section above). Once
	you find a better server, the command for changing servers is
	<font color=#47B6FF>/server server.name.here</font>.<p>

<dt><b><a href="http://www.irchelp.org/irchelp/networks/">Server Lists</a></b>
<dd>On most clients, typing <font color=#47B6FF>/links</font> gives
	a list of servers on your current net.  Use this command
	sparingly, no more than a couple times in a row, or you may
	mistaken for a &quot;link looking&quot; troublemaker. <p>

<dt><b>Ping? Pong!</b>
<dd><a href="http://www.irchelp.org/irchelp/mirc/">mIRC</a> users: <i>Ping? Pong!</i> in
	the status window just means your server pinged you to make sure
	you were still connected, and your client
	automatically replied with a pong. Don't worry about these.<p>

<dt><b>Reminder about DCC chat</b>
<dd>The /dcc chat command can be used to establish a one-on-one
	connection that avoids lag and will not be broken by a net
	split! Check your docs for usage info.  In most clients, you can
	set up a DCC chat connection by both typing <font
	color=#47B6FF>/dcc chat nick_of_other_person</font>.  To talk
	through that connection, type <font color=#47B6FF>/msg =nick
	whatever</font> (note the = sign). In <a
	href="http://www.irchelp.org/irchelp/mirc/">mIRC</a>, you can also start a DCC chat
	session by selecting <i>DCC</i> and then <i>Chat</i> from the
	menu and then entering the nick of the user with whom you wish
	to chat. A window opens for that dcc chat session.<p>

</dl>

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>


<hr>
<a name="help"><h2>8. More detailed help</h2>
</a>
<blockquote>

For further information about these issues, as well as about other
commands, visit the web site &lt;<a
href="http://www.irchelp.org">http://www.irchelp.org</a>&gt;. There
you can find many <a href="http://www.irchelp.org/irchelp/faq.html">help files</a>, such as:
<ul>
<li> <a href="http://www.irchelp.org/irchelp/ircprimer.html">IRC Primer</a>
<li> <a
href="http://www.irchelp.org/irchelp/altircfaq.html">FAQ (Frequently Asked Questions)</a> for <a href="news:alt.irc">alt.irc</a> newsgroup
<li> <a href="http://www.irchelp.org/irchelp/irctutorial.html">IRC tutorial</a>
</ul><p>

At that web site you will also find more advanced information for
specific IRC clients, including:
<ul>
<li><a
href="http://www.irchelp.org/irchelp/ircii/">ircII client</a> and <a
href="http://www.irchelp.org/irchelp/script/">ircII scripts</a>.
<li><a href="http://www.irchelp.org/irchelp/mac/">Mac clients</a>
<li><a href="http://www.irchelp.org/irchelp/mirc/">mIRC client</a> for Windows
</ul><p>

Looking for other clients? The most comprehensive source of clients
is at the <a href="ftp://ftp.undernet.org/pub/irc/clients/">Undernet
FTP archive</a> or <a href="http://clients.undernet.org/">Undernet
WWW archive</a>. The clients are organized into groups like Windows, Macintosh, DOS, Amiga, Java, etc.<p>

The <a href="http://www.irchelp.org/irchelp/mirc/">mIRC</a> client also has excellent
built-in help files written by Tjerk Vonck (mirc@dds.nl). Select
<i>Ircintro.hlp</i> from the <i>Help</i> menu.<p>

<!--
Good automated help is available via FreeSoft's client ai- on EFnet.
Type <FONT COLOR=#47B6FF>/msg ai- help</FONT> for a <A
HREF="/irchelp/helpirc.html">menu of choices</A>.<P>
-->

<small>[ <a href="#contents">Contents</a> ]</small><p>

</blockquote>

<hr>
<a name="warning"><h2>9. A word of warning</h2>
</a>
<blockquote>
<b>IRC scripts</b> are sets of commands that your client <i>will</i>
run.  Many otherwise good scripts have been hacked so that if you
load them, you can seriously compromise your security (someone can
get into your account, delete all of your files, read your mail,
etc.). There are also evildoers who try to send people viruses and
other bad things. Just like in real life, don't accept anything from
a stranger. There have been many incidents of this type, not just a
few. <b>Do not ever</b> run a script unless you know what each line
does, not even if it is given to you by a friend, as your friend may
not have the expertise to detect well-hidden &quot;trojans&quot;.<p>

<b>Automatic DCC get</b> is a very bad idea! Once it is on, you are
susceptible to dangers ranging from disconnection from your server
to giving someone else control of your computer. Quite a few people
have run into serious problems because of the DCC autoget setting.
<p>

<small>[ <a href="#contents">Contents</a> ]</small><p>
</blockquote>

<hr>

Special thanks to FreeSoft, prysm, hershey, turtle, Ariell, and
other #irchelp helpers on EFnet for their many helpful suggestions.
<p>

Now that you've read this beginner's guide, get on IRC and enjoy!
Or if you are interested in learning more, check out the many documents on the <a href="http://www.irchelp.org/">#IRChelp home page</a>.<p>


<hr>
<center>
