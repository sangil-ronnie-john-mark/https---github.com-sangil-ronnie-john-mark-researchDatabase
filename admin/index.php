<?php
SESSION_START();
if ($_SESSION['login_status']) {
include '../css/plugins.php';
?>
<html lang="en" class="h-100">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HCC Research Database System</title>
</head>
<body class="d-flex flex-column h-100">

<?php include 'css/navbar.php'?>

 <?php if (isset($_SESSION['error'])): ?>
    <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(() => {
            const alertElement = document.getElementById('error-alert');
            if (alertElement) {
                const alert = bootstrap.Alert.getOrCreateInstance(alertElement);
                alert.close();
            }
        }, 3000);
    </script>
<?php 
    unset($_SESSION['error']);
endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(() => {
            const alertElement = document.getElementById('success-alert');
            if (alertElement) {
                const alert = bootstrap.Alert.getOrCreateInstance(alertElement);
                alert.close();
            }
        }, 3000);
    </script>
<?php 
    unset($_SESSION['success']);
endif; ?>

<main class="flex-grow-1 d-flex justify-content-center align-items-center">
  <div  class="text-center w-50">
	<img src="../assets/images/rdu.png" height="200px" alt="RDU Logo">
    <h3>HOLY CROSS COLLEGE</h3>
    <h3>RESEARCH DATABASE SYSTEM</h3>
	
	<form action="search.php" method="GET">
		<div class="input-group mt-3">
		  <input type="text" name="search" class="form-control" placeholder="Search" required>
		  <button class="btn btn-primary" type="submit">
			<i class="bi bi-search"></i>
		  </button>
		</div>
	</form>
	
  </div>
</main>
<?php include '../css/footer.php'; ?>
</body>
<?php
} else {
	$_SESSION['error'] = "Invalid Token";
	Header('Location: ../');
}
?>