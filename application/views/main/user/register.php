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
            <td><i>Confirm</i></td>
            <td><input type="text" name="retypePassword" /></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><input type="text" name="email" /></td>
        </tr>
        <tr>
            <td><i>Confirm</i></td>
            <td><input type="text" name="retypeEmail" /></td>
        </tr>
        <tr>
            <th>Hide Your Email?</th>
            <td>
                <input type="radio" name="hideEmail" value="1" checked="checked" /> Yes
                <input type="radio" name="hideEmail" value="0" /> No
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
