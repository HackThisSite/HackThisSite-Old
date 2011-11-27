<?php
$bbcode = new BBCode;

$admin = array();
if ($GLOBALS['permissions']->check('postNews'))
	array_push($admin, '<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/post_news/edit/' . $id->create(array('id' => (string) $entry['_id'], 'date' => $entry['date']), 'news') . '">Edit</a>');
if ($GLOBALS['permissions']->check('deleteNews')) 
	array_push($admin, '<a href="' . $GLOBALS['config']['baseUrl'] . 'admin/post_news/delete/' . $id->create(array('id' => (string) $entry['_id'], 'date' => $entry['date']), 'news') . '">Delete</a>');

$realAdmin = '';
if (!empty($admin))
	$realAdmin = '&nbsp;&nbsp;' . implode(' | ', $admin);
	
$comments = ($entry['commentable'] ? 'N/a comments' : 'comments disabled');	
?>
		<table border="0" width="100%" cellspacing="2" cellpadding="3">
			<tr>
				<td colspan="2">
					<span style="font-size: 18px"><b><?php echo $title; ?></b></span> <br />
				</td>
			</tr>

			<tr>
				<td colspan="2">
					Published by: HackThisSite Staff, on <?php echo Date::minuteFormat($date); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="article">

					<br /><div align="left"><?php echo $bbcode->parse($body, '#'); ?></div> <br /><br />
				</td>
			</tr>
			<tr style="font-size: 10px">
				<td width="30%">
					<?php echo $comments . $realAdmin; ?>
				</td>
			</tr>
		</table>
