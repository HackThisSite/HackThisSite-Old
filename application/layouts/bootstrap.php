<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Hack This Site!<?php if (isset($title)): ?> | <?php echo $title; ?><?php endif; ?></title>

		<!-- Le styles -->
		<link href="<?php echo Url::format('themes/bootstrap/css/bootstrap.min.css', true); ?>" rel="stylesheet">
		<style type="text/css">
		  body {
			padding-top: 60px;
			padding-bottom: 40px;
		  }
		  .sidebar-nav {
			padding: 9px 0;
		  }
		</style>
		<link href="<?php echo Url::format('themes/bootstrap/css/bootstrap-responsive.min.css', true); ?>" rel="stylesheet">
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- Start header -->
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</a>
					<a class="brand" href="<?php echo Url::format('/'); ?>">HackThisSite</a>
					
					<div class="nav-collapse">
						<ul class="nav">
							<li><a href="<?php echo Url::format('/'); ?>">Home</a></li>
<?php if (Session::isLoggedIn()): ?>
							<li><a href="<?php echo Url::format('/user/settings'); ?>">Settings</a></li>
							<li><a href="<?php echo Url::format('/user/logout'); ?>">Logout</a></li>
<?php endif; ?>
						</ul>
						
<?php if (Session::isLoggedIn()): ?>
						<p class="navbar-text pull-right">Logged in as 
						<a href="<?php echo Url::format('/user/view/' . Session::getVar('username')); ?>">
							<?php echo Session::getVar('username'); ?>
						</a></p>
<?php else: ?>
						<p class="navbar-text pull-right">
							<a href="<?php echo Url::format('/user/login'); ?>">Login</a>
						</p>
<?php endif; ?>
					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>
		<!-- End header -->
		
		<div class="container"><div class="row">
			<!-- Start navigation -->
			<div class="span3">
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
<?php if (CheckAcl::can('viewAdminPanel')): ?>
						<li class="nav-header">Admin Panel</li>
    <?php if (CheckAcl::can('postNews')) : ?><li><a href="<?php echo Url::format('/news/post'); ?>">Post News</a></li><?php endif; ?>
    <?php if (CheckAcl::can('approveArticles')): ?><li><a href="<?php echo Url::format('/article/approve'); ?>">Approve Articles</a></li><?php endif; ?>
    <?php if (CheckAcl::can('postLectures')) : ?><li><a href="<?php echo Url::format('/lecture/post'); ?>">Post Lecture</a></li><?php endif; ?>
    <?php if (CheckAcl::can('manageNotice')) : ?><li><a href="<?php echo Url::format('/notice'); ?>">Manage Notices</a></li><?php endif; ?>
    <?php if (CheckAcl::can('viewStats')) : ?><li><a href="<?php echo Url::format('/stats'); ?>">View Stats</a></li><?php endif; ?>
<?php endif; ?>
						<li class="nav-header">Search</li>
						<li>
							<form class="form-search" action="<?php echo Url::format('search'); ?>" method="post">
								<input type="text" name="query" placeholder="Search" class="input-medium search-query" />
							</form>
						</li>
<?php foreach ($leftNav as $title => $section): ?>
						<li class="nav-header"><?php echo $title; ?></li>
<?php foreach ($section as $name => $location): ?>
						<li><a href="<?php echo Url::format($location); ?>"><?php echo $name; ?></a></li>
<?php endforeach;endforeach; ?>
					</ul>
				</div>
                
                <h4>
                    <img src="<?php echo Url::format('/twitter.png', true); ?>" />&nbsp;
                    HackThisSite Twitter
                </h4>
                
                <p><?php echo implode('<hr />', $tweets); ?></p>
			</div>
			<!-- End navigation -->
			
			<!-- Start content -->
			<div class="span9">
<?php
$notices = Error::getAllNotices();
if (Error::has() && !empty($notices)) {
	echo '<div class="alert alert-info">';

	foreach($notices as $notice) { 
		echo $notice, '<br />';
	}
	echo '</div><br />';
}

$errors = Error::getAllErrors();
if (Error::has() && !empty($errors)) {
	echo '<div class="alert alert-error">';
	foreach($errors as $error) {
		echo $error, '<br />';
	}
	echo '</div><br />';
}
?>
				<?php echo $content; ?>
			</div>
			<!-- End content -->
		</div></div>
		
		<script src="<?php echo Url::format('themes/jquery.js', true); ?>"></script>
		<script src="<?php echo Url::format('themes/bootstrap/js/bootstrap.min.js', true); ?>"></script>
	</body>
</html>
