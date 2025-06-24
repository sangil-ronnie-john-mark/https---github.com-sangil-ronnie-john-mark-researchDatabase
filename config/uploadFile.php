<?php
SESSION_START();
require_once 'dbcon.php'; // Assuming dbcon.php establishes a PDO connection or a mysqli connection in $conn

// Sanitize and retrieve POST data
$title = ucfirst(strtolower($_POST['title']));
$year = addslashes($_POST['year']);
$author = addslashes($_POST['author']);
$department = addslashes($_POST['department']);
$category = addslashes($_POST['category']);
$abstract = addslashes($_POST['abstract']);
$ocrPdf = addslashes($_POST['ocrPdf']); // This contains the extracted text

$targetDir = "../assets/upload/pdf/";
$fileName = basename($_FILES["fileToUpload"]["name"]);
$targetFile = $targetDir . $fileName;
$uploadOk = 1;

$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
$allowedTypes = ['pdf'];

// Check if file type is allowed
if (!in_array($fileType, $allowedTypes)) {
    $_SESSION['error'] = "Invalid File Format. Only PDF Files are allowed to upload.";
    header('Location: ../admin/submissions.php');
    exit(); // Stop execution
}

// Check for duplicate filename
$stmt_filename = $conn->prepare("SELECT COUNT(*) FROM research WHERE filename = ?");
$stmt_filename->bind_param("s", $fileName);
$stmt_filename->execute();
$stmt_filename->bind_result($count_filename);
$stmt_filename->fetch();
$stmt_filename->close();

if ($count_filename > 0) {
    $_SESSION['error'] = "Upload failed: A file with the name '<strong>" . htmlspecialchars($fileName) . "</strong>' already exists. Please rename your file or upload a different one.";
    header('Location: ../admin/submissions.php');
    exit(); // Stop execution
}

// Check for duplicate title
$stmt_title = $conn->prepare("SELECT COUNT(*) FROM research WHERE title = ?");
$stmt_title->bind_param("s", $title);
$stmt_title->execute();
$stmt_title->bind_result($count_title);
$stmt_title->fetch();
$stmt_title->close();

if ($count_title > 0) {
    $_SESSION['error'] = "Upload failed: A research paper with the title '<strong>" . htmlspecialchars($title) . "</strong>' already exists.";
    header('Location: ../admin/submissions.php');
    exit(); // Stop execution
}

// Check for duplicate abstract (using the extracted ocrPdf text for similarity)
$stmt_abstract = $conn->prepare("SELECT COUNT(*) FROM research WHERE ocrPdf = ?");
$stmt_abstract->bind_param("s", $ocrPdf);
$stmt_abstract->execute();
$stmt_abstract->bind_result($count_abstract);
$stmt_abstract->fetch();
$stmt_abstract->close();

if ($count_abstract > 0) {
    $_SESSION['error'] = "Upload failed: A research paper with a very similar abstract already exists. Please check your submission or revise the abstract.";
    header('Location: ../admin/submissions.php');
    exit(); // Stop execution
}

// If all checks pass, proceed with file upload and database insertion
if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO research (title, year, authors, Department, program, abstract, filename, ocrPdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssss", $title, $year, $author, $department, $category, $abstract, $fileName, $ocrPdf);

    if ($stmt->execute()) {
        $_SESSION['success'] = "The file " . htmlspecialchars($fileName) . " has been uploaded successfully.";
        header('Location: ../admin/submissions.php');
        exit(); // Stop execution
    } else {
        // If database insertion fails, delete the uploaded file to prevent orphans
        unlink($targetFile);
        $_SESSION['error'] = "Failed to insert data into database. Please try again.";
        header('Location: ../admin/submissions.php');
        exit(); // Stop execution
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Sorry, your file was not uploaded due to an unexpected error.";
    header('Location: ../admin/submissions.php');
    exit(); // Stop execution
}

// Close the connection if it's still open
if ($conn) {
    $conn->close();
}
?>