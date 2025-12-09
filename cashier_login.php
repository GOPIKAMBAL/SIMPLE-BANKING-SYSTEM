<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM staff WHERE username = ? AND role = 'cashier' LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $staff = mysqli_fetch_assoc($result);
        if ($password === $staff['password']) {
            $_SESSION['cashier_id'] = $staff['id'];
            $_SESSION['role'] = $staff['role'];
            header("Location: cashier_home.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid username or role!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Cashier Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(-45deg, #001C44, #005792, #00B4D8, #001C44);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Animated background shapes */
        .shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.18;
            animation: floatShape 14s linear infinite, rotate 20s linear infinite;
        }

        .shape.square {
            border-radius: 18% 82% 70% 30% / 30% 40% 60% 70%;
        }

        @keyframes floatShape {
            0% { transform: translateY(0) scale(1) rotate(0deg); opacity: 0.18; }
            50% { opacity: 0.32; }
            100% { transform: translateY(-100vh) scale(1.15) rotate(30deg); opacity: 0.08; }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2.5rem;
            max-width: 400px;
            width: 100%;
            position: relative;
            z-index: 1;
            transform-style: preserve-3d;
            perspective: 1000px;
            cursor: pointer;
            user-select: none;
            will-change: transform;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes rotate360 {
            0% {
                transform: perspective(1000px) rotateY(0deg);
            }
            100% {
                transform: perspective(1000px) rotateY(360deg);
            }
        }

        @keyframes containerFloat {
            0%, 100% {
                transform: translateY(0) rotateX(0) rotateY(0);
            }
            50% {
                transform: translateY(-10px) rotateX(2deg) rotateY(2deg);
            }
        }

        .bank-title {
            color: #001C44;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            text-align: center;
            animation: glow 2s ease-in-out infinite alternate, titleFloat 4s ease-in-out infinite;
            text-shadow: 
                0 0 5px rgba(0, 87, 146, 0.5),
                0 0 10px rgba(0, 87, 146, 0.3),
                0 0 15px rgba(0, 87, 146, 0.2),
                0 0 20px rgba(0, 87, 146, 0.1);
            animation: neonPulse 2s ease-in-out infinite;
        }

        .bank-title i {
            animation: float 3s ease-in-out infinite;
            color: #005792;
            margin-right: 10px;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }

        @keyframes titleFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-5px) rotate(1deg); }
            75% { transform: translateY(5px) rotate(-1deg); }
        }

        .login-title {
            color: #005792;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 0.5px;
            overflow: hidden;
            border-right: 2px solid #005792;
            white-space: nowrap;
            animation: typing 3.5s steps(40, end), blink-caret 0.75s step-end infinite;
        }

        .form-label {
            color: #001C44;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            border-color: #005792;
            box-shadow: 0 8px 20px rgba(0, 87, 146, 0.2);
            transform: translateY(-3px) scale(1.02) rotate(0.5deg);
        }

        .btn-login {
            background: linear-gradient(135deg, #005792 0%, #00B4D8 100%);
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.8rem;
            margin-top: 1.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: buttonPulse 2s infinite;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease-out, height 0.6s ease-out;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 87, 146, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #00B4D8 0%, #005792 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-login:hover::after {
            opacity: 1;
        }

        .alert {
            border-radius: 10px;
            animation: slideIn 0.5s ease-out, shake 0.5s ease-in-out, alertPulse 2s infinite;
        }

        @keyframes slideIn {
            0% {
                transform: translateY(-20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes alertPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        @keyframes neonPulse {
            0% { text-shadow: 0 0 5px rgba(0, 87, 146, 0.5), 0 0 10px rgba(0, 87, 146, 0.3); }
            50% { text-shadow: 0 0 10px rgba(0, 87, 146, 0.6), 0 0 20px rgba(0, 87, 146, 0.4); }
            100% { text-shadow: 0 0 5px rgba(0, 87, 146, 0.5), 0 0 10px rgba(0, 87, 146, 0.3); }
        }

        @keyframes buttonPulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 87, 146, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 87, 146, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 87, 146, 0); }
        }

        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: #005792 }
        }

        .back-link {
            color: #005792;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 1rem;
        }

        .back-link:hover {
            color: #00B4D8;
            transform: translateX(-5px);
        }

        .back-link i {
            margin-right: 5px;
            transition: transform 0.3s ease;
        }

        .back-link:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <!-- Animated background shapes -->
    <div class="shapes">
        <div class="shape" style="width: 90px; height: 90px; left: 12vw; bottom: -100px; background: #00B4D8; animation-delay: 0s;"></div>
        <div class="shape square" style="width: 60px; height: 60px; left: 28vw; bottom: -120px; background: #005792; animation-delay: 1.2s;"></div>
        <div class="shape" style="width: 110px; height: 110px; left: 65vw; bottom: -150px; background: #001C44; animation-delay: 0.7s;"></div>
        <div class="shape square" style="width: 70px; height: 70px; left: 80vw; bottom: -90px; background: #00B4D8; animation-delay: 2.1s;"></div>
        <div class="shape" style="width: 45px; height: 45px; left: 52vw; bottom: -60px; background: #005792; animation-delay: 1.7s;"></div>
        <div class="shape square" style="width: 80px; height: 80px; left: 18vw; bottom: -110px; background: #001C44; animation-delay: 2.7s;"></div>
    </div>

    <div class="login-container">
        <h1 class="bank-title"><i class="fas fa-university"></i>ELITE BANK</h1>
        <h2 class="login-title">Cashier Login</h2>
        
        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-login">Login</button>
            </div>
        </form>
        <div class="text-center">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const container = document.querySelector('.login-container');
        let isRotating = false;

        // Simple click rotation function
        function rotateOnClick() {
            if (isRotating) return;
            
            isRotating = true;
            container.style.animation = 'rotate360 1s cubic-bezier(0.4, 0, 0.2, 1) forwards';
            
            // Reset animation after completion
            setTimeout(() => {
                container.style.animation = '';
                isRotating = false;
            }, 1000);
        }

        // Add click event listener
        container.addEventListener('click', (e) => {
            rotateOnClick();
        });

        // Add initial animation
        window.addEventListener('load', () => {
            container.style.animation = 'containerFloat 6s ease-in-out infinite';
        });
    </script>
</body>
</html> 