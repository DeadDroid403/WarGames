<?php
session_start();

// --- CONFIGURATION ---
$host = 'db';
$db = getenv("POSTGRES_DB");
$user = getenv("POSTGRES_USER");
$pass = getenv("POSTGRES_PASSWORD");
$dsn  = "pgsql:host=$host;port=5432;dbname=$db";

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$msg = "";
$msgType = "";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $userId = $_SESSION['user_id'];

    // --- HANDLE FORM SUBMISSIONS ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. Update Username
        if (isset($_POST['update_profile'])) {
            $newUsername = trim($_POST['username']);
            
            // Check uniqueness
            $check = $pdo->prepare("SELECT id FROM users WHERE username = :u AND id != :id");
            $check->execute(['u' => $newUsername, 'id' => $userId]);
            
            if ($check->rowCount() > 0) {
                $msg = "Username already taken.";
                $msgType = "error";
            } else {
                // Update User Table
                $stmt = $pdo->prepare("UPDATE users SET username = :u WHERE id = :id");
                $stmt->execute(['u' => $newUsername, 'id' => $userId]);
                
                // Update Challenges Table (Maintain consistency)
                $oldUsername = $_SESSION['username'];
                $updateChall = $pdo->prepare("UPDATE challenges SET author = :new WHERE author = :old");
                $updateChall->execute(['new' => $newUsername, 'old' => $oldUsername]);

                // Update Session
                $_SESSION['username'] = $newUsername;
                $msg = "Profile updated successfully.";
                $msgType = "success";
            }
        }

        // 2. Change Password
        if (isset($_POST['change_password'])) {
            $currentPwd = $_POST['current_password'];
            $newPwd     = $_POST['new_password'];
            $confirmPwd = $_POST['confirm_password'];

            // Get real current password hash
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPwd, $userRow['password'])) {
                $msg = "Incorrect current password.";
                $msgType = "error";
            } elseif ($newPwd !== $confirmPwd) {
                $msg = "New passwords do not match.";
                $msgType = "error";
            } elseif (strlen($newPwd) < 6) {
                $msg = "Password must be at least 6 characters.";
                $msgType = "error";
            } else {
                $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                $upd->execute(['p' => $newHash, 'id' => $userId]);
                
                $msg = "Password changed successfully.";
                $msgType = "success";
            }
        }
    }

    // --- FETCH USER DATA (Refresh after updates) ---
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Count Uploaded Challenges (Based on username)
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM challenges WHERE author = :author");
    $countStmt->execute(['author' => $user['username']]);
    $uploadCount = $countStmt->fetchColumn();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | DDsec </title>
    
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
            <li><a href="upload_challenge.php">Upload Challenge</a></li>
            <li><a href="profile.php" style="color:var(--primary-accent);">Profile</a></li>
            <li><a href="logout.php" class="btn-login" style="border-color: #ff4d4d; color: #ff4d4d !important;">Logout</a></li>
        </ul>
    </header>

    <div class="profile-container">
        
        <?php if($msg): ?>
            <div class="error-msg" style="
                background: <?php echo $msgType == 'success' ? 'rgba(0, 255, 127, 0.2)' : 'rgba(255, 0, 50, 0.2)'; ?>; 
                color: <?php echo $msgType == 'success' ? '#00ff7f' : '#ff4d4d'; ?>; 
                border-color: <?php echo $msgType == 'success' ? '#00ff7f' : '#ff4d4d'; ?>;">
                <i class="fas <?php echo $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> 
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            
            <div class="profile-card">
                <div class="avatar-circle">
                    <i class="fas fa-user-secret"></i>
                </div>
                
                <h2 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h2>
                <div class="profile-role"><?php echo htmlspecialchars($user['role']); ?> operative</div>
                
                <div style="margin: 20px 0; color: #aaa; font-size: 0.9rem;">
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p style="margin-top:5px;"><i class="fas fa-calendar-alt"></i> Joined: <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                </div>

                <div class="profile-stats">
                    <div class="stat-box">
                        <h4><?php echo $uploadCount; ?></h4>
                        <p>Uploaded</p>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3 class="section-title"><i class="fas fa-id-card"></i> Edit Profile</h3>
                
                <form action="profile.php" method="POST" style="margin-bottom: 40px;">
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn-main" style="padding: 10px 20px; font-size: 1rem;">Update Identity</button>
                </form>

                <h3 class="section-title"><i class="fas fa-lock"></i> Security Clearance</h3>
                
                <form action="profile.php" method="POST">
                    <div class="input-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div style="display: flex; gap: 20px;">
                        <div class="input-group" style="flex:1;">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="input-group" style="flex:1;">
                            <label>Confirm New</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn-secondary" style="width: 100%;">Reset Credentials</button>
                </form>
            </div>

        </div>
    </div>

    <footer>
        <p>&copy; 2025 DDsec Wargames. Classified Information.</p>
    </footer>

    <script>
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        })
    </script>
</body>
</html>