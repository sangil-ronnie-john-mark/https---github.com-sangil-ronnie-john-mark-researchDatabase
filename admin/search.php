<?php
SESSION_START();
if (!$_SESSION['login_status']) {
    $_SESSION['error'] = "Invalid Token";
    header('Location: ../loginPage.php');
    exit();
}

include '../config/dbcon.php'; // This must define $conn = new mysqli(...)

$search = trim($_GET['search'] ?? '');

if (!$search) {
    echo "No search query provided.";
    exit();
}

// Basic multi-column LIKE search
$query = "
    SELECT id, title, authors, year, abstract, filename, Department, program, ocrPdf
    FROM research 
    WHERE 
        title LIKE ? OR 
        authors LIKE ? OR 
        abstract LIKE ? OR 
		Department LIKE ? OR 
        program LIKE ? OR 
        year LIKE ? OR 
        ocrPdf LIKE ? 
    ORDER BY year DESC
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$like = "%$search%";
$stmt->bind_param("sssssss", $like, $like, $like, $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - HCC Research Database</title>
    <link rel="stylesheet" href="../css/plugins.php">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include 'css/navbar.php'; ?>

<main class="container mt-5 mb-5 flex-grow-1">
    <h3>Search Results for: <em><?= htmlspecialchars($search) ?></em></h3>
    <hr>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="border rounded p-3 mb-4 shadow-sm">
                <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                <small>
                    <strong>Authors:</strong> <?= htmlspecialchars($row['authors']) ?> <br>
                    <strong>Year:</strong> <?= htmlspecialchars($row['year']) ?> <br>
                    <strong>Department:</strong> <?= htmlspecialchars($row['Department']) ?> <br>
				  <strong>Program:</strong> <?= htmlspecialchars($row['program']) ?>
                </small>
                <p class="mt-3 abstract-text">
					<?= htmlspecialchars(preg_replace('/\s+/', ' ', substr($row['abstract'], 0, 1000))) ?>...
				</p>

			<form action="../config/delete.php?file=<?=$row['id']?>" method="POST">
				<a href="../assets/upload/pdf/<?=$row['filename']?>" class="btn btn-outline-primary" target="_blank">
					View PDF
				</a>
				<input type="hidden" name="filename" value="<?=$row['filename']?>">
				<input type="hidden" name="id" value="<?=$row['id']?>">
				<input type="submit" class="btn btn-outline-danger" value="Delete" onclick="return confirm('Are you sure you want to delete <?=$row['title']?>?')">
			</form>
			
			


                <?php if (!empty($row['ocrPdf'])): ?>
                    
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No results found. Try another search term.</p>
    <?php endif; ?>
</main>

<?php include '../css/footer.php'; ?>
</body>
</html>
