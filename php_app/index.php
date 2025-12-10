<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDsec Wargames | Hack The Future</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            <li><a href="leaderboard.php">Leaderboard</a></li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="upload.php">Upload Challenge</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="btn-login" style="border-color: #ff4d4d; color: #ff4d4d !important;">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login">Login</a></li>
            <?php endif; ?>
        </ul>
        
        <i class="fas fa-bars" style="color:white; font-size: 1.5rem; display: none;"></i>
    </header>

    <section class="hero">
        <div style="z-index: 2;">
            <h1>CAPTURE THE FLAG</h1>
            <p>Enter the cyber arena. Break the code. Pwn the system.<br>Join the elite league of cybersecurity experts.</p>
            
            <div class="cta-buttons">
                <button class="btn-main" onclick="window.location.href='challenges.php'">Start Hacking</button>
                <button class="btn-secondary" onclick="window.location.href='leaderboard.php'">View Top Hackers</button>
            </div>
        </div>

        <div style="position: absolute; bottom: 10%; left: 10%; width: 10px; height: 10px; background: var(--primary-accent); box-shadow: 0 0 20px var(--primary-accent); border-radius: 50%; opacity: 0.6; animation: float 6s infinite;"></div>
        <div style="position: absolute; top: 20%; right: 15%; width: 8px; height: 8px; background: var(--secondary-accent); box-shadow: 0 0 20px var(--secondary-accent); border-radius: 50%; opacity: 0.6; animation: float 8s infinite reverse;"></div>
    </section>

    <section class="stats-container">
        <div class="stat-card">
            <h3>50+</h3>
            <p>Active Challenges</p>
        </div>
        <div class="stat-card">
            <h3>1.2k</h3>
            <p>Registered Hackers</p>
        </div>
        <div class="stat-card">
            <h3>$0</h3>
            <p>Free to Play</p>
        </div>
    </section>

    <footer>
        <div class="logo" style="font-size: 1.2rem; margin-bottom: 10px;">DDsec <span>Wargames</span></div>
        <div class="socials">
            <a href="#"><i class="fab fa-discord"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-github"></i></a>
        </div>
        <br>
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