<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- CONFIGURATION ---
$host = 'db';
$db = getenv("POSTGRES_DB");
$user = getenv("POSTGRES_USER");
$pass = getenv("POSTGRES_PASSWORD");
$dsn  = "pgsql:host=$host;port=5432;dbname=$db";

$challenges = [];
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Build Query with Search Filter
    $sql = "SELECT * FROM challenges WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (name ILIKE :search)";
        $params['search'] = "%$search%";
    }
    
    // Order by newest first
    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Helper: Get badge class based on difficulty text
function getDifficultyClass($diff) {
    $diff = strtolower($diff);
    if ($diff == 'easy') return 'easy';
    if ($diff == 'medium') return 'medium';
    return 'hard';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenges | DDsec</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/static/styles.css">
</head>
<body>

    <header id="navbar">
        <a href="index.php" class="logo">DD<span>sec</span></a>
        
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="challenges.php" class="active" style="color: var(--primary-accent);">Challenges</a></li>
        
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="upload_challenge.php">Upload Challenge</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="btn-login" style="border-color: #ff4d4d; color: #ff4d4d !important;">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login">Login</a></li>
            <?php endif; ?>
        </ul>
        
        <i class="fas fa-bars" style="color:white; font-size: 1.5rem; display: none;"></i>
    </header>

    <div class="search-container">
        <form action="challenges.php" method="GET" class="search-box">
            <input type="text" name="q" placeholder="Search challenges by name or category..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
            <i class="fas fa-search"></i>
        </form>
    </div>

    <div class="challenges-grid">
        
        <?php if (count($challenges) > 0): ?>
            <?php foreach ($challenges as $chal): ?>
                
                <div class="challenge-card">
                    <span class="badge <?php echo getDifficultyClass($chal['difficulty']); ?>">
                        <?php echo htmlspecialchars($chal['difficulty']); ?>
                    </span>

                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($chal['name']); ?></h3>
                    </div>

                    <div class="card-meta">
                        <span><i class="fas fa-user-astronaut"></i> <?php echo htmlspecialchars($chal['author']); ?></span>
                        <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($chal['category']); ?></span>
                        <span><i class="fas fa-code"></i> <?php echo htmlspecialchars($chal['language']); ?></span>
                    </div>

                    <div class="card-meta" style="font-size: 0.8rem; color: #666;">
                        Uploaded: <?php echo date('M d, Y', strtotime($chal['created_at'])); ?>
                    </div>

                    <?php if (!empty($chal['file_path'])): ?>
                        <a href="<?php echo htmlspecialchars($chal['file_path']); ?>" class="btn-download" download>
                            <i class="fas fa-download"></i> Download Docker
                        </a>
                    <?php else: ?>
                        <button class="btn-download" style="opacity: 0.5; cursor: not-allowed;">No Files</button>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; color: #888; margin-top: 50px;">
                <h2><i class="fas fa-ghost"></i> No Challenges Found</h2>
                <p>Try searching for something else.</p>
            </div>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; 2025 DDsec Wargames Private Limited. All Rights Reserved.</p>
    </footer>

    <script>
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        })
    </script>
</body>
</html>