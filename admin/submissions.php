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
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">

<?php include 'css/navbar.php'?>

<main class="d-flex justify-content-center align-items-start mt-4 flex-grow-1">
  <div class="text-start w-75">
 

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


 <div class="container mt-5">
  <div class="shadow p-5 mb-5 bg-white rounded border">
   <h3 class="mb-4">Submissions</h3>
    <form action="../config/uploadFile.php" method="POST" enctype="multipart/form-data">
      <!-- Title Input -->
 <div class="row mb-3">
  <!-- Title Input (longer) -->
  <div class="col-md-9">
    <label for="pdfTitle" class="form-label">Title</label>
    <input type="text" class="form-control" id="pdfTitle" name="title" placeholder="Enter title" required>
  </div>

  <!-- Year Input (shorter) -->
  <div class="col-md-3">
    <label for="yearInput" class="form-label">Year</label>
    <input type="number" class="form-control" id="yearInput" name="year" placeholder="Enter year" min="1900" max="2099" required>
  </div>
</div>


	<!-- Author Input -->
	<div class="mb-3">
	  <label for="authorInput" class="form-label">Author(s)</label>
	  <input type="text" class="form-control" id="authorInput" name="author" placeholder="Enter author(s)" required>
	  <div class="form-text">Separate multiple authors with commas.</div>
	</div>
     
      <div class="mb-3">
        <label for="pdfFile" class="form-label">Upload PDF</label>
        <input class="form-control" type="file" id="pdfFile" name="fileToUpload" accept=".pdf" required>
      </div>
<div class="row mb-3">
  <div class="col-md-6">
    <label for="departmentSelect" class="form-label">Department</label>
    <select name="department" class="form-select" id="departmentSelect" required>
      <option selected disabled value="">Choose...</option>
      <option value="School of Computing, Information Technology and Engineering">School of Computing, Information Technology and Engineering</option>
      <option value="School of Arts, Sciences, and Education">School of Arts, Sciences, and Education</option>
      <option value="School of Criminal Justice">School of Criminal Justice</option>
      <option value="School of Tourism and Hospitality Management">School of Tourism and Hospitality Management</option>
      <option value="School of Business and Accountancy">School of Business and Accountancy</option>
    </select>
  </div>

  <div class="col-md-6">
    <label for="categorySelect" class="form-label">Program</label>
    <select class="form-select" id="categorySelect" name="category" required>
      <option selected disabled value="">Choose a department first...</option>
    </select>
  </div>
</div>

<script>
  const programs = {
    "School of Computing, Information Technology and Engineering": [
      "Bachelor of Science in Civil Engineering",
      "Bachelor of Science in Computer Engineering",
      "Bachelor of Science in Computer Science",
      "Bachelor of Science in Information Technology",
      "Bachelor of Library and Information Science"
    ],
    "School of Arts, Sciences, and Education": [
      "Bachelor of Elementary Education",
      "Bachelor of Science in Development Communication",
      "Bachelor of Science in Psychology",
      "Bachelor of Secondary Education major in English",
      "Bachelor of Secondary Education major in Filipino",
      "Bachelor of Secondary Education major in Mathematics",
      "Bachelor of Secondary Education major in Science",
    ],
    "School of Criminal Justice": [
      "Bachelor of Science in Criminology"
    ],
    "School of Tourism and Hospitality Management": [
      "Bachelor of Science in Hospitality Management",
      "Bachelor of Science in Tourism Management"
    ],
    "School of Business and Accountancy": [
      "Bachelor of Science in Accountancy",
      "Bachelor of Science in Accounting Information System",
      "Bachelor of Science in Business Administration major in Financial Management",
      "Bachelor of Science in Business Administration major in Marketing Management"
    ]
  };

  const departmentSelect = document.getElementById('departmentSelect');
  const categorySelect = document.getElementById('categorySelect');

  departmentSelect.addEventListener('change', function () {
    const selectedDept = this.value;
    const options = programs[selectedDept] || [];

    categorySelect.innerHTML = '<option selected disabled value="">Choose...</option>';

    options.forEach(program => {
      const option = document.createElement('option');
      option.value = program;
      option.textContent = program;
      categorySelect.appendChild(option);
    });
  });
</script>


      <!-- Abstract -->
      <div class="mb-3">
        <label for="abstract" class="form-label">Abstract</label>
        <textarea class="form-control" id="abstract" name="abstract" rows="4" placeholder="Enter your abstract here" required></textarea>
      </div>

      <!-- Visible textarea to show extracted PDF text -->
      <div class="mb-3" style="display: none;">
        <textarea class="form-control" id="pdfText" name="ocrPdf" rows="10" placeholder="Extracted text will appear here..."></textarea>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn-outline-primary">Upload</button>
    </form>

  </div>
</div>


    </div>
  </div>
</main>

<!-- Footer -->
<?php include '../css/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- pdf.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.min.js"></script>

<script>
  const pdfFileInput = document.getElementById('pdfFile');
  const pdfTextArea = document.getElementById('pdfText');

  pdfFileInput.addEventListener('change', async (event) => {
    const file = event.target.files[0];

    if (!file || file.type !== 'application/pdf') {
      pdfTextArea.value = '';
      return;
    }

    try {
      const arrayBuffer = await file.arrayBuffer();
      const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

      let fullText = '';

      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();
        const pageText = textContent.items.map(item => item.str).join(' ');
        fullText += pageText + '\n\n';
      }

      // Show extracted text inside visible textarea
      pdfTextArea.value = fullText;

    } catch (error) {
      console.error('PDF text extraction error:', error);
      pdfTextArea.value = ''; // clear on error
    }
  });
</script>

</body>
</html>

<?php
} else {
  $_SESSION['error'] = "Invalid Token";
  header('Location: ../');
}
?>
