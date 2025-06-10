<?php
SESSION_START();
$_SESSION['login_status'] = false;

Header('Location: ../');
?>