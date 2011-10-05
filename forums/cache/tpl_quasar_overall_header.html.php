<?php if (!defined('IN_PHPBB')) exit; ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo (isset($this->_rootref['S_CONTENT_DIRECTION'])) ? $this->_rootref['S_CONTENT_DIRECTION'] : ''; ?>" lang="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>" xml:lang="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>">
<head>

<meta http-equiv="content-type" content="text/html; charset=<?php echo (isset($this->_rootref['S_CONTENT_ENCODING'])) ? $this->_rootref['S_CONTENT_ENCODING'] : ''; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-language" content="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="copyright" content="2000, 2002, 2005, 2007 phpBB Group" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<?php echo (isset($this->_rootref['META'])) ? $this->_rootref['META'] : ''; ?>

<title><?php echo (isset($this->_rootref['SITENAME'])) ? $this->_rootref['SITENAME'] : ''; ?> &bull; <?php if ($this->_rootref['S_IN_MCP']) {  echo ((isset($this->_rootref['L_MCP'])) ? $this->_rootref['L_MCP'] : ((isset($user->lang['MCP'])) ? $user->lang['MCP'] : '{ MCP }')); ?> &bull; <?php } else if ($this->_rootref['S_IN_UCP']) {  echo ((isset($this->_rootref['L_UCP'])) ? $this->_rootref['L_UCP'] : ((isset($user->lang['UCP'])) ? $user->lang['UCP'] : '{ UCP }')); ?> &bull; <?php } echo (isset($this->_rootref['PAGE_TITLE'])) ? $this->_rootref['PAGE_TITLE'] : ''; ?></title>

