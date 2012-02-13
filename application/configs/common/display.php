<?php
return array(
    "display:layout" => "main",
    "display:leftnav" => array(
        "Challenges" => array(
            "Basic"       => "missions/basic", /*
            "Realistic"   => "#",
            "Application" => "#",
            "Programming" => "#",
            "Phreaking"   => "#",
            "ExtBasic"    => "#",
            "Javascript"  => "#",
            "Stego"       => "#",
            "IRC"         => "#", */
        ),
        'Get Informed' => array(
            'News'     => 'news/view/' . date('Y') . '/',
            'Articles' => 'article',
            'Lectures' => 'lecture',
            'HackThisZine' => 'http://hackbloc.org/?action=zine'
        ),
        'Get Involved' => array(
            'Donate to HackThisSite!' => '/pages/info/donate',
            'Store' => 'http://www.cafepress.com/htsstore',
            'Submit Article' => 'article/post',
            'Submit Bug Report' => 'bugs',
        ),
        'Communicate' => array(
            'Forums' => 'forums',
            'Private Messages' => 'forums/ucp.php?i=pm',
            'IRC IdleRPG' => 'http://www.irc.hackthissite.org/idlerpg',
            'IRC Quotes' => 'http://qdb.hackthissite.org/',
            'IRC Command Reference' => 'pages/info/reference'
        ),
        'About HTS' => array(
            'About the Project' => 'pages/info/guide',
            'Bill of Rights' => 'pages/info/billofrights',
            'Legal Disclaimer' => 'pages/info/legal',
            'Privacy Statements' => 'pages/info/privacy',
            'Link to Us' => 'pages/info/linktous',
            'Under the Hood' => 'pages/info/underthehood',
            'Staff Charter' => 'pages/info/staffcharter',
        )
    )
);
