<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<title>Hack This Site!<?php if (isset($title)): ?> | <?php echo $title; ?><?php endif; ?></title>
<link rel="stylesheet" href="<?php echo Url::format('/themes/VectorLover/VectorLover.css', true); ?>" type="text/css" />

</head>

<body>
<!-- wrap starts here -->
<div id="wrap">

	<!--header -->
	<div id="header">			
				
		<h1 id="logo-text"><a href="<?php echo Url::format('/'); ?>">HackThisSite</a></h1>		
		<p id="slogan">put your site slogan here...</p>	
		
		<div id="top-menu">
			<p><a href="index.html">rss feed</a> | <a href="index.html">contact</a> | <a href="index.html">login</a></p>
		</div>			
				
	<!--header ends-->					
	</div>
		
	<!-- navigation starts-->	
	<div  id="nav">
		<ul>
			<li id="current"><a href="<?php echo Url::format('/'); ?>">Home</a></li>
			<li><a href="style.html">Style Demo</a></li>
			<li><a href="blog.html">Blog</a></li>
			<li><a href="index.html">Services</a></li>
			<li><a href="index.html">Support</a></li>
			<li><a href="index.html">About</a></li>		
		</ul>
	<!-- navigation ends-->	
	</div>					
			
	<!-- content starts -->
	<div id="content">
	
		<div id="main">
				
<?php echo $content; ?>			

		<!-- main ends -->	
		</div>
				
		<div id="sidebar">
		
			<h3>About</h3>			
			
			<p>
			<a href="index.html"><img src="images/gravatar.jpg" width="40" height="40" alt="image" class="float-left" /></a>
			Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec libero. Suspendisse bibendum. 
			Cras id urna. Morbi tincidunt, orci ac convallis aliquam, lectus turpis varius lorem, eu 
			posuere nunc justo tempus leo. Donec mattis, purus nec placerat bibendum, dui pede condimentum 
			odio, ac blandit ante orci ut diam.
			</p>	
			
			<h3>Search</h3>	
			
			<form id="qsearch" action="index.html" method="get" >
			<p>
			<label for="qsearch">Search:</label>
			<input class="tbox" type="text" name="qsearch" value="Search this site..." title="Start typing and hit ENTER" />
			<input class="btn" alt="Search" type="image" name="searchsubmit" title="Search" src="images/search.gif" />
			</p>
			</form>		
			
			<h3>Sidebar Menu</h3>
			<ul class="sidemenu">				
				<li><a href="index.html">Home</a></li>
				<li><a href="index.html#TemplateInfo">TemplateInfo</a></li>
				<li><a href="style.html">Style Demo</a></li>
				<li><a href="blog.html">Blog</a></li>
				<li><a href="http://www.dreamtemplate.com" title="Web Templates">Web Templates</a></li>
			</ul>

            <h3>Sponsors</h3>
			<ul class="sidemenu">
                <li><a href="http://www.dreamtemplate.com" title="Website Templates">DreamTemplate <br />
                <span>Over 6,000+ Premium Web Templates</span></a>
                </li>
                <li><a href="http://www.themelayouts.com" title="WordPress Themes">ThemeLayouts <br />
                <span>Premium WordPress &amp; Joomla Themes</span></a>
                </li>
                <li><a href="http://www.imhosted.com" title="Website Hosting">ImHosted.com <br />
                <span>Affordable Web Hosting Provider</span>
                </a></li>
                <li><a href="http://www.dreamstock.com" title="Stock Photos">DreamStock <br />
                <span>Download Amazing Stock Photos</span></a>
                </li>
                <li><a href="http://www.evrsoft.com" title="Website Builder">Evrsoft <br />
                <span>Website Builder Software &amp; Tools</span></a>
                </li>
                <li><a href="http://www.webhostingwp.com" title="Web Hosting">Web Hosting <br />
                <span>Top 10 Hosting Reviews</span></a>
                </li>
			</ul>
			
			<h3>Wise Words</h3>
			<p>&quot;<?php echo $randomQuote; ?>.&quot;</p>
						
		<!-- sidebar ends -->		
		</div>		
		
	<!-- content ends-->	
	</div>
		
	<!-- footer starts -->		
	<div id="footer">
						
			<p>
			&copy; All your copyright info here  
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<a href="http://www.bluewebtemplates.com/" title="Website Templates">website templates</a> by <a href="http://www.styleshout.com/">styleshout</a> |
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a> | 
			<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
			
   		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<a href="index.html">Home</a>&nbsp;|&nbsp;
   		<a href="index.html">Sitemap</a>&nbsp;|&nbsp;
	   	<a href="index.html">RSS Feed</a>
   		</p>			
	
	<!-- footer ends-->
	</div>

<!-- wrap ends here -->
</div>

</body>
</html>