<?php if ($this->_rootref['S_ENABLE_FEEDS']) {  if ($this->_rootref['S_ENABLE_FEEDS_OVERALL']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo (isset($this->_rootref['SITENAME'])) ? $this->_rootref['SITENAME'] : ''; ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_NEWS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_FEED_NEWS'])) ? $this->_rootref['L_FEED_NEWS'] : ((isset($user->lang['FEED_NEWS'])) ? $user->lang['FEED_NEWS'] : '{ FEED_NEWS }')); ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?mode=news" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_FORUMS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_ALL_FORUMS'])) ? $this->_rootref['L_ALL_FORUMS'] : ((isset($user->lang['ALL_FORUMS'])) ? $user->lang['ALL_FORUMS'] : '{ ALL_FORUMS }')); ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?mode=forums" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_TOPICS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_FEED_TOPICS_NEW'])) ? $this->_rootref['L_FEED_TOPICS_NEW'] : ((isset($user->lang['FEED_TOPICS_NEW'])) ? $user->lang['FEED_TOPICS_NEW'] : '{ FEED_TOPICS_NEW }')); ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?mode=topics" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_TOPICS_ACTIVE']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_FEED_TOPICS_ACTIVE'])) ? $this->_rootref['L_FEED_TOPICS_ACTIVE'] : ((isset($user->lang['FEED_TOPICS_ACTIVE'])) ? $user->lang['FEED_TOPICS_ACTIVE'] : '{ FEED_TOPICS_ACTIVE }')); ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?mode=topics_active" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_FORUM'] && $this->_rootref['S_FORUM_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_FORUM'])) ? $this->_rootref['L_FORUM'] : ((isset($user->lang['FORUM'])) ? $user->lang['FORUM'] : '{ FORUM }')); ?> - <?php echo (isset($this->_rootref['FORUM_NAME'])) ? $this->_rootref['FORUM_NAME'] : ''; ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?f=<?php echo (isset($this->_rootref['S_FORUM_ID'])) ? $this->_rootref['S_FORUM_ID'] : ''; ?>" /><?php } if ($this->_rootref['S_ENABLE_FEEDS_TOPIC'] && $this->_rootref['S_TOPIC_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo ((isset($this->_rootref['L_FEED'])) ? $this->_rootref['L_FEED'] : ((isset($user->lang['FEED'])) ? $user->lang['FEED'] : '{ FEED }')); ?> - <?php echo ((isset($this->_rootref['L_TOPIC'])) ? $this->_rootref['L_TOPIC'] : ((isset($user->lang['TOPIC'])) ? $user->lang['TOPIC'] : '{ TOPIC }')); ?> - <?php echo (isset($this->_rootref['TOPIC_TITLE'])) ? $this->_rootref['TOPIC_TITLE'] : ''; ?>" href="<?php echo (isset($this->_rootref['U_FEED'])) ? $this->_rootref['U_FEED'] : ''; ?>?f=<?php echo (isset($this->_rootref['S_FORUM_ID'])) ? $this->_rootref['S_FORUM_ID'] : ''; ?>&amp;t=<?php echo (isset($this->_rootref['S_TOPIC_ID'])) ? $this->_rootref['S_TOPIC_ID'] : ''; ?>" /><?php } } $this->_tpl_include('quasar_config.html'); ?>


<!--
	phpBB style name: quasar
	Based on style:   prosilver (this is the default phpBB3 style)
	Original author:  Tom Beddard ( http://www.subBlue.com/ )
	Modified by: RocketTheme, LLC (C) Copyright. All rights reserved.     
	
	NOTE: This page was generated by phpBB, the free open-source bulletin board package.
	      The phpBB Group is not responsible for the content of this page and forum. For more information
	      about phpBB please visit http://www.phpbb.com
-->

<script type="text/javascript">
// <![CDATA[
	var jump_page = '<?php echo ((isset($this->_rootref['LA_JUMP_PAGE'])) ? $this->_rootref['LA_JUMP_PAGE'] : ((isset($this->_rootref['L_JUMP_PAGE'])) ? addslashes($this->_rootref['L_JUMP_PAGE']) : ((isset($user->lang['JUMP_PAGE'])) ? addslashes($user->lang['JUMP_PAGE']) : '{ JUMP_PAGE }'))); ?>:';
	var on_page = '<?php echo (isset($this->_rootref['ON_PAGE'])) ? $this->_rootref['ON_PAGE'] : ''; ?>';
	var per_page = '<?php echo (isset($this->_rootref['PER_PAGE'])) ? $this->_rootref['PER_PAGE'] : ''; ?>';
	var base_url = '<?php echo (isset($this->_rootref['A_BASE_URL'])) ? $this->_rootref['A_BASE_URL'] : ''; ?>';
	var style_cookie = 'phpBBstyle';
	var style_cookie_settings = '<?php echo (isset($this->_rootref['A_COOKIE_SETTINGS'])) ? $this->_rootref['A_COOKIE_SETTINGS'] : ''; ?>';
	var onload_functions = new Array();
	var onunload_functions = new Array();

	<?php if ($this->_rootref['S_USER_PM_POPUP']) {  ?>

		if (<?php echo (isset($this->_rootref['S_NEW_PM'])) ? $this->_rootref['S_NEW_PM'] : ''; ?>)
		{
			var url = '<?php echo (isset($this->_rootref['UA_POPUP_PM'])) ? $this->_rootref['UA_POPUP_PM'] : ''; ?>';
			window.open(url.replace(/&amp;/g, '&'), '_phpbbprivmsg', 'height=225,resizable=yes,scrollbars=yes, width=400');
		}
	<?php } ?>


	/**
	* Find a member
	*/
	function find_username(url)
	{
		popup(url, 760, 570, '_usersearch');
		return false;
	}

	/**
	* New function for handling multiple calls to window.onload and window.unload by pentapenguin
	*/
	window.onload = function()
	{
		for (var i = 0; i < onload_functions.length; i++)
		{
			eval(onload_functions[i]);
		}
	}

	window.onunload = function()
	{
		for (var i = 0; i < onunload_functions.length; i++)
		{
			eval(onunload_functions[i]);
		}
	}

// ]]>
</script>
<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_SUPER_TEMPLATE_PATH'])) ? $this->_rootref['T_SUPER_TEMPLATE_PATH'] : ''; ?>/forum_fn.js"></script>
<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_SUPER_TEMPLATE_PATH'])) ? $this->_rootref['T_SUPER_TEMPLATE_PATH'] : ''; ?>/styleswitcher.js"></script>

<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/print.css" rel="stylesheet" type="text/css" media="print" title="printonly" />
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/gantry.css" rel="stylesheet" type="text/css" media="screen, projection" />
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/grid-12.css" rel="stylesheet" type="text/css" media="screen, projection" />
<!--[if IE 6]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/gantry-ie6.css" rel="stylesheet" type="text/css" media="screen, projection"  />
<![endif]-->
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/joomla.css" rel="stylesheet" type="text/css" media="screen, projection" />
<!--[if IE 6]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/joomla-ie6.css" rel="stylesheet" type="text/css" media="screen, projection"  />
<![endif]-->
<!--[if IE 7]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/joomla-ie7.css" rel="stylesheet" type="text/css" media="screen, projection" />	
<![endif]-->
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/joomla.css" rel="stylesheet" type="text/css" media="screen, projection" />
<!--[if IE 6]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/gantry_css/joomla-ie6.css" rel="stylesheet" type="text/css" media="screen, projection"  />
<![endif]-->
<link href="<?php echo (isset($this->_rootref['T_STYLESHEET_LINK'])) ? $this->_rootref['T_STYLESHEET_LINK'] : ''; ?>" rel="stylesheet" type="text/css" media="screen, projection" />
<!--[if IE 6]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/template-ie6.css" rel="stylesheet" type="text/css" media="screen, projection"  />
<![endif]-->
<!--[if IE 7]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/template-ie7.css" rel="stylesheet" type="text/css" media="screen, projection"  />
<![endif]-->
<!--[if IE 8]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/template-ie8.css" rel="stylesheet" type="text/css" media="screen, projection"  />	
<![endif]-->
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/<?php echo (isset($this->_tpldata['DEFINE']['.']['COLOR_VARIATION'])) ? $this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] : ''; ?>.css" rel="stylesheet" type="text/css" media="screen, projection" />
<!--[if IE 6]>
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/<?php echo (isset($this->_tpldata['DEFINE']['.']['COLOR_VARIATION'])) ? $this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] : ''; ?>-ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->

<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/small.css" rel="alternate stylesheet" type="text/css" title="A--" />
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/normal.css" rel="stylesheet" type="text/css" title="A" />
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/medium.css" rel="alternate stylesheet" type="text/css" title="A+" />
<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/large.css" rel="alternate stylesheet" type="text/css" title="A++" />
<style type="text/css">
<?php if ($this->_tpldata['DEFINE']['.']['QUASAR_LINK_COLOR']) {  ?>

  body a {color:<?php echo (isset($this->_tpldata['DEFINE']['.']['QUASAR_LINK_COLOR'])) ? $this->_tpldata['DEFINE']['.']['QUASAR_LINK_COLOR'] : ''; ?>;}
  body a, #rt-main-surround .rt-article-title, #rt-main-surround .title, #rt-showcase .title, #rt-showcase .showcase-title span, #rt-top .title, #rt-header .title, #rt-feature .title {color:<?php echo (isset($this->_tpldata['DEFINE']['.']['QUASAR_LINK_COLOR'])) ? $this->_tpldata['DEFINE']['.']['QUASAR_LINK_COLOR'] : ''; ?>;}
<?php } if ($this->_rootref['S_CONTENT_DIRECTION'] != ('rtl')) {  if ($this->_tpldata['DEFINE']['.']['AVATAR_POSITION'] == (left)) {  ?>

	.postprofile {float: left; border: 0px; margin-left: -6px;}
	.postbody {float: right;}
	ul.profile-icons {margin-right: 40px;}
	.online {background-position: 17% -1%;}
<?php } if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == (left) || $this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == (right)) {  ?>

    #postingbox .autowidth {width: 90% !important; }
	#postingbox .column2 { width: 75%; margin-top:-65px; position: relative;}
	#postingbox .column1 { width: 60%;position: relative; z-index:10000;}
	#postingbox fieldset.fields1 dd { margin-left: 15em;}
	#colour_palette dd { margin-left: 5em !important;}
<?php } } if ($this->_tpldata['DEFINE']['.']['SHOW_QUASAR_PATHWAY']) {  ?>

	#rt-feature .rt-block {margin-bottom: -10px !important; }
<?php } ?>

