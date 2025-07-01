<?php

$password = "@hehhaoo9pnjk"; 


session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (!empty($password)) {
    if (isset($_POST['password'])) {
        if (password_verify($_POST['password'], password_hash($password, PASSWORD_DEFAULT))) {
            $_SESSION['logged_in'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // Display login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login</title>
            <script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>body { font-family: 'Inter', sans-serif; }</style>
        </head>
        <body class="bg-gray-100 flex items-center justify-center h-screen">
            <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">Research and Development Unit</h1>
                <form method="post">
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// --- Helper Functions ---
function deleteDir($dirPath) {
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

// --- Core Logic & Action Handling ---
$basePath = __DIR__;
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';
$fullPath = realpath($basePath . '/' . $currentPath);

if ($fullPath === false || strpos($fullPath, $basePath) !== 0) {
    $currentPath = '';
    $fullPath = $basePath;
}
$currentPath = trim(str_replace($basePath, '', $fullPath), '/');

$message = isset($_GET['message']) ? $_GET['message'] : '';

// Handle file saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_file'])) {
    $fileToSave = $_POST['file_path'];
    $content = $_POST['content'];
    $fullFileToSavePath = $basePath . '/' . $fileToSave; // Use direct path for new files

    if (strpos(realpath(dirname($fullFileToSavePath)), $basePath) !== 0) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: Invalid file path.</div>';
    } else {
        file_put_contents($fullFileToSavePath, $content);
        $parentDir = dirname($fileToSave) === '.' ? '' : dirname($fileToSave);
        $successMessage = urlencode('<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">File saved successfully!</div>');
        header('Location: ?path=' . urlencode($parentDir) . '&message=' . $successMessage);
        exit;
    }
}

// Handle file creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_file'])) {
    $newFileName = basename(trim($_POST['filename']));
    if ($newFileName) {
        $newFilePath = $fullPath . '/' . $newFileName;
        if (!file_exists($newFilePath)) {
            touch($newFilePath);
            header('Location: ?edit=' . urlencode(ltrim($currentPath . '/' . $newFileName, '/')));
            exit;
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: File already exists.</div>';
        }
    }
}

// Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    $newFolderName = basename(trim($_POST['foldername']));
    if ($newFolderName) {
        $newFolderPath = $fullPath . '/' . $newFolderName;
        if (!file_exists($newFolderPath)) {
            mkdir($newFolderPath, 0755);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Folder created successfully.</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: Folder already exists.</div>';
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $itemToDelete = $_GET['delete'];
    $fullItemPath = realpath($basePath . '/' . $itemToDelete);
    $parentDir = dirname($itemToDelete) === '.' ? '' : dirname($itemToDelete);

    if ($fullItemPath && strpos($fullItemPath, $basePath) === 0) {
        try {
            if (is_dir($fullItemPath)) {
                deleteDir($fullItemPath);
                $successMessage = urlencode('<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Folder deleted successfully.</div>');
            } else {
                unlink($fullItemPath);
                $successMessage = urlencode('<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">File deleted successfully.</div>');
            }
            header('Location: ?path=' . urlencode($parentDir) . '&message=' . $successMessage);
            exit;
        } catch (Exception $e) {
            $errorMessage = urlencode('<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: ' . $e->getMessage() . '</div>');
            header('Location: ?path=' . urlencode($parentDir) . '&message=' . $errorMessage);
            exit;
        }
    }
}

// Handle file editing
if (isset($_GET['edit'])) {
    $fileToEdit = $_GET['edit'];
    $fullFileToEditPath = $basePath . '/' . $fileToEdit; // No realpath to allow new files

    if (strpos(realpath(dirname($fullFileToEditPath)), $basePath) !== 0) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $content = file_exists($fullFileToEditPath) && is_file($fullFileToEditPath) ? htmlspecialchars(file_get_contents($fullFileToEditPath)) : '';
    $fileExtension = pathinfo($fileToEdit, PATHINFO_EXTENSION);
    $modes = [
        'php' => 'application/x-httpd-php',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'html' => 'text/html',
        'css' => 'text/css',
        'xml' => 'application/xml',
        'sql' => 'text/x-sql'
    ];
    $editorMode = isset($modes[$fileExtension]) ? $modes[$fileExtension] : 'text/plain';

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit File: <?php echo basename($fileToEdit); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material-darker.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>

        <style>
            body { font-family: 'Inter', sans-serif; }
            .CodeMirror {
                border: 1px solid #ddd;
                height: 70vh;
                font-size: 16px;
                border-radius: 0.375rem;
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">Editing: <span class="font-mono text-blue-600"><?php echo htmlspecialchars(basename($fileToEdit)); ?></span></h1>
                    <a href="?path=<?php echo urlencode(dirname($fileToEdit) === '.' ? '' : dirname($fileToEdit)); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">&larr; Back to Explorer</a>
                </div>
                <form method="post" id="editor-form">
                    <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($fileToEdit); ?>">
                    <input type="hidden" name="save_file" value="1">
                    <textarea name="content" id="code-editor"><?php echo $content; ?></textarea>
                    <div class="mt-4"><button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Save Changes</button></div>
                </form>
            </div>
        </div>
        <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("code-editor"), {
                lineNumbers: true,
                mode: '<?php echo $editorMode; ?>',
                theme: "material-darker",
                lineWrapping: true,
                indentUnit: 4
            });
            // Update textarea before form submission
            document.getElementById('editor-form').addEventListener('submit', function() {
                editor.save();
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// --- Display File Explorer ---
$items = @scandir($fullPath) ?: [];
$directories = [];
$files = [];
$viewableExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp', 'svg']; // Added viewable file extensions

foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $itemPath = $fullPath . '/' . $item;
    $relativePath = ltrim($currentPath . '/' . $item, '/');
    if (is_dir($itemPath)) {
        $directories[] = ['name' => $item, 'path' => $relativePath];
    } else {
        $files[] = ['name' => $item, 'path' => $relativePath, 'size' => filesize($itemPath)];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>PHP File Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">PHP File Explorer</h1>
                    <?php if (!empty($password)): ?>
                        <a href="?logout=1" class="text-sm bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded focus:outline-none focus:shadow-outline">Logout</a>
                    <?php endif; ?>
                </div>
                <nav class="text-gray-500 text-sm mt-2" aria-label="Breadcrumb">
                    <ol class="list-none p-0 inline-flex">
                        <li class="flex items-center"><a href="?path=" class="hover:text-blue-500">Root</a></li>
                        <?php
                        $pathParts = explode('/', $currentPath); $builtPath = '';
                        foreach ($pathParts as $part) {
                            if (empty($part)) continue;
                            $builtPath .= $part . '/';
                            echo '<li class="flex items-center"><span class="mx-2">/</span><a href="?path=' . urlencode(rtrim($builtPath, '/')) . '" class="hover:text-blue-500">' . htmlspecialchars($part) . '</a></li>';
                        }
                        ?>
                    </ol>
                </nav>
            </div>

            <div class="p-6">
                <?php if ($message) echo urldecode($message); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <form method="post" class="flex items-center space-x-2">
                        <input type="text" name="filename" placeholder="new-file.php" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <button type="submit" name="create_file" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline whitespace-nowrap">Create File</button>
                    </form>
                    <form method="post" class="flex items-center space-x-2">
                        <input type="text" name="foldername" placeholder="new-folder" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <button type="submit" name="create_folder" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline whitespace-nowrap">Create Folder</button>
                    </form>
                </div>

                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Permissions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($currentPath)): $parentPath = dirname($currentPath); if ($parentPath === '.') $parentPath = ''; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10v10a1 1 0 001 1h16a1 1 0 001-1V10M3 10l9-7 9 7"></path></svg><a href="?path=<?php echo urlencode($parentPath); ?>" class="text-blue-600 hover:text-blue-900 font-medium">.. (Parent Directory)</a></div></td>
                                <td class="hidden sm:table-cell"></td><td class="hidden sm:table-cell"></td><td></td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($directories as $dir): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><svg class="w-6 h-6 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg><a href="?path=<?php echo urlencode($dir['path']); ?>" class="text-blue-600 hover:text-blue-900 font-medium"><?php echo htmlspecialchars($dir['name']); ?></a></div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">Folder</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell font-mono"><?php echo substr(sprintf('%o', fileperms($fullPath . '/' . $dir['name'])), -4); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?path=<?php echo urlencode($dir['path']); ?>" class="text-indigo-600 hover:text-indigo-900">Open</a>
                                    <a href="?delete=<?php echo urlencode($dir['path']); ?>" onclick="return confirm('Are you sure you want to delete this folder and all its contents?');" class="text-red-600 hover:text-red-900 ml-4">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php foreach ($files as $file):
                            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $isViewable = in_array($fileExt, $viewableExtensions);
                            $filePath = ($currentPath ? $currentPath . '/' : '') . $file['name'];
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                        <?php if ($isViewable): ?>
                                            <a href="#" class="text-blue-600 hover:text-blue-900 font-medium view-file" data-path="<?php echo htmlspecialchars($filePath); ?>" data-ext="<?php echo $fileExt; ?>"><?php echo htmlspecialchars($file['name']); ?></a>
                                        <?php else: ?>
                                            <span class="text-gray-900"><?php echo htmlspecialchars($file['name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo round($file['size'] / 1024, 2); ?> KB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell font-mono"><?php echo substr(sprintf('%o', fileperms($fullPath . '/' . $file['name'])), -4); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?edit=<?php echo urlencode($file['path']); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <a href="?delete=<?php echo urlencode($file['path']); ?>" onclick="return confirm('Are you sure you want to delete this file?');" class="text-red-600 hover:text-red-900 ml-4">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <footer class="text-center text-sm text-gray-500 mt-4"><p>PHP File Explorer. Use with caution.</p></footer>
    </div>

    <div id="file-viewer-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-full max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 id="modal-title" class="text-lg font-bold text-gray-800">File Viewer</h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
            </div>
            <div id="modal-content" class="p-4 flex-grow overflow-auto flex items-center justify-center">
                </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('file-viewer-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const closeModal = document.getElementById('close-modal');

        document.querySelectorAll('.view-file').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const filePath = this.dataset.path;
                const fileExt = this.dataset.ext;
                const fileName = this.textContent;

                modalTitle.textContent = fileName;
                modalContent.innerHTML = ''; // Clear previous content

                if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
                    const img = document.createElement('img');
                    img.src = filePath;
                    img.className = 'max-w-full max-h-full rounded';
                    modalContent.appendChild(img);
                } else if (fileExt === 'pdf') {
                    const iframe = document.createElement('iframe');
                    iframe.src = filePath;
                    iframe.className = 'w-full h-full';
                    iframe.setAttribute('frameborder', '0');
                    modalContent.appendChild(iframe);
                }

                modal.classList.remove('hidden');
            });
        });

        function hideModal() {
            modal.classList.add('hidden');
            modalContent.innerHTML = ''; // Clean up content
        }

        closeModal.addEventListener('click', hideModal);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape" && !modal.classList.contains('hidden')) {
                hideModal();
            }
        });
    });
    </script>
</body>
</html>