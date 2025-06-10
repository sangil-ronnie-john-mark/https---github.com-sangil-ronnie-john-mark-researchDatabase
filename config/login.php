<?php
SESSION_START();
require_once 'dbcon.php';
$username = addslashes($_POST['username']);
$password = addslashes($_POST['password']);

$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	$_SESSION['login_status'] = true;
  Header('Location: ../admin');
} else {
  $_SESSION['error'] = "Invalid Username or Password!";
  Header('Location: ../');

}

$conn->close();

?>