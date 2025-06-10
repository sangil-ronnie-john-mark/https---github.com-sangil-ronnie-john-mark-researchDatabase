<?php

require_once '../config/dbcon.php';
$search = $_POST['search'] ?? '';
$text1 = <<<EOT
$search
EOT;

// Stop word removal
function removeStopWords($text) {
    $stopWords = [
        'a','an','the','and','or','but','if','while','at','by','for','with','about','against','between','into',
        'through','during','before','after','above','below','to','from','up','down','in','out','on','off','over',
        'under','again','further','then','once','here','there','when','where','why','how','all','any','both',
        'each','few','more','most','other','some','such','no','nor','not','only','own','same','so','than','too',
        'very','can','will','just','don','should','now','what','which','who','whom','this','that','these','those',
        'is','am','are','was','were','be','been','being','have','has','had','having','do','does','did','doing'
    ];

    $words = preg_split('/\s+/', strtolower(strip_tags($text)));
    $filtered = array_diff($words, $stopWords);
    return implode(' ', $filtered);
}

// Word frequency
function getWordFrequencies($text) {
    $text = preg_replace('/[^a-z0-9 ]/', '', strtolower($text));
    $words = explode(' ', $text);
    $freq = [];
    foreach ($words as $word) {
        if (strlen($word) > 2) {
            $freq[$word] = ($freq[$word] ?? 0) + 1;
        }
    }
    return $freq;
}

// Cosine similarity
function cosineSimilarity($vec1, $vec2) {
    $dot = 0;
    $normA = 0;
    $normB = 0;

    $keys = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));

    foreach ($keys as $k) {
        $a = $vec1[$k] ?? 0;
        $b = $vec2[$k] ?? 0;
        $dot += $a * $b;
        $normA += $a * $a;
        $normB += $b * $b;
    }

    if ($normA == 0 || $normB == 0) return 0;
    return $dot / (sqrt($normA) * sqrt($normB));
}

// Preprocess user input
$userInputClean = removeStopWords($text1);
$userVec = getWordFrequencies($userInputClean);

// Fetch all research entries
$sql = "SELECT * FROM research";
$result = $conn->query($sql);

$similarPapers = [];

while ($row = $result->fetch_assoc()) {
    $dbText = removeStopWords($row['title'] . ' ' . $row['abstract']);
    $dbVec = getWordFrequencies($dbText);
    $similarity = cosineSimilarity($userVec, $dbVec);
    $percent = round($similarity * 100, 2);

    if ($percent > 0) {
        $row['similarity'] = $percent;
        $similarPapers[] = $row;
    }
}

// Sort by similarity descending
usort($similarPapers, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Similarity Check Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include 'css/navbar.php' ?>
<div class="container py-5">
    <h3 class="mb-4">Similarity Index</h3>

    <?php if (count($similarPapers) > 0): ?>
        <?php 
      
        $filteredPapers = array_filter($similarPapers, fn($p) => $p['similarity'] >= 25); 
        ?>

        <?php if (count($filteredPapers) > 0): ?>
            <?php foreach ($filteredPapers as $paper): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($paper['title']) ?></h5>
                        <p><strong>Similarity:</strong> <?= $paper['similarity'] ?>%</p>
						   <small>
							<strong>Authors:</strong> <?= htmlspecialchars($paper['authors']) ?> <br>
							<strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?> <br>
							<strong>Department:</strong> <?= htmlspecialchars($paper['Department']) ?> <br>
						  <strong>Program:</strong> <?= htmlspecialchars($paper['program']) ?>
						</small>
                        <p class="card-text text-muted"><?= htmlspecialchars(substr(strip_tags($paper['abstract']), 0, 1000)) ?>...</p>
                        <a href="../assets/upload/pdf/<?=$paper['filename']?>" class="btn btn-sm btn-outline-primary">View Paper</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No similar papers found above 20% similarity.</div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info">No papers found.</div>
    <?php endif; ?>
</div>

</body>
</html>
<?php include '../css/footer.php'; ?>