</style>
  
<!--[if IE 6]>
<style type="text/css">
  #postingbox .column2 { width: 75%; margin-top:0px; position: relative; margin-left: -300px;}
</style>
<![endif]-->
<!--[if IE 7]>
  <style type="text/css">
  #postingbox .column2 { width: 75%; margin-top:0px; position: relative; margin-left: -300px;} #message-box #message {width: 98%;} #cp-main {width:80%;}
</style>
<![endif]-->
  
<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_TEMPLATE_PATH'])) ? $this->_rootref['T_TEMPLATE_PATH'] : ''; ?>/rt_js/mootools-release-1.11.js"></script>

<?php if ($this->_tpldata['DEFINE']['.']['ENABLE_QUASAR_FONTSPANS']) {  ?>

<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_TEMPLATE_PATH'])) ? $this->_rootref['T_TEMPLATE_PATH'] : ''; ?>/rt_js/gantry-buildspans.js"></script>
<?php } ?>

<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_TEMPLATE_PATH'])) ? $this->_rootref['T_TEMPLATE_PATH'] : ''; ?>/rt_js/gantry-totop.js"></script>
<?php if ($this->_tpldata['DEFINE']['.']['ENABLE_QUASAR_FONTSPANS']) {  ?>

<script type="text/javascript">
window.addEvent('domready', function() {
    var modules = ['rt-block'];
    var header = ['h3','h2','h1'];
    GantryBuildSpans(modules, header);
}); 
</script>
<?php } ?>


<!--[if IE 6]>
<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_TEMPLATE_PATH'])) ? $this->_rootref['T_TEMPLATE_PATH'] : ''; ?>/rt_js/belated-png.js"></script>
<script type="text/javascript">
	window.addEvent('domready', function() {
		var pngClasses = ['.png', '#rt-logo', '.stuff', 'h2.title', '.rt-headline', '.feature-arrow-l', '.feature-arrow-r', '#rt-bottom', '.rt-main-inner', '.rokstories-tip','.icon','li.header','img','li','span','.shadow'];
		pngClasses.each(function(fixMePlease) {
			DD_belatedPNG.fix(fixMePlease);
		});
	});
</script>
</style>
<![endif]-->

<!--[if !(IE 6)]>
<script type="text/javascript" src="<?php echo (isset($this->_rootref['T_TEMPLATE_PATH'])) ? $this->_rootref['T_TEMPLATE_PATH'] : ''; ?>/rt_js/gantry-inputs.js"></script>
<script type="text/javascript">
    InputsExclusion.push('#rt-popup','#gallery','#postingbox fieldset.fields1 dd')
</script>
<![endif]-->

