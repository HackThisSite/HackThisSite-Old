<?php
if (!empty($valid) && $valid):
?>
<u><h2>Edit User:  <?php echo $user['username']; ?></h2></u>

<form action="<?php echo Url::format('/user/settings/save'); ?>" method="post">
<?php if (CheckAcl::can('changeUsername')): ?>
	<b>Username: </b><input type="text" name="username" value="<?php echo htmlentities($user['username'], ENT_QUOTES, '', false); ?>" /><br />
<?php endif; ?>
    <b>Email:  </b><input type="text" name="email" value="<?php echo htmlentities($user['email'], ENT_QUOTES, '', false); ?>"/><br />
    <b>Hide your email?  </b> <input type="checkbox" name="hideEmail" value="true"<?php echo ($user['hideEmail'] ? ' checked="checked"' : ''); ?> /><br />
<?php if (CheckAcl::can('editAcl')): ?>
    <b>Group:  </b><select name="group">
<?php foreach (acl::$acls as $acl): ?>
        <option value="<?php echo $acl; ?>"<?php echo ($acl == $user['group'] ? 'selected="selected"' : ''); ?>><?php echo ucwords($acl); ?></option>
<?php endforeach; ?>
    </select><br />
    <?php endif; ?>
    
    <br />
	<b><u>Change Password</u></b><br />
	<b>Old Password:  </b><input type="text" name="oldpassword" /><br />
	<b>New Password:  </b><input type="text" name="password" /><br />
    <input type="submit" name="submit" value="Save" />
</form>

<br /><hr /><br />
<b><u>Add a Certificate</u></b><br />
<form action="<?php echo Url::format('user/addkey'); ?>" method="post">
	<textarea cols="65" rows="15" name="csr">The contents of your CSR</textarea><br />
	<input type="submit" name="submit" value="Submit" />
</form>
<br />

<b><u>Certificates</u></b><br />

<table border="1" style="width: 100%">
<?php if (!empty($user['certs'])): 
foreach ($user['certs'] as $cert): ?>
	<tr<?php if ($secure && $clientSSLKey == $cert['certKey']): ?> style="background-color: LightGreen" title="In-Use"<?php endif; ?>>
		<td style="width: 1%"><?php echo $cert['serialNumber']; ?></td>
		<td><?php echo $cert['hash']; ?></td>
		<td><?php echo $cert['subject']['organizationName']; ?></td>
		<td><?php echo Date::dayFormat($cert['validFrom_time_t']); ?></td>
		<td><?php echo Date::dayFormat($cert['validTo_time_t']); ?></td>
		<td style="width: 1%">
			<form action="<?php echo Url::format('/user/viewCert'); ?>" method="post">
				<input type="hidden" name="hash" value="<?php echo $cert['certKey']; ?>" />
				<input type="submit" value="View" />
			</form>
		</td>
		<td style="width: 1%">
			<form action="<?php echo Url::format('/user/rmCert'); ?>" method="post">
				<input type="hidden" name="hash" value="<?php echo $cert['certKey']; ?>" />
				<input type="submit" value="Delete" />
			</form>
		</td>
	</tr>
<?php endforeach;
else: ?>
	<tr><td><center>You have no certificates.</center></td></tr>
<?php endif; ?>
</table>
<a href="<?php echo Url::format('pages/info/keyauthentication'); ?>">About</a><br />

<br /><hr />
<b><u>Security Settings</u></b><br />

<form action="<?php echo Url::format('/user/settings/saveAuth'); ?>" method="post">
	<input type="checkbox" name="passwordAuth" <?php if (in_array('password', $user['auths'])): ?>checked="checked"<?php endif; ?> />&nbsp;
	Password authentication<br />
	<input type="checkbox" name="certificateAuth" <?php if (in_array('certificate', $user['auths'])): ?>checked="checked"<?php endif; ?> />&nbsp;
	Certificate authentication<br />
	<input type="checkbox" name="certAndPassAuth" <?php if (in_array('cert+pass', $user['auths'])): ?>checked="checked"<?php endif; ?> />&nbsp;
	Certificate and Password authentication<br />
	<input type="checkbox" name="autoAuth" <?php if (in_array('autoauth', $user['auths'])): ?>checked="checked"<?php endif; ?> />&nbsp;
	Automatically authenticate you on TLS.<br />
	<input type="submit" value="Save" /><br />
	<a href="<?php echo Url::format('pages/info/keyauthentication#auths'); ?>">More info...</a><br />
</form>

<?php
endif;
?>
