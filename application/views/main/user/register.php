<?php if (!empty($valid) && $valid): ?>
<h2><u>Register</u></h2>

<form action="<?php echo Url::format('/user/register/save'); ?>" method="post">
    <table border="0">
        <tr>
            <th>Username</th>
            <td><input type="text" name="username" /></td>
        </tr>
        <tr>
            <th>Password</th>
            <td><input type="text" name="password" /></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><input type="text" name="email" /></td>
        </tr>
        <tr>
            <th>Hide Your Email?</th>
            <td>
                <input type="checkbox" name="hideEmail" value="true" /> Yes
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <input type="submit" name="submit" value="Register" />
                <input type="reset" value="Reset" />
            </th>
        </tr>
    </table>
</form>
<?php endif; ?>
