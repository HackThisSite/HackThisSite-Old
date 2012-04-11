<?php if (!empty($password)): ?>
Your password is now:  <b><?php echo $password; ?></b>
<?php elseif ($password === false): ?>
You may now login with your usual username/password.
<?php endif; ?>
