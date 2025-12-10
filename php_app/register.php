<?php
session_start();

// --- CONFIGURATION ---
$host = 'db';
$db = getenv("POSTGRES_DB");
$user = getenv("POSTGRES_USER");
$pass = getenv("POSTGRES_PASSWORD");
$dsn  = "pgsql:host=$host;port=5432;dbname=$db";

$error = "";
$success = "";

// --- REGISTRATION LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or Email already taken.";
            } else {
                // Hash Password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert New User (Default role: player)
                $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, 'player', NOW())";
                $insertStmt = $pdo->prepare($sql);
                
                if ($insertStmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password])) {
                    $success = "Registration successful! Redirecting to login...";
                    header("refresh:2;url=login.php"); // Auto-redirect after 2 seconds
                } else {
                    $error = "Registration failed. Try again.";
                }
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
    <title>Register | DDsec</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/static/styles.css">
</head>
<body>

    <header id="navbar">
        <a href="index.php" class="logo">DD<span>sec</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php" class="btn-login">Login</a></li>
        </ul>
    </header>

    <div class="login-container">
        <div class="login-card">
            <h2><i class="fas fa-user-plus"></i> Join the Ranks</h2>
            
            <?php if($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="error-msg" style="background: rgba(0, 255, 100, 0.2); color: #00ffaa; border-color: #00ffaa;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="input-group">
                    <label for="username">Username / Handle</label>
                    <input type="text" name="username" id="username" placeholder="Choose your alias" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="secure@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Create a strong password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat password" required>
                </div>

                <button type="submit" class="btn-main" style="width: 100%; margin-top: 10px;">Register Account</button>
            </form>

            <div class="register-link">
                Already an agent? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 DDsec Wargames Private Limited. Recruitment Division.</p>
    </footer>

    <script>
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        })
    </script>

</body>
</html>