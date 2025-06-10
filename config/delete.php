<?php
SESSION_START();
require_once 'dbcon.php';
$filename = $_POST['filename'];
$id = $_POST['id'];
$sql = "DELETE FROM research WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
  unlink('../assets/upload/pdf/'.$filename);
  $_SESSION['success'] = "Research Deleted Successfully!";
  Header('Location: ../admin/');
} else {
  $_SESSION['error'] = "Error";
  Header('Location: ../admin/');
}

mysqli_close($conn);
?>