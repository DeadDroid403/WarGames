<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction | DDsec</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/static/styles.css">

    <style>
        /* Local overrides for this specific page */
        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .construction-container {
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 40px;
            background: rgba(5, 5, 16, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
            border-top: 2px solid var(--primary-accent);
            border-bottom: 2px solid var(--secondary-accent);
        }

        .icon-box {
            font-size: 5rem;
            margin-bottom: 20px;
            color: var(--primary-accent);
            animation: pulse 2s infinite;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        p {
            font-size: 1.2rem;
            color: #aaa;
            max-width: 500px;
            margin: 0 auto 30px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #222;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        .progress-fill {
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-accent), var(--primary-accent));
            box-shadow: 0 0 10px var(--primary-accent);
            animation: load 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { text-shadow: 0 0 10px var(--primary-accent); opacity: 1; }
            50% { text-shadow: 0 0 30px var(--primary-accent); opacity: 0.7; }
            100% { text-shadow: 0 0 10px var(--primary-accent); opacity: 1; }
        }

        @keyframes load {
            0% { width: 10%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 30px;
            border: 1px solid var(--glass-border);
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-btn:hover {
            border-color: var(--primary-accent);
            color: var(--primary-accent);
        }
    </style>
</head>
<body>

    <div style="position: absolute; top: 10%; left: 10%; width: 300px; height: 300px; background: var(--secondary-accent); filter: blur(150px); opacity: 0.2; border-radius: 50%;"></div>
    <div style="position: absolute; bottom: 10%; right: 10%; width: 300px; height: 300px; background: var(--primary-accent); filter: blur(150px); opacity: 0.2; border-radius: 50%;"></div>

    <div class="construction-container">
        <div class="icon-box">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1>Under Construction</h1>
        <p>Our engineers are fortifying the mainframe. <br>This section is currently under strict maintenance protocol.</p>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <div style="font-family: 'Orbitron'; color: var(--primary-accent); font-size: 0.9rem;">
            ESTIMATED RESTORATION: Soon 
        </div>

        <br>
        <a href="index.php" class="back-btn">Return to Base</a>
    </div>

</body>
</html>