<?php if ($this->_rootref['S_CONTENT_DIRECTION'] == ('rtl')) {  ?>

	<link href="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/bidi.css" rel="stylesheet" type="text/css" media="screen, projection" />
<?php if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('full')) {  ?>	
	<style type="text/css">
	  .rtl .forabg, .rtl .forumbg {margin-left:10px;margin-right:0px;width:100%;}
	  <?php if ($this->_rootref['IE_VERSION'] == ('6')) {  ?>

	   .rtl .forabg, .rtl .forumbg {margin-left:-20px;margin-right:0px;padding-left:0px;width:100%;}
	   .rtl #wrap {position: static;}
	  <?php } ?>

	</style>
<?php } } if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('full')) {  ?>	
<style type="text/css">
    li.header dt {background-position: 3% 50%;}
	dd.lastpost  {width:22%;}
	ul.topiclist dt {width:56%;}
	<?php if ($this->_rootref['S_CONTENT_DIRECTION'] == ('rtl')) {  ?>

	    li.header dt {background-position: 99% 52% !important;}
	   .rtl .forabg {width: 97%;	/* fix for IE6 */}
       .rtl .forumbg {width: 97%;	/* fix for IE6 */}
    <?php } ?>

</style>
<?php } if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('fluid')) {  ?>

<style type="text/css">
    dd.lastpost  {width:13%;}
    ul.topiclist dt {width:68%;}
    .rt-container  {width: 97%;}
    .rt-container .rt-grid-12 {width:100%;}
    #rt-body-surround .rt-container,#rt-footer .rt-container,#rt-copyright .rt-container {width: 97%;}
    #rt-body-surround .rt-container .rt-grid-12 { width: 99%;}
    #rt-bottom .rt-grid-4,#rt-footer .rt-grid-4 {width: 32%;}
    .rt-container .rt-grid-2 {width:auto;float: left;}
    .rt-container .rt-grid-3 {width:auto;float: left;}
    .rt-container .rt-grid-10 {width:70%;float: right;}
    .rt-container .rt-grid-4 {width:auto;float: right;}
    .rt-container .rt-grid-8 {width:98%;}
    #rt-accessibility {float: right;}
    dl.icon dt {background-position: 12px 50%;}
</style>
<?php } if ($this->_tpldata['DEFINE']['.']['ROK_IMAGESET_SWITCHER']) {  if ($this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] == ('style5') || $this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] == ('style6')) {  ?>

<script type="text/javascript">
window.addEvent('domready', function() {
  var newImagePath = '<?php echo (isset($this->_rootref['T_IMAGESET_PATH'])) ? $this->_rootref['T_IMAGESET_PATH'] : ''; ?>quasar_red_imageset/imageset/'; // remember to add the ending forward slash
  var classToSearch = 'li.row dl.icon';
  var els = $$(classToSearch);
  if (els.length) {
    els.each(function(el) {
      var bg = el.getStyle('background-image'), newBg, imgSource;
      bg = bg.replace('")', '').replace(")", '');
      var tmp = bg.split('/');
      imgSource = tmp[tmp.length - 1];
      el.setStyle('background-image', 'url('+newImagePath+imgSource+')');     
    });
  }
});
</script>
<?php } if ($this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] == ('style3') || $this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] == ('style4')) {  ?>

<script type="text/javascript">
window.addEvent('domready', function() {
  var newImagePath = '<?php echo (isset($this->_rootref['T_IMAGESET_PATH'])) ? $this->_rootref['T_IMAGESET_PATH'] : ''; ?>quasar_green_imageset/imageset/'; // remember to add the ending forward slash
  var classToSearch = 'li.row dl.icon';
  var els = $$(classToSearch);
  if (els.length) {
    els.each(function(el) {
      var bg = el.getStyle('background-image'), newBg, imgSource;
      bg = bg.replace('")', '').replace(")", '');
      var tmp = bg.split('/');
      imgSource = tmp[tmp.length - 1];
      el.setStyle('background-image', 'url('+newImagePath+imgSource+')');     
    });
  }
});
</script>
<?php } } ?>

</head>

<body id="phpbb" class="section-<?php echo (isset($this->_rootref['SCRIPT_NAME'])) ? $this->_rootref['SCRIPT_NAME'] : ''; ?> <?php echo (isset($this->_rootref['S_CONTENT_DIRECTION'])) ? $this->_rootref['S_CONTENT_DIRECTION'] : ''; ?> backgroundlevel-<?php echo (isset($this->_tpldata['DEFINE']['.']['QUASAR_BG_LEVEL'])) ? $this->_tpldata['DEFINE']['.']['QUASAR_BG_LEVEL'] : ''; ?> bodylevel-<?php echo (isset($this->_tpldata['DEFINE']['.']['QUASAR_BODY_LEVEL'])) ? $this->_tpldata['DEFINE']['.']['QUASAR_BODY_LEVEL'] : ''; ?> cssstyle-<?php echo (isset($this->_tpldata['DEFINE']['.']['COLOR_VARIATION'])) ? $this->_tpldata['DEFINE']['.']['COLOR_VARIATION'] : ''; ?> font-family-$QUASAR_FONTFACE font-size-is-default col12">

