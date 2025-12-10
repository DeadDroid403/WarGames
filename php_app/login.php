<?php
session_start();

// --- CONFIGURATION ---
$host = 'db';
$db = getenv("POSTGRES_DB");
$user = getenv("POSTGRES_USER");
$pass = getenv("POSTGRES_PASSWORD");
$dsn  = "pgsql:host=$host;port=5432;dbname=$db";

$error = "";

// --- LOGIN LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Prepare Query (Prevent SQL Injection)
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userRow && password_verify($password, $userRow['password'])) {
                // Success: Set Session
                $_SESSION['user_id'] = $userRow['id'];
                $_SESSION['username'] = $userRow['username'];
                $_SESSION['role'] = $userRow['role']; // e.g., 'admin' or 'player'
                
                header("Location: index.php"); // Redirect to dashboard
                exit;
            } else {
                $error = "Invalid credentials. Hack harder.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DDsec Wargames</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/static/styles.css">
</head>
<body>

    <header id="navbar">
        <a href="index.php" class="logo">DDsec <span>Wargames</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="challenges.php">Challenges</a></li>
            <li><a href="register.php" class="btn-login" style="border-color: var(--secondary-accent); color: var(--secondary-accent) !important;">Register</a></li>
        </ul>
    </header>

    <div class="login-container">
        <div class="login-card">
            <h2><i class="fas fa-user-secret"></i> System Access</h2>
            
            <?php if($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="username">Codename / Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your handle" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your secret" required>
                </div>

                <button type="submit" class="btn-main" style="width: 100%; margin-top: 10px;"> Authenticate</button>
            </form>

            <div class="register-link">
                New recruit? <a href="register.php">Join the Wargames</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 DDsec Wargames Private Limited. Secure Access Only.</p>
    </footer>

    <script>
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        })
    </script>
</body>
</html>