<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Hack This Site!<?php if (isset($title)): ?> | <?php echo $title; ?><?php endif; ?></title>
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
        
        <table border="1" width="95%">
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
    <?php if (CheckAcl::can('postNews')) : ?><a href="<?php echo Url::format('/news/post'); ?>">Post News</a><?php endif; ?>
<hr />
<?php endif; ?>
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
        
        <br /><hr /><br />
        Page rendered in <strong><?php echo $pageExecutionTime; ?></strong> seconds.</center>
    </body>
</html>
