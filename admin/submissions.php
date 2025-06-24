<?php
SESSION_START();
if ($_SESSION['login_status']) {
include '../css/plugins.php'; // Ensure this includes your other CSS/plugins
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title>HCC Research Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Example: Adjust textarea height for smaller screens if default is too large */
        @media (max-width: 767.98px) {
            textarea.form-control {
                height: 200px !important; /* Adjust as needed for smaller screens */
            }
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

<?php include 'css/navbar.php'?>

<main class="d-flex justify-content-center align-items-start mt-4 flex-grow-1 py-4"> <div class="container"> <?php if (isset($_SESSION['success'])): ?>
            <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <script>
                // Make sure Bootstrap JS is loaded before this runs
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(() => {
                        const alertElement = document.getElementById('success-alert');
                        if (alertElement) {
                            const alert = bootstrap.Alert.getOrCreateInstance(alertElement);
                            alert.close();
                        }
                    }, 3000);
                });
            </script>
        <?php
            unset($_SESSION['success']);
        endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(() => {
                        const alertElement = document.getElementById('error-alert');
                        if (alertElement) {
                            const alert = bootstrap.Alert.getOrCreateInstance(alertElement);
                            alert.close();
                        }
                    }, 5000);
                });
            </script>
        <?php
            unset($_SESSION['error']);
        endif;
        ?>

        <div class="shadow p-3 p-md-5 mb-5 bg-white rounded border"> <h3 class="mb-4">Submissions</h3>
            <form action="../config/uploadFile.php" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-12 col-md-9 mb-3 mb-md-0"> <label for="pdfTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="pdfTitle" name="title" placeholder="Enter title" required>
                    </div>

                    <div class="col-12 col-md-3"> <label for="yearInput" class="form-label">Year</label>
                        <input type="number" class="form-control" id="yearInput" name="year" placeholder="Enter year" min="1900" max="2099" required>
                    </div>
                </div>

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
                    <div class="col-12 col-md-6 mb-3 mb-md-0"> <label for="departmentSelect" class="form-label">Department</label>
                        <select name="department" class="form-select" id="departmentSelect" required>
                            <option selected disabled value="">Choose...</option>
                            <option value="Senior High School">Senior High School</option>
                            <option value="School of Computing, Information Technology and Engineering">School of Computing, Information Technology and Engineering</option>
                            <option value="School of Arts, Sciences, and Education">School of Arts, Sciences, and Education</option>
                            <option value="School of Criminal Justice">School of Criminal Justice</option>
                            <option value="School of Tourism and Hospitality Management">School of Tourism and Hospitality Management</option>
                            <option value="School of Business and Accountancy">School of Business and Accountancy</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6"> <label for="categorySelect" class="form-label">Program</label>
                        <select class="form-select" id="categorySelect" name="category" required>
                            <option selected disabled value="">Choose a department first...</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="abstract" class="form-label">Abstract</label>
                    <textarea class="form-control" id="abstract" name="abstract" rows="4" placeholder="Enter your abstract here" required></textarea>
                </div>

                <div class="mb-3" style="display: none;">
                    <textarea class="form-control" id="pdfText" name="ocrPdf" rows="10" placeholder="Extracted text will appear here..."></textarea>
                </div>

                <button type="submit" class="btn btn-outline-primary">Upload</button>
            </form>
        </div>
    </div>
</main>

<?php include '../css/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.min.js"></script>

<script>
    const programs = {
        "Senior High School": [
            "Accountancy, Business, and Management",
            "Science, Technology, Engineering, and Mathematics",
            "Humanities and Social Sciences",
            "General Academic Strand",
            "Technical-Vocational-Livelihood - Home Economics",
            "Technical-Vocational-Livelihood - Information and Communications Technology",
        ],
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
            "Bachelor of Science in Marketing Management"
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

    // PDF.js script
    const pdfFileInput = document.getElementById('pdfFile');
    const pdfTextArea = document.getElementById('pdfText');

    // Ensure pdfjsLib is available (it's loaded via the CDN in <head>)
    if (typeof pdfjsLib !== 'undefined') {
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
    } else {
        console.error("pdfjsLib is not defined. Make sure pdf.min.js is loaded correctly.");
    }

</script>

</body>
</html>

<?php
} else {
    $_SESSION['error'] = "Invalid Token";
    header('Location: ../');
}
?>