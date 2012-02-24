<h1>Link To Us</h1>

<p>
You could make an image that links to HackThisSite:
<a href='<?php echo Config::get('other:baseUrl'); ?>'>
<img src='<?php echo Url::format('/images/hts_80x15.gif'); ?>' alt='Hack This Site!'/>
</a>
</p>

<p>
Code:
<input readonly="readonly" value = "&lt;a href='<?php echo Config::get('other:baseUrl'); ?>'&gt;&lt;img src='http://www.hackthissite.org/images/hts_80x15.gif' alt='Hack This Site!'/&gt;&lt;/a&gt;"/>
</p>

<p>
Or you could make another image that links to HackThisSite:
<a href='<?php echo Url::format('/'); ?>'><img src='<?php echo Config::get('other:baseUrl'); ?>/images/hts_80x15_2.gif' alt='Hack This Site!'/></a>
</p>

<p>
Code:
<input readonly="readonly" value = "&lt;a href='<?php echo Config::get('other:baseUrl'); ?>'&gt;&lt;img src='http://www.hackthissite.org/images/hts_80x15_2.gif' alt='Hack This Site!'/&gt;&lt;/a&gt;"/>
</p>

<p>
Or you could make a plaintext link to HackThisSite:
<a href='<?php echo Url::format('/'); ?>'>Hack This Site!</a>
</p>

<p>
Code:
<input readonly="readonly" value = "&lt;a href='<?php echo Config::get('other:baseUrl'); ?>/'&gt;Hack This Site!&lt;/a&gt;"/>
</p>
