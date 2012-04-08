<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Hack This Site!<?php if (isset($title)): ?> | <?php echo $title; ?><?php endif; ?></title>
        
        <style type="text/css">
        code {
			border: 1px dashed grey;
			display: block;
			padding: 10px;
			margin: 10px;
		}
        </style>
    </head>
    
    <body>
        <a href="<?php echo Url::format('/'); ?>">Home</a>
        <center>
            <i><?php echo $randomQuote; ?></i><br />
        <?php
        if (Error::has()):
        foreach(Error::getAllNotices() as $notice): ?>
        <?php echo $notice; ?><br />
        <?php endforeach; foreach(Error::getAllErrors() as $error): ?>
        <?php echo $error; ?><br />
        <?php endforeach;endif; ?>
        
        <table border="1" width="800px;">
        <tr>
            <td width="20%" valign="top">
<?php
if (Session::isLoggedIn()):
?>
                Hello, <a href="<?php echo Url::format('user/view/'); ?>"><?php echo Session::getVar('username'); ?></a>!<br />
                <a href="<?php echo Url::format('user/settings'); ?>">Settings</a> - 
                <a href="<?php echo Url::format('/user/logout'); ?>">Logout</a>
<?php
else:
?>
                <form action="<?php echo Url::format('/user/login'); ?>" method="post">
                    Username: <input type="text" name="username" /><br />
                    Password: <input type="text" name="password" /><br />
                    <input type="submit" name="submit" value="Login" />
                </form><br />
                <a href="<?php echo Url::format('/user/register'); ?>">Register</a>
<?php
endif;
?>
                <hr />
<?php if (CheckAcl::can('viewAdminPanel')): ?>
    <?php if (CheckAcl::can('postNews')) : ?><a href="<?php echo Url::format('/news/post'); ?>">Post News</a><br /><?php endif; ?>
    <?php if (CheckAcl::can('approveArticles')): ?><a href="<?php echo Url::format('/article/approve'); ?>">Approve Articles</a><br /><?php endif; ?>
    <?php if (CheckAcl::can('postLectures')) : ?><a href="<?php echo Url::format('/lecture/post'); ?>">Post Lecture</a><br /><?php endif; ?>
    <?php if (CheckAcl::can('manageNotice')) : ?><a href="<?php echo Url::format('/notice'); ?>">Manage Notices</a><br /><?php endif; ?>
<hr />
<?php endif; ?>
<form action="<?php echo Url::format('search'); ?>" method="post">
    <input type="text" name="query" value="Search" /><input type="submit" name="submit" value="Go" />
</form>
<hr />
                <?php foreach ($leftNav as $title => $section): ?>
                <b><u><?php echo $title; ?></u></b><br />
                <?php foreach ($section as $name => $location): ?>
                <a href="<?php echo Url::format($location); ?>"><?php echo $name; ?></a><br />
                <?php endforeach; ?>
                <br />
                <?php endforeach; ?>
            </td>
            <td width="70%" valign="top"><?php echo $content;?></td>
        </tr>
        </table>
        
        <br /><br />
        Page rendered in <strong><?php echo $pageExecutionTime; ?></strong> seconds.</center>
    </body>
</html>
