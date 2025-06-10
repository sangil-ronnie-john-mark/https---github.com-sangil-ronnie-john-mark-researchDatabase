<?php
SESSION_START();
$filename = $_GET['filename'];
if (isset($filename)) {
$file = '../assets/upload/pdf/'.$filename;
?>

<embed src="<?=$file?>" type="application/pdf" width="100%" height="600px" />

<?php

} else {
	if ($_SESSION['login_status']) {
		Header('Location: index.php');
	} else {
		Header('Location: ../');
	}
}
?>