<?php
SESSION_START();
if ($_SESSION['login_status']) {
include '../css/plugins.php'; // Ensure this includes Bootstrap CSS and JS
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title>HCC Research Database System</title>
</head>
<body class="d-flex flex-column h-100">

<?php include 'css/navbar.php'?>

<main class="flex-grow-1 d-flex justify-content-center align-items-center bg-light py-4"> <div class="container"> <div class="text-center bg-white p-3 p-md-5 rounded shadow mx-auto" style="max-width: 600px;"> <img src="../assets/images/rdu.png" class="img-fluid mb-3" style="max-height: 100px;" alt="RDU Logo"> <h4>SIMILARITY SEARCH</h4>

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
    </div>
</main>

<?php include '../css/footer.php'; ?>
</body>
</html>
<?php
} else {
    $_SESSION['error'] = "Invalid Token";
    Header('Location: ../');
}
?>