<div id="rt-top">
   <div class="rt-container">
    <div id="rt-top2">
	    <?php if ($this->_tpldata['DEFINE']['.']['SHOW_QUASAR_FONT']) {  ?>

            <div class="rt-grid-<?php if ($this->_rootref['S_DISPLAY_SEARCH'] && ! $this->_rootref['S_IN_SEARCH']) {  ?>3<?php } else { ?>6<?php } ?> rt-omega">
        		<div class="rt-block">
			        <div id="rt-accessibility">
				        <div class="rt-desc"><?php echo ((isset($this->_rootref['L_CHANGE_FONT_SIZE'])) ? $this->_rootref['L_CHANGE_FONT_SIZE'] : ((isset($user->lang['CHANGE_FONT_SIZE'])) ? $user->lang['CHANGE_FONT_SIZE'] : '{ CHANGE_FONT_SIZE }')); ?></div>
				        <div id="rt-buttons">
                            <a href="#" onclick="fontsizeup(); return false;" onkeypress="return fontsizeup(event);" title="<?php echo ((isset($this->_rootref['L_CHANGE_FONT_SIZE'])) ? $this->_rootref['L_CHANGE_FONT_SIZE'] : ((isset($user->lang['CHANGE_FONT_SIZE'])) ? $user->lang['CHANGE_FONT_SIZE'] : '{ CHANGE_FONT_SIZE }')); ?>" class="large"><span class="button"></span></a>
                            <a href="#" onclick="fontsizedown(); return false;" onkeypress="return fontsizedown(event);"  title="<?php echo ((isset($this->_rootref['L_CHANGE_FONT_SIZE'])) ? $this->_rootref['L_CHANGE_FONT_SIZE'] : ((isset($user->lang['CHANGE_FONT_SIZE'])) ? $user->lang['CHANGE_FONT_SIZE'] : '{ CHANGE_FONT_SIZE }')); ?>" class="small"><span class="button"></span></a>
				        </div>
			        </div>
			        <div class="clear"></div>
				</div>
			</div>
		<?php } if ($this->_tpldata['DEFINE']['.']['SHOW_QUASAR_DATE']) {  ?>

		    <div class="rt-grid-5 rt-alpha rt-omega">
    		    <div class="date-block">
			        <span class="date"><?php if ($this->_rootref['S_USER_LOGGED_IN']) {  echo (isset($this->_rootref['LAST_VISIT_DATE'])) ? $this->_rootref['LAST_VISIT_DATE'] : ''; } else { echo (isset($this->_rootref['CURRENT_TIME'])) ? $this->_rootref['CURRENT_TIME'] : ''; } ?></span>
		        </div>
			</div>
		<?php } if ($this->_rootref['S_DISPLAY_SEARCH'] && ! $this->_rootref['S_IN_SEARCH']) {  ?>

            <div class="rt-grid-4 rt-omega">
                <div class="rt-block">
                    <div class="module-surround">
                        <div class="module-content">
                            <form action="<?php echo (isset($this->_rootref['U_SEARCH'])) ? $this->_rootref['U_SEARCH'] : ''; ?>" method="post" id="rokajaxsearch" class="light">
							    <fieldset>
	                                <div class="search">
                                        <input name="keywords" id="roksearch_search_str" type="text"  title="<?php echo ((isset($this->_rootref['L_SEARCH_KEYWORDS'])) ? $this->_rootref['L_SEARCH_KEYWORDS'] : ((isset($user->lang['SEARCH_KEYWORDS'])) ? $user->lang['SEARCH_KEYWORDS'] : '{ SEARCH_KEYWORDS }')); ?>" class="inputbox" value="<?php if ($this->_rootref['SEARCH_WORDS']) {  echo (isset($this->_rootref['SEARCH_WORDS'])) ? $this->_rootref['SEARCH_WORDS'] : ''; } else { echo ((isset($this->_rootref['L_SEARCH_MINI'])) ? $this->_rootref['L_SEARCH_MINI'] : ((isset($user->lang['SEARCH_MINI'])) ? $user->lang['SEARCH_MINI'] : '{ SEARCH_MINI }')); } ?>" onclick="if(this.value=='<?php echo ((isset($this->_rootref['LA_SEARCH_MINI'])) ? $this->_rootref['LA_SEARCH_MINI'] : ((isset($this->_rootref['L_SEARCH_MINI'])) ? addslashes($this->_rootref['L_SEARCH_MINI']) : ((isset($user->lang['SEARCH_MINI'])) ? addslashes($user->lang['SEARCH_MINI']) : '{ SEARCH_MINI }'))); ?>')this.value='';" onblur="if(this.value=='')this.value='<?php echo ((isset($this->_rootref['LA_SEARCH_MINI'])) ? $this->_rootref['LA_SEARCH_MINI'] : ((isset($this->_rootref['L_SEARCH_MINI'])) ? addslashes($this->_rootref['L_SEARCH_MINI']) : ((isset($user->lang['SEARCH_MINI'])) ? addslashes($user->lang['SEARCH_MINI']) : '{ SEARCH_MINI }'))); ?>';" />
									    <?php echo (isset($this->_rootref['S_SEARCH_HIDDEN_FIELDS'])) ? $this->_rootref['S_SEARCH_HIDDEN_FIELDS'] : ''; ?>

									</div>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="clear"></div>
    </div>
	</div>
</div>

<div id="rt-header">
    <div class="rt-container">
	    <div id="rt-header2">
		    <div id="rt-header3">
			    <div id="rt-header4">
				    <div class="rt-grid-2 rt-alpha">
					    <div class="rt-block"><a href="<?php if ($this->_tpldata['DEFINE']['.']['QUASAR_LOGO_LINK']) {  echo (isset($this->_tpldata['DEFINE']['.']['QUASAR_LOGO_LINK'])) ? $this->_tpldata['DEFINE']['.']['QUASAR_LOGO_LINK'] : ''; } else { echo (isset($this->_rootref['U_INDEX'])) ? $this->_rootref['U_INDEX'] : ''; } ?>" id="rt-logo"></a></div>
					</div>
                    <div class="rt-grid-10 rt-omega">
                        <div class="_menu">
                            <div class="rt-block">
                                <div class="module-surround">
                                    <div class="module-icon"></div>
                                    <div class="module-content">			
									    <ul class="menu">
										    <li class="item1 <?php if ($this->_rootref['SCRIPT_NAME'] == ('index')) {  ?>active<?php } ?> root"><a class="orphan item" href="<?php echo (isset($this->_rootref['U_INDEX'])) ? $this->_rootref['U_INDEX'] : ''; ?>" accesskey="h"><span><?php echo ((isset($this->_rootref['L_INDEX'])) ? $this->_rootref['L_INDEX'] : ((isset($user->lang['INDEX'])) ? $user->lang['INDEX'] : '{ INDEX }')); ?></span></a></li>
											<?php if (! $this->_rootref['S_IS_BOT'] && $this->_rootref['S_USER_LOGGED_IN']) {  ?><li class="item2 <?php if ($this->_rootref['SCRIPT_NAME'] == ('ucp')) {  ?>active<?php } ?> root"><a class="orphan item bullet" href="<?php echo (isset($this->_rootref['U_PROFILE'])) ? $this->_rootref['U_PROFILE'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_PROFILE'])) ? $this->_rootref['L_PROFILE'] : ((isset($user->lang['PROFILE'])) ? $user->lang['PROFILE'] : '{ PROFILE }')); ?>" accesskey="u"><span><?php echo ((isset($this->_rootref['L_PROFILE'])) ? $this->_rootref['L_PROFILE'] : ((isset($user->lang['PROFILE'])) ? $user->lang['PROFILE'] : '{ PROFILE }')); ?></span></a></li><?php } ?>

											<li class="item3 <?php if ($this->_rootref['SCRIPT_NAME'] == ('faq')) {  ?>active<?php } ?> root"><a class="orphan item bullet" href="<?php echo (isset($this->_rootref['U_FAQ'])) ? $this->_rootref['U_FAQ'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_FAQ_EXPLAIN'])) ? $this->_rootref['L_FAQ_EXPLAIN'] : ((isset($user->lang['FAQ_EXPLAIN'])) ? $user->lang['FAQ_EXPLAIN'] : '{ FAQ_EXPLAIN }')); ?>"><span><?php echo ((isset($this->_rootref['L_FAQ'])) ? $this->_rootref['L_FAQ'] : ((isset($user->lang['FAQ'])) ? $user->lang['FAQ'] : '{ FAQ }')); ?></span></a></li>
											<?php if (! $this->_rootref['S_IS_BOT']) {  if ($this->_rootref['S_DISPLAY_MEMBERLIST']) {  ?><li class="item2 <?php if ($this->_rootref['SCRIPT_NAME'] == ('memberlist')) {  ?>active<?php } ?> root"><a href="<?php echo (isset($this->_rootref['U_MEMBERLIST'])) ? $this->_rootref['U_MEMBERLIST'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_MEMBERLIST_EXPLAIN'])) ? $this->_rootref['L_MEMBERLIST_EXPLAIN'] : ((isset($user->lang['MEMBERLIST_EXPLAIN'])) ? $user->lang['MEMBERLIST_EXPLAIN'] : '{ MEMBERLIST_EXPLAIN }')); ?>" class="orphan item bullet"><span><?php echo ((isset($this->_rootref['L_MEMBERLIST'])) ? $this->_rootref['L_MEMBERLIST'] : ((isset($user->lang['MEMBERLIST'])) ? $user->lang['MEMBERLIST'] : '{ MEMBERLIST }')); ?></span></a></li><?php } if (! $this->_rootref['S_USER_LOGGED_IN'] && $this->_rootref['S_REGISTER_ENABLED'] && ! ( $this->_rootref['S_SHOW_COPPA'] || $this->_rootref['S_REGISTRATION'] )) {  ?><li class="item4 <?php if ($this->_rootref['S_REGISTRATION']) {  ?>active<?php } ?> root"><a class="orphan item bullet" href="<?php echo (isset($this->_rootref['U_REGISTER'])) ? $this->_rootref['U_REGISTER'] : ''; ?>"><span><?php echo ((isset($this->_rootref['L_REGISTER'])) ? $this->_rootref['L_REGISTER'] : ((isset($user->lang['REGISTER'])) ? $user->lang['REGISTER'] : '{ REGISTER }')); ?></span></a></li><?php } ?>

											    <li class="item5 <?php if ($this->_rootref['S_DISPLAY_FULL_LOGIN']) {  ?>active<?php } ?> root"><a class="orphan item bullet" href="<?php echo (isset($this->_rootref['U_LOGIN_LOGOUT'])) ? $this->_rootref['U_LOGIN_LOGOUT'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_LOGIN_LOGOUT'])) ? $this->_rootref['L_LOGIN_LOGOUT'] : ((isset($user->lang['LOGIN_LOGOUT'])) ? $user->lang['LOGIN_LOGOUT'] : '{ LOGIN_LOGOUT }')); ?>" accesskey="l"><span><?php echo ((isset($this->_rootref['L_LOGIN_LOGOUT'])) ? $this->_rootref['L_LOGIN_LOGOUT'] : ((isset($user->lang['LOGIN_LOGOUT'])) ? $user->lang['LOGIN_LOGOUT'] : '{ LOGIN_LOGOUT }')); ?></span></a></li>
                                            <?php } ?>

										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="rt-feature">
    <div class="rt-container">
        <div class="rt-grid-12 rt-alpha rt-omega"></div>
        <div class="clear"></div>
    </div>
</div>

<div id="rt-toptab">
    <div class="rt-container">
        <div class="clear"></div>
        <div class="rt-block">
            <div class="shadow">
                <div class="toptab">
                    <span class="toptab2"><?php echo (isset($this->_rootref['SITENAME'])) ? $this->_rootref['SITENAME'] : ''; ?></span>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

 <div id="rt-main-surround">
    <?php if ($this->_tpldata['DEFINE']['.']['SHOW_QUASAR_PATHWAY']) {  ?>

	    <div id="rt-breadcrumbs">
		    <div class="rt-container">
			    <div class="rt-breadcrumb-surround">
				    <a href="<?php echo (isset($this->_rootref['U_INDEX'])) ? $this->_rootref['U_INDEX'] : ''; ?>" id="breadcrumbs-home"></a><span class="breadcrumbs pathway"><span class="no-link"><?php echo ((isset($this->_rootref['L_INDEX'])) ? $this->_rootref['L_INDEX'] : ((isset($user->lang['INDEX'])) ? $user->lang['INDEX'] : '{ INDEX }')); ?></span><?php $_navlinks_count = (isset($this->_tpldata['navlinks'])) ? sizeof($this->_tpldata['navlinks']) : 0;if ($_navlinks_count) {for ($_navlinks_i = 0; $_navlinks_i < $_navlinks_count; ++$_navlinks_i){$_navlinks_val = &$this->_tpldata['navlinks'][$_navlinks_i]; ?> <img src="<?php echo (isset($this->_rootref['T_THEME_PATH'])) ? $this->_rootref['T_THEME_PATH'] : ''; ?>/images/arrow.png" alt=""  /><a href="<?php echo $_navlinks_val['U_VIEW_FORUM']; ?>"><?php echo $_navlinks_val['FORUM_NAME']; ?></a><?php }} ?></span>
				</div>
				<div class="clear"></div>
            </div>    
		</div>
	<?php } ?>

	<div id="rt-main" class="mb8-sa4">
        <div class="rt-container">
            <div class="rt-grid-<?php if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('full')) {  ?>12<?php } else { ?>8 <?php } if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == (left)) {  ?>rt-push-4<?php } ?>"> 
                <div class="rt-block">
                    <div id="rt-mainbody">
                        <div class="rt-joomla">
                            <div class="rt-article">
                                <div class="rt-headline">
                                    <h1 class="rt-article-title"><?php if ($this->_rootref['FORUM_NAME']) {  echo (isset($this->_rootref['FORUM_NAME'])) ? $this->_rootref['FORUM_NAME'] : ''; } else { echo (isset($this->_rootref['PAGE_TITLE'])) ? $this->_rootref['PAGE_TITLE'] : ''; } ?></h1>
                                    <div class="clear"></div>
                                </div>
								<div id="wrap">
								    <a id="top" name="top" accesskey="t"></a>
									    <div id="page-header">
										    <?php if ($this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('full') || $this->_tpldata['DEFINE']['.']['QUASAR_MENUPOSITION'] == ('fluid')) {  ?>

											    <div class="navbar">
												    <div class="inner">
													    <?php if (! $this->_rootref['S_IS_BOT'] && $this->_rootref['S_USER_LOGGED_IN']) {  ?>

														    <ul class="linklist leftside">
															    <li class="icon-ucp">
																    <a href="<?php echo (isset($this->_rootref['U_PROFILE'])) ? $this->_rootref['U_PROFILE'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_PROFILE'])) ? $this->_rootref['L_PROFILE'] : ((isset($user->lang['PROFILE'])) ? $user->lang['PROFILE'] : '{ PROFILE }')); ?>" accesskey="e"><?php echo ((isset($this->_rootref['L_PROFILE'])) ? $this->_rootref['L_PROFILE'] : ((isset($user->lang['PROFILE'])) ? $user->lang['PROFILE'] : '{ PROFILE }')); ?></a>
																	<?php if ($this->_rootref['S_DISPLAY_PM']) {  ?> (<a href="<?php echo (isset($this->_rootref['U_PRIVATEMSGS'])) ? $this->_rootref['U_PRIVATEMSGS'] : ''; ?>"><?php echo (isset($this->_rootref['PRIVATE_MESSAGE_INFO'])) ? $this->_rootref['PRIVATE_MESSAGE_INFO'] : ''; ?></a>)<?php } if ($this->_rootref['S_DISPLAY_SEARCH']) {  ?> &bull;
																	    <a href="<?php echo (isset($this->_rootref['U_SEARCH_SELF'])) ? $this->_rootref['U_SEARCH_SELF'] : ''; ?>"><?php echo ((isset($this->_rootref['L_SEARCH_SELF'])) ? $this->_rootref['L_SEARCH_SELF'] : ((isset($user->lang['SEARCH_SELF'])) ? $user->lang['SEARCH_SELF'] : '{ SEARCH_SELF }')); ?></a>
																	<?php } if ($this->_rootref['U_RESTORE_PERMISSIONS']) {  ?> &bull;
																	    <a href="<?php echo (isset($this->_rootref['U_RESTORE_PERMISSIONS'])) ? $this->_rootref['U_RESTORE_PERMISSIONS'] : ''; ?>"><?php echo ((isset($this->_rootref['L_RESTORE_PERMISSIONS'])) ? $this->_rootref['L_RESTORE_PERMISSIONS'] : ((isset($user->lang['RESTORE_PERMISSIONS'])) ? $user->lang['RESTORE_PERMISSIONS'] : '{ RESTORE_PERMISSIONS }')); ?></a>
																	<?php } ?>

																</li>
															</ul>
														<?php } ?>

														<ul class="linklist rightside">
														    <li class="icon-faq"><a href="<?php echo (isset($this->_rootref['U_FAQ'])) ? $this->_rootref['U_FAQ'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_FAQ_EXPLAIN'])) ? $this->_rootref['L_FAQ_EXPLAIN'] : ((isset($user->lang['FAQ_EXPLAIN'])) ? $user->lang['FAQ_EXPLAIN'] : '{ FAQ_EXPLAIN }')); ?>"><?php echo ((isset($this->_rootref['L_FAQ'])) ? $this->_rootref['L_FAQ'] : ((isset($user->lang['FAQ'])) ? $user->lang['FAQ'] : '{ FAQ }')); ?></a></li>
															<?php if (! $this->_rootref['S_IS_BOT']) {  if ($this->_rootref['S_DISPLAY_MEMBERLIST']) {  ?><li class="icon-members"><a href="<?php echo (isset($this->_rootref['U_MEMBERLIST'])) ? $this->_rootref['U_MEMBERLIST'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_MEMBERLIST_EXPLAIN'])) ? $this->_rootref['L_MEMBERLIST_EXPLAIN'] : ((isset($user->lang['MEMBERLIST_EXPLAIN'])) ? $user->lang['MEMBERLIST_EXPLAIN'] : '{ MEMBERLIST_EXPLAIN }')); ?>"><?php echo ((isset($this->_rootref['L_MEMBERLIST'])) ? $this->_rootref['L_MEMBERLIST'] : ((isset($user->lang['MEMBERLIST'])) ? $user->lang['MEMBERLIST'] : '{ MEMBERLIST }')); ?></a></li><?php } if (! $this->_rootref['S_USER_LOGGED_IN'] && $this->_rootref['S_REGISTER_ENABLED'] && ! ( $this->_rootref['S_SHOW_COPPA'] || $this->_rootref['S_REGISTRATION'] )) {  ?><li class="icon-register"><a href="<?php echo (isset($this->_rootref['U_REGISTER'])) ? $this->_rootref['U_REGISTER'] : ''; ?>"><?php echo ((isset($this->_rootref['L_REGISTER'])) ? $this->_rootref['L_REGISTER'] : ((isset($user->lang['REGISTER'])) ? $user->lang['REGISTER'] : '{ REGISTER }')); ?></a></li><?php } ?>

																<li class="icon-logout"><a href="<?php echo (isset($this->_rootref['U_LOGIN_LOGOUT'])) ? $this->_rootref['U_LOGIN_LOGOUT'] : ''; ?>" title="<?php echo ((isset($this->_rootref['L_LOGIN_LOGOUT'])) ? $this->_rootref['L_LOGIN_LOGOUT'] : ((isset($user->lang['LOGIN_LOGOUT'])) ? $user->lang['LOGIN_LOGOUT'] : '{ LOGIN_LOGOUT }')); ?>" accesskey="x"><?php echo ((isset($this->_rootref['L_LOGIN_LOGOUT'])) ? $this->_rootref['L_LOGIN_LOGOUT'] : ((isset($user->lang['LOGIN_LOGOUT'])) ? $user->lang['LOGIN_LOGOUT'] : '{ LOGIN_LOGOUT }')); ?></a></li>
															<?php } ?>

														</ul>
													</div>
												</div>
											<?php } ?>

										</div>
									</div>
									<a name="start_here"></a>
									<div id="page-body">
									    <?php if ($this->_rootref['S_BOARD_DISABLED'] && $this->_rootref['S_USER_LOGGED_IN'] && ( $this->_rootref['U_MCP'] || $this->_rootref['U_ACP'] )) {  ?>

										    <div id="information" class="rules">
											    <div class="inner"><span class="corners-top"><span></span></span>
												    <strong><?php echo ((isset($this->_rootref['L_INFORMATION'])) ? $this->_rootref['L_INFORMATION'] : ((isset($user->lang['INFORMATION'])) ? $user->lang['INFORMATION'] : '{ INFORMATION }')); ?>:</strong> <?php echo ((isset($this->_rootref['L_BOARD_DISABLED'])) ? $this->_rootref['L_BOARD_DISABLED'] : ((isset($user->lang['BOARD_DISABLED'])) ? $user->lang['BOARD_DISABLED'] : '{ BOARD_DISABLED }')); ?>

												<span class="corners-bottom"><span></span></span></div>
											</div>
										<?php } ?>