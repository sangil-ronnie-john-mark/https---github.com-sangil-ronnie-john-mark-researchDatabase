<?php
SESSION_START();
require_once 'dbcon.php';

$title = ucfirst(strtolower($_POST['title']));
$year = addslashes($_POST['year']);
$author = addslashes($_POST['author']); 
$department = addslashes($_POST['department']);
$category = addslashes($_POST['category']);
$abstract = addslashes($_POST['abstract']);
$ocrPdf = addslashes($_POST['ocrPdf']);


$targetDir = "../assets/upload/pdf/";
$targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileName = basename($_FILES["fileToUpload"]["name"]);

$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
$allowedTypes = ['pdf'];

if (!in_array($fileType, $allowedTypes)) {
     $_SESSION['error'] = "Invalid File Format. Only PDF Files are allowed to upload.";
	  Header('Location: ../admin/submissions.php');
    $uploadOk = 0;
}

if ($uploadOk && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO research (title, year, authors, Department, program, abstract, filename, ocrPdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssss", $title, $year, $author, $department ,$category, $abstract, $fileName, $ocrPdf);

    if ($stmt->execute()) {
        $_SESSION['success'] = "The file " . htmlspecialchars($fileName) . " has been uploaded successfully.";
		Header('Location: ../admin/submissions.php');
    } else {
        echo "Failed to insert data into database.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Sorry, your file was not uploaded.";
}
?>
