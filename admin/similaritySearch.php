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



<main class="flex-grow-1 d-flex justify-content-center align-items-center bg-light">
  <div class="text-center w-50 bg-white p-5 rounded shadow">
	<img src="../assets/images/rdu.png" height="100px" alt="RDU Logo">
    <h4>SIMILARITY SEARCH</h4>

    <form action="similarity.php" method="POST">
      <textarea 
        name="search" 
        class="form-control mb-3" 
        placeholder="Enter abstract here..." 
        required 
        style="height: 300px; resize: none;"></textarea>
      <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-search"></i> Search
      </button>
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