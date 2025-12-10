<?php
session_start();

// --- CONFIGURATION ---
$host = 'db';
$db = getenv("POSTGRES_DB");
$user = getenv("POSTGRES_USER");
$pass = getenv("POSTGRES_PASSWORD");
$dsn  = "pgsql:host=$host;port=5432;dbname=$db";

// --- SECURITY CHECK ---
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// --- FORM HANDLING ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Inputs
    $name = trim($_POST['name']);
    $author = trim($_POST['author']);
    $desc = trim($_POST['description']);
    $category = $_POST['category'];
    $difficulty = $_POST['difficulty'];
    $language = trim($_POST['language']);
    
    // 2. Validate Text Fields
    if (empty($name) || empty($author) || empty($category) || empty($difficulty)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($name) > 100) {
        $error = "Challenge Name is too long (Max 100 chars).";
    } else {
        
        // 3. Handle File Upload
        if (isset($_FILES['challenge_file']) && $_FILES['challenge_file']['error'] == 0) {
            
            $file = $_FILES['challenge_file'];
            $fileName = $file['name'];
            $fileTmp  = $file['tmp_name'];
            $fileSize = $file['size'];
            
            // Extract extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validations
            $allowed = ['zip'];
            $maxSize = 100 * 1024 * 1024; // 100 MB in Bytes

            if (!in_array($fileExt, $allowed)) {
                $error = "Invalid file type. Only .ZIP files are allowed.";
            } elseif ($fileSize > $maxSize) {
                $error = "File is too large. Max limit is 100MB.";
            } else {
                // Generate Unique Name: "timestamp_filename.zip"
                $newFileName = time() . "_" . preg_replace('/[^a-zA-Z0-9\._-]/', '', $fileName);
                $uploadDir = 'challenges/';
                
                // Create directory if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destPath)) {
                    // 4. Insert into Database
                    try {
                        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                        
                        $sql = "INSERT INTO challenges (name, author, description, category, difficulty, language, file_path, created_at) 
                                VALUES (:name, :author, :desc, :cat, :diff, :lang, :path, NOW())";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            'name' => $name,
                            'author' => $author,
                            'desc' => $desc,
                            'cat' => $category,
                            'diff' => $difficulty,
                            'lang' => $language,
                            'path' => $destPath
                        ]);

                        $success = "Challenge uploaded successfully! Deploying to grid...";
                    } catch (PDOException $e) {
                        $error = "Database Error: " . $e->getMessage();
                        // Optional: Unlink (delete) file if DB insert fails
                        unlink($destPath);
                    }
                } else {
                    $error = "Failed to move uploaded file. Check folder permissions.";
                }
            }
        } else {
            $error = "Please upload a ZIP file containing the Docker/Source.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Challenge | DDsec </title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/static/styles.css">
</head>
<body>

    <header id="navbar">
        <a href="index.php" class="logo">DD<span>sec</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="challenges.php">Challenges</a></li>
            <li><a href="upload_challenge.php" style="color:var(--primary-accent);">Upload Challenge</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php" class="btn-login" style="border-color: #ff4d4d; color: #ff4d4d !important;">Logout</a></li>
        </ul>
    </header>

    <div class="upload-container">
        <div class="upload-card">
            <h2 style="font-family: 'Orbitron'; margin-bottom: 20px; color: #fff;">
                <i class="fas fa-upload"></i> Deploy New Challenge
            </h2>

            <?php if($error): ?>
                <div class="error-msg"><i class="fas fa-bug"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="error-msg" style="background: rgba(0, 255, 127, 0.2); color: #00ff7f; border-color: #00ff7f;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="upload_challenge.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label>Challenge Name</label>
                        <input type="text" name="name" placeholder="e.g. Operation Firewall" required>
                    </div>

                    <div class="input-group">
                        <label>Author</label>
                        <input type="text" name="author" value="<?php echo $_SESSION['username'] ?? ''; ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Web Exploitation">Web Exploitation</option>
                            <option value="Cryptography">Cryptography</option>
                            <option value="Reverse Engineering">Reverse Engineering</option>
                            <option value="Forensics">Forensics</option>
                            <option value="Pwn">Pwn / Binary Exploitation</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Difficulty</label>
                        <select name="difficulty" required>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                            <option value="Insane">Insane</option>
                        </select>
                    </div>

                    <div class="input-group full-width">
                        <label>Tech Stack / Source Code Language</label>
                        <input type="text" name="language" placeholder="e.g. Python, PHP, C++, Solidity">
                    </div>

                    <div class="input-group full-width">
                        <label>Description / Hint</label>
                        <textarea name="description" class="input-field" placeholder="Brief the hackers about the target..." style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); color:#fff; border-radius:5px;"></textarea>
                    </div>

                    <div class="input-group full-width">
                        <label>Challenge Artifacts (ZIP only, Max 100MB)</label>
                        <div class="file-upload-wrapper">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p id="file-text">Drag & Drop or Click to Upload ZIP</p>
                            <input type="file" name="challenge_file" accept=".zip" required onchange="updateFileName(this)">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-main" style="width: 100%; margin-top: 30px;">
                    <i class="fas fa-rocket"></i> Upload Challenge
                </button>

            </form>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            var fileName = input.files[0].name;
            document.getElementById('file-text').innerText = "Selected: " + fileName;
            document.getElementById('file-text').style.color = "var(--primary-accent)";
        }

        // Sticky Header
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        })
    </script>
</body>
</html>