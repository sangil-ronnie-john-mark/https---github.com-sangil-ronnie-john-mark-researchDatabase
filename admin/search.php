<?php
SESSION_START();
if (!$_SESSION['login_status']) {
    $_SESSION['error'] = "Invalid Token";
    header('Location: ../');
    exit();
}

include '../config/dbcon.php'; // This must define $conn = new mysqli(...)

$search = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1); // Get current page, default to 1
$limit = 5; // Number of results per page
$offset = ($page - 1) * $limit;
$selectedYear = $_GET['year'] ?? ''; // Get selected year from sidebar
$yearRangeStart = $_GET['year_start'] ?? ''; // Get start year for custom range
$yearRangeEnd = $_GET['year_end'] ?? ''; // Get end year for custom range

// Initialize arrays for WHERE clauses, parameters, and types
$whereClauses = [];
$params = [];
$types = "";

// Build WHERE clause for search term
if ($search) {
    $whereClauses[] = " (title LIKE ? OR authors LIKE ? OR abstract LIKE ? OR Department LIKE ? OR program LIKE ? OR year LIKE ? OR ocrPdf LIKE ?) ";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
    $types .= "sssssss";
}

// Build WHERE clause for specific year selection
if ($selectedYear) {
    $whereClauses[] = " year = ? ";
    $params[] = $selectedYear;
    $types .= "s";
}

// Build WHERE clause for custom year range
if ($yearRangeStart && $yearRangeEnd) {
    $whereClauses[] = " year BETWEEN ? AND ? ";
    $params[] = $yearRangeStart;
    $params[] = $yearRangeEnd;
    $types .= "ss";
}

// Combine all WHERE clauses
$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = ' WHERE ' . implode(' AND ', $whereClauses);
}

// Prepare the count query
$countQuery = "SELECT COUNT(*) AS total FROM research " . $whereSql;
$countStmt = $conn->prepare($countQuery);
if (!$countStmt) {
    die("Prepare failed for count query: (" . $conn->errno . ") " . $conn->error);
}

// Bind parameters for the count query if any exist
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalResults = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalResults / $limit);
$countStmt->close();

// Default ORDER BY clause
$orderBySql = "ORDER BY year DESC";
$orderByParams = [];
$orderByTypes = "";

// If a search term is present, apply custom prioritization
if ($search) {
    $orderBySql = "
        ORDER BY
            CASE
                WHEN title LIKE ? THEN 1
                WHEN abstract LIKE ? THEN 2
                WHEN ocrPdf LIKE ? THEN 3
                ELSE 4
            END,
            year DESC
    ";
    // These parameters are specifically for the ORDER BY clause
    $orderByParams = [$like, $like, $like];
    $orderByTypes = "sss";
}

// Prepare the main data query with the dynamic ORDER BY
$query = "
    SELECT id, title, authors, year, abstract, filename, Department, program, ocrPdf
    FROM research
    " . $whereSql . "
    " . $orderBySql . "
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed for main query: (" . $conn->errno . ") " . $conn->error);
}

// Combine parameters: WHERE params + ORDER BY params + LIMIT/OFFSET params
$finalParams = array_merge($params, $orderByParams);
$finalTypes = $types . $orderByTypes;

// Add limit and offset params for pagination
$finalParams[] = $limit;
$finalParams[] = $offset;
$finalTypes .= "ii";

// Bind the combined parameters for the main query
if (!empty($finalTypes)) {
    $stmt->bind_param($finalTypes, ...$finalParams);
}

$stmt->execute();
$result = $stmt->get_result();

// Get the latest year from the database for the sidebar
$latestYearQuery = "SELECT MAX(year) AS latest_year FROM research";
$latestYearResult = $conn->query($latestYearQuery);
$latestYearRow = $latestYearResult->fetch_assoc();
$latestYear = $latestYearRow['latest_year'];

// Determine years to display in the sidebar
$yearsToShow = [];
if ($latestYear) {
    for ($i = 0; $i < 3; $i++) {
        if (($latestYear - $i) >= 1900) {
            $yearsToShow[] = $latestYear - $i;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - HCC Research Database</title>
    <link rel="stylesheet" href="../css/plugins.php">
    <style>
        /* Ensure html and body take full viewport height */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex; /* Make body a flex container */
            flex-direction: column; /* Stack children vertically */
        }

        /* The main content area that expands */
        .container-fluid.flex-grow-1 {
            flex-grow: 1; /* Allows this container to take up remaining vertical space */
            display: flex; /* Make it a flex container for its row child */
            flex-direction: column; /* Stack its children (the row) vertically */
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 0px;
        }

        /* The row containing sidebar and main content */
        .container-fluid > .row {
            flex-grow: 1; /* Allows the row to take up remaining space within its parent (container-fluid) */
            height: 100%; /* Ensures the row tries to fill 100% of its parent's height */
        }

        .sidebar {
            padding: 25px;
            /* Key properties for full height and stickiness */
            height: 100vh; /* Make sidebar take full viewport height */
            position: sticky; /* Makes it stick to the top as you scroll */
            top: 0; /* Aligns to the top of the viewport */
            z-index: 1020; /* Ensure it's above other content if necessary, below modals */
            overflow-y: auto; /* Enable scrolling for sidebar content if it overflows */
            box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Subtle shadow for depth */
        }

        .sidebar h5 {
            margin-top: 2px;
            margin-bottom: 15px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            display: block;
            padding: 8px 0;
            color: #000000;
            text-decoration: none;
        }
        .sidebar ul li a:hover {
            color: #263A56;
        }
        .sidebar .active-year {
            font-weight: bold;
        }
        /* Style for the form elements within the sidebar for better contrast */
        .sidebar form .form-control::placeholder {
            color: #cccccc;
        }
        .sidebar form .btn-primary {
            background-color: #263A56;
            border-color: #263A56;
        }
        .sidebar form .btn-primary:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
        }
        /* Adjust main content padding for alignment with sidebar */
        main.col-md-9 {
            padding-left: 30px; /* Add some left padding to main content */
        }
        .abstract-text {
            text-align: justify;
        }

        /* Adjustments for smaller screens if needed */
        @media (max-width: 767.98px) {
            .sidebar {
                position: relative; /* Remove sticky on small screens */
                height: auto; /* Auto height on small screens */
                border-bottom: 1px solid rgba(255,255,255,0.1); /* Separator */
                margin-bottom: 15px;
            }
            main.col-md-9 {
                padding-left: 15px; /* Default padding on small screens */
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include 'css/navbar.php'; ?>
    </header>

    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
            <nav class="col-md-3 col-lg-2 d-none d-md-block sidebar">
                <h5>Filter by Year</h5>
                <ul class="nav flex-column">
                    <?php foreach ($yearsToShow as $year): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($selectedYear == $year) ? 'active-year' : '' ?>"
                               href="?search=<?= urlencode($search) ?>&year=<?= $year ?>&year_start=<?= urlencode($yearRangeStart) ?>&year_end=<?= urlencode($yearRangeEnd) ?>">
                                <?= $year ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item">
                        <hr style="border-color: #5a7d9b;"> <form action="" method="GET">
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                            <div class="mb-2">
                                <label for="year_start" class="form-label">Custom Range (Start):</label>
                                <input type="number" id="year_start" name="year_start" class="form-control"
                                       value="<?= htmlspecialchars($yearRangeStart) ?>" placeholder="e.g., 2010">
                            </div>
                            <div class="mb-2">
                                <label for="year_end" class="form-label">Custom Range (End):</label>
                                <input type="number" id="year_end" name="year_end" class="form-control"
                                       value="<?= htmlspecialchars($yearRangeEnd) ?>" placeholder="e.g., 2015">
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 mt-2">Apply Filter</button>
                        </form>
                    </li>
                </ul>
            </nav>

            <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <form action="search.php" method="GET">
                    <div class="input-group mt-3">
                      <input value="<?=$search?>" type="text" name="search" class="form-control" placeholder="Search" required>
                      <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                      </button>
                    </div>
                </form>
                <h3>Search Results for: <em><?= htmlspecialchars($search) ?></em> (<?= $totalResults ?> results found)
                    <?php if ($selectedYear): ?>
                        in year <em><?= htmlspecialchars($selectedYear) ?></em>
                    <?php endif; ?>
                    <?php if ($yearRangeStart && $yearRangeEnd): ?>
                        from <em><?= htmlspecialchars($yearRangeStart) ?></em> to <em><?= htmlspecialchars($yearRangeEnd) ?></em>
                    <?php endif; ?>
                </h3>
                <hr>

                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Determine if "Track" or "Program" is appropriate
                    if ($row['Department'] == 'Senior High School') {
                        $specific = 'Track';
                    } else {
                        $specific = 'Program';
                    }
                    ?>
                        <div class="border rounded p-3 mb-4 shadow-sm">
                            <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                            <small>
                                <strong>Authors:</strong> <?= htmlspecialchars($row['authors']) ?> <br>
                                <strong>Year:</strong> <?= htmlspecialchars($row['year']) ?> <br>
                                <strong>Department:</strong> <?= htmlspecialchars($row['Department']) ?> <br>
                                <strong><?=$specific?>:</strong> <?= htmlspecialchars($row['program']) ?>
                            </small>
                            <p class="mt-3 abstract-text">
                                <?= htmlspecialchars(preg_replace('/\s+/', ' ', substr($row['abstract'], 0, 1000))) ?>...
                            </p>

                            <div class="d-flex justify-content-start align-items-center mt-3">
                                <a href="../assets/upload/pdf/<?=$row['filename']?>" class="btn btn-outline-primary me-2" target="_blank">
                                    View PDF
                                </a>
                                <form action="../config/delete.php?file=<?=$row['id']?>" method="POST" onsubmit="return confirm('Are you sure you want to delete \'<?= addslashes($row['title']) ?>\'?');">
                                    <input type="hidden" name="filename" value="<?=$row['filename']?>">
                                    <input type="hidden" name="id" value="<?=$row['id']?>">
                                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>

                            <?php if (!empty($row['ocrPdf'])): ?>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php
                            // Base URL for pagination links
                            $paginationBaseUrl = "?search=" . urlencode($search) .
                                "&year=" . urlencode($selectedYear) .
                                "&year_start=" . urlencode($yearRangeStart) .
                                "&year_end=" . urlencode($yearRangeEnd);
                            ?>

                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="<?= $paginationBaseUrl ?>&page=<?= $page - 1 ?>"
                                       aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="<?= $paginationBaseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="<?= $paginationBaseUrl ?>&page=<?= $page + 1 ?>"
                                       aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                <?php else: ?>
                    <p>No results found. Try another search term or year filter.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer class="mt-auto">
        <?php include '../css/footer.php'; ?>
    </footer>
</body>
</html>