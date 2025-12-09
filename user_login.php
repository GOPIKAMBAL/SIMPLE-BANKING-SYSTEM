<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: user_home.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            animation: containerFloat 6s ease-in-out infinite;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            perspective: 1000px;
            cursor: grab;
            user-select: none;
        }

        .login-container.rotate {
            animation: rotate3D 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes containerFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .bank-logo {
            text-align: center;
            margin-bottom: 1rem;
        }

        .bank-logo i {
            font-size: 3.5rem;
            color: #005792;
            animation: logoFloat 3s ease-in-out infinite;
            text-shadow: 
                0 0 10px rgba(0, 87, 146, 0.3),
                0 0 20px rgba(0, 87, 146, 0.2),
                0 0 30px rgba(0, 87, 146, 0.1);
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-10px) rotate(5deg);
            }
        }

        .bank-title {
            color: #001C44;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            text-align: center;
            animation: glow 2s ease-in-out infinite alternate;
            text-shadow: 
                0 0 5px rgba(0, 87, 146, 0.5),
                0 0 10px rgba(0, 87, 146, 0.3),
                0 0 15px rgba(0, 87, 146, 0.2),
                0 0 20px rgba(0, 87, 146, 0.1);
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

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating label {
            color: #666;
            transition: all 0.3s ease;
            animation: labelFloat 3s ease-in-out infinite;
        }

        .form-floating:hover label {
            color: #005792;
            transform: translateY(-2px);
        }

        @keyframes labelFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        .back-link {
            color: #005792;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 1rem;
            position: relative;
        }

        .back-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #005792;
            transition: width 0.3s ease;
        }

        .back-link:hover::after {
            width: 100%;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            .bank-title {
                font-size: 1.8rem;
            }
        }

        /* Add particle effects */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: var(--size, 4px);
            height: var(--size, 4px);
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite, particleRotate 10s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        @keyframes particleRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Add ripple effect to inputs */
        .form-control {
            position: relative;
            overflow: hidden;
        }

        .form-control::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(0, 87, 146, 0.2);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .form-control:focus::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        /* Add wave effect to background */
        .wave {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%2300B4D8" fill-opacity="0.2" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: 1440px 100px;
            animation: wave 10s linear infinite;
            z-index: 0;
        }

        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        /* Add floating icons animation */
        .form-floating i {
            animation: iconFloat 2s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(5deg); }
        }

        /* Add 3D effect to login container */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            animation: containerFloat 6s ease-in-out infinite, containerRotate 10s ease-in-out infinite;
        }

        @keyframes containerRotate {
            0%, 100% { transform: rotateX(0deg) rotateY(0deg); }
            25% { transform: rotateX(1deg) rotateY(1deg); }
            75% { transform: rotateX(-1deg) rotateY(-1deg); }
        }

        /* Add hover effect to back link */
        .back-link:hover {
            transform: translateX(-5px);
        }

        /* Add magnetic effect to login container */
        .login-container {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Add sparkle effect */
        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            pointer-events: none;
            animation: sparkleFloat 3s linear infinite;
        }

        @keyframes sparkleFloat {
            0% {
                transform: translateY(0) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }

        /* Add typing effect to login title */
        .login-title {
            overflow: hidden;
            border-right: 2px solid #005792;
            white-space: nowrap;
            animation: typing 3.5s steps(40, end), blink-caret 0.75s step-end infinite;
        }

        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: #005792 }
        }

        /* Add hover effect to form controls */
        .form-control:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 87, 146, 0.2);
        }

        /* Add ripple effect to button */
        .btn-login {
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn-login:hover::after {
            animation: ripple 1s ease-out;
        }

        /* Add floating effect to icons */
        .fas {
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(5deg); }
        }

        /* Add gradient animation to background */
        body {
            background: linear-gradient(-45deg, #001C44, #005792, #00B4D8, #001C44);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Add 3D tilt effect to login container */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .login-container:hover {
            transform: rotateX(5deg) rotateY(5deg);
        }

        /* Add floating effect to alert */
        .alert {
            animation: alertFloat 3s ease-in-out infinite;
        }

        @keyframes alertFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Add shine effect to form controls */
        .form-control {
            position: relative;
            overflow: hidden;
        }

        .form-control::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        /* Enhanced background effects */
        body {
            background: linear-gradient(-45deg, #001C44, #005792, #00B4D8, #001C44);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            position: relative;
            overflow: hidden;
        }

        /* Add floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.15;
            animation: orbFloat 20s infinite;
        }

        .orb:nth-child(1) {
            width: 300px;
            height: 300px;
            background: #00B4D8;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .orb:nth-child(2) {
            width: 400px;
            height: 400px;
            background: #005792;
            top: 60%;
            left: 70%;
            animation-delay: -5s;
        }

        .orb:nth-child(3) {
            width: 200px;
            height: 200px;
            background: #001C44;
            top: 30%;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes orbFloat {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(50px, 50px) scale(1.1);
            }
            50% {
                transform: translate(0, 100px) scale(0.9);
            }
            75% {
                transform: translate(-50px, 50px) scale(1.05);
            }
        }

        /* Add animated grid */
        .grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            opacity: 0.3;
            z-index: 0;
        }

        @keyframes gridMove {
            0% {
                transform: perspective(500px) rotateX(60deg) translateY(0);
            }
            100% {
                transform: perspective(500px) rotateX(60deg) translateY(50px);
            }
        }

        /* Add floating bubbles */
        .bubble {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: bubbleFloat 15s infinite;
            z-index: 0;
        }

        @keyframes bubbleFloat {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }

        /* Add light beams */
        .light-beam {
            position: fixed;
            width: 2px;
            height: 100vh;
            background: linear-gradient(to bottom, 
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0) 100%);
            animation: beamSweep 8s infinite;
            z-index: 0;
        }

        @keyframes beamSweep {
            0% {
                transform: translateX(-100vw) rotate(45deg);
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateX(100vw) rotate(45deg);
                opacity: 0;
            }
        }

        /* Add floating particles with trails */
        .particle-trail {
            position: fixed;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: trailFloat 10s linear infinite;
            z-index: 0;
        }

        .particle-trail::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: inherit;
            border-radius: inherit;
            animation: trail 1s linear infinite;
        }

        @keyframes trail {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }
            100% {
                transform: scale(10);
                opacity: 0;
            }
        }

        @keyframes trailFloat {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(100px, -100px);
            }
        }

        /* Add rotation effect to login container */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .login-container.rotate {
            animation: rotate3D 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes rotate3D {
            0% {
                transform: rotateX(0) rotateY(0) rotateZ(0);
            }
            25% {
                transform: rotateX(10deg) rotateY(10deg) rotateZ(5deg);
            }
            50% {
                transform: rotateX(-10deg) rotateY(-10deg) rotateZ(-5deg);
            }
            75% {
                transform: rotateX(5deg) rotateY(5deg) rotateZ(2deg);
            }
            100% {
                transform: rotateX(0) rotateY(0) rotateZ(0);
            }
        }

        /* Add glow effect during rotation */
        .login-container.rotating {
            box-shadow: 
                0 0 20px rgba(0, 87, 146, 0.3),
                0 0 40px rgba(0, 87, 146, 0.2),
                0 0 60px rgba(0, 87, 146, 0.1);
        }

        /* Add ripple effect on click */
        .login-container::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .login-container:active::after {
            animation: ripple 0.6s ease-out;
        }

        /* Enhanced rotation styles */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.1s ease-out;
            cursor: grab;
            user-select: none;
        }

        .login-container:active {
            cursor: grabbing;
        }

        /* Add rotation modes indicator */
        .rotation-modes {
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .login-container:hover .rotation-modes {
            opacity: 1;
        }

        .rotation-mode {
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 10px rgba(0, 87, 146, 0.5);
            backdrop-filter: blur(5px);
        }

        /* Enhanced glow effect during rotation */
        .login-container.rotating {
            box-shadow: 
                0 0 20px rgba(0, 87, 146, 0.3),
                0 0 40px rgba(0, 87, 146, 0.2),
                0 0 60px rgba(0, 87, 146, 0.1),
                0 0 80px rgba(0, 87, 146, 0.05);
        }

        /* Add rotation trail effect */
        .rotation-trail {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            background: rgba(0, 87, 146, 0.1);
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .login-container.rotating .rotation-trail {
            opacity: 1;
        }

        /* Enhanced cursor following effect */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.1s ease-out;
            cursor: grab;
            user-select: none;
            will-change: transform;
        }

        /* Add smooth movement effect */
        .login-container.moving {
            transition: transform 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        /* Add glow effect during movement */
        .login-container.moving {
            box-shadow: 
                0 0 20px rgba(0, 87, 146, 0.3),
                0 0 40px rgba(0, 87, 146, 0.2),
                0 0 60px rgba(0, 87, 146, 0.1),
                0 0 80px rgba(0, 87, 146, 0.05);
        }

        /* Enhanced secure movement styles */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: grab;
            user-select: none;
            will-change: transform;
            position: relative;
        }

        /* Add secure movement effect */
        .login-container.secure-move {
            animation: secureFloat 3s ease-in-out infinite;
        }

        @keyframes secureFloat {
            0%, 100% {
                transform: translateY(0) rotateX(0) rotateY(0);
            }
            25% {
                transform: translateY(-5px) rotateX(2deg) rotateY(-2deg);
            }
            75% {
                transform: translateY(5px) rotateX(-2deg) rotateY(2deg);
            }
        }

        /* Add secure glow effect */
        .login-container.secure-active {
            box-shadow: 
                0 0 20px rgba(0, 87, 146, 0.3),
                0 0 40px rgba(0, 87, 146, 0.2),
                0 0 60px rgba(0, 87, 146, 0.1),
                0 0 80px rgba(0, 87, 146, 0.05);
        }

        /* Add secure border effect */
        .login-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid transparent;
            border-radius: 22px;
            background: linear-gradient(45deg, #005792, #00B4D8) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-container.secure-active::before {
            opacity: 1;
        }

        /* Enhanced jumping movement styles */
        .login-container {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: grab;
            user-select: none;
            will-change: transform;
            position: relative;
            animation: jumpFloat 4s ease-in-out infinite;
        }

        @keyframes jumpFloat {
            0%, 100% {
                transform: translateY(0) rotateX(0) rotateY(0);
            }
            20% {
                transform: translateY(-15px) rotateX(5deg) rotateY(-5deg);
            }
            40% {
                transform: translateY(0) rotateX(-5deg) rotateY(5deg);
            }
            60% {
                transform: translateY(-10px) rotateX(3deg) rotateY(-3deg);
            }
            80% {
                transform: translateY(0) rotateX(-3deg) rotateY(3deg);
            }
        }

        /* Add bounce effect */
        .login-container.jumping {
            animation: bounceJump 0.8s cubic-bezier(0.36, 0, 0.66, -0.56) infinite;
        }

        @keyframes bounceJump {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        /* Add hop effect */
        .login-container.hopping {
            animation: hopJump 1.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes hopJump {
            0%, 100% {
                transform: translateY(0) rotate(0);
            }
            25% {
                transform: translateY(-25px) rotate(-5deg);
            }
            75% {
                transform: translateY(-15px) rotate(5deg);
            }
        }

        /* Add spring effect */
        .login-container.springing {
            animation: springJump 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        @keyframes springJump {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            30% {
                transform: translateY(-30px) scale(1.1);
            }
            60% {
                transform: translateY(-10px) scale(0.95);
            }
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

    <!-- Add wave effect -->
    <div class="wave"></div>

    <!-- Modify particles to have different sizes -->
    <div class="particles">
        <?php for($i = 0; $i < 20; $i++): ?>
            <div class="particle" style="
                left: <?php echo rand(0, 100); ?>vw;
                top: <?php echo rand(0, 100); ?>vh;
                --size: <?php echo rand(2, 6); ?>px;
                animation-delay: <?php echo rand(0, 15); ?>s;
                animation-duration: <?php echo rand(10, 20); ?>s;
            "></div>
        <?php endfor; ?>
    </div>

    <div class="login-container">
        <div class="bank-logo">
            <i class="fas fa-university"></i>
        </div>
        <h1 class="bank-title">ELITE BANK</h1>
        <h2 class="login-title">User Login</h2>
        
        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
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

    <!-- Add sparkles -->
    <div class="sparkles">
        <?php for($i = 0; $i < 10; $i++): ?>
            <div class="sparkle" style="
                left: <?php echo rand(0, 100); ?>vw;
                top: <?php echo rand(0, 100); ?>vh;
                animation-delay: <?php echo rand(0, 3); ?>s;
            "></div>
        <?php endfor; ?>
    </div>

    <!-- Add background elements -->
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="grid"></div>

    <!-- Add bubbles -->
    <?php for($i = 0; $i < 15; $i++): ?>
        <div class="bubble" style="
            left: <?php echo rand(0, 100); ?>vw;
            width: <?php echo rand(10, 30); ?>px;
            height: <?php echo rand(10, 30); ?>px;
            animation-delay: <?php echo rand(0, 15); ?>s;
            animation-duration: <?php echo rand(10, 20); ?>s;
        "></div>
    <?php endfor; ?>

    <!-- Add light beams -->
    <?php for($i = 0; $i < 3; $i++): ?>
        <div class="light-beam" style="
            left: <?php echo rand(0, 100); ?>vw;
            animation-delay: <?php echo rand(0, 8); ?>s;
        "></div>
    <?php endfor; ?>

    <!-- Add particle trails -->
    <?php for($i = 0; $i < 20; $i++): ?>
        <div class="particle-trail" style="
            left: <?php echo rand(0, 100); ?>vw;
            top: <?php echo rand(0, 100); ?>vh;
            animation-delay: <?php echo rand(0, 10); ?>s;
        "></div>
    <?php endfor; ?>

    <!-- Add rotation modes indicator -->
    <div class="rotation-modes">
        <div class="rotation-mode"><i class="fas fa-sync-alt me-1"></i>Drag to Rotate</div>
        <div class="rotation-mode"><i class="fas fa-magic me-1"></i>Auto Rotate</div>
        <div class="rotation-mode"><i class="fas fa-random me-1"></i>Random Spin</div>
    </div>

    <!-- Add rotation trail -->
    <div class="rotation-trail"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.login-container');
            const form = document.querySelector('form');
            let isDragging = false;
            let startX, startY;
            let currentRotationX = 0;
            let currentRotationY = 0;
            let currentRotationZ = 0;
            const maxRotation = 15;
            let autoRotate = false;
            let randomSpin = false;
            let spinInterval;
            let mouseX = 0;
            let mouseY = 0;
            let containerX = 0;
            let containerY = 0;
            let targetX = 0;
            let targetY = 0;
            let targetRotationX = 0;
            let targetRotationY = 0;
            let secureTimeout;
            let jumpTimeout;
            let currentJumpAnimation = 'jumpFloat';

            // Add secure movement class
            container.classList.add('secure-move');

            // Function to check if click is on form elements
            function isFormElement(target) {
                return target.closest('input') || target.closest('button') || target.closest('form');
            }

            // Function to cycle through jump animations
            function cycleJumpAnimation() {
                const animations = ['jumpFloat', 'bounceJump', 'hopJump', 'springJump'];
                const currentIndex = animations.indexOf(currentJumpAnimation);
                const nextIndex = (currentIndex + 1) % animations.length;
                currentJumpAnimation = animations[nextIndex];
                
                container.classList.remove('jumping', 'hopping', 'springing');
                container.style.animation = 'none';
                
                setTimeout(() => {
                    container.style.animation = '';
                    if (currentJumpAnimation !== 'jumpFloat') {
                        container.classList.add(currentJumpAnimation.toLowerCase());
                    }
                }, 50);
            }

            // Add click handler for jump animation
            container.addEventListener('click', function(e) {
                if (!isDragging && !isFormElement(e.target)) {
                    cycleJumpAnimation();
                }
            });

            // Mouse events for jumping movement
            container.addEventListener('mousedown', function(e) {
                if (!isFormElement(e.target)) {
                    isDragging = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    container.classList.remove('jumping', 'hopping', 'springing');
                    container.style.animation = 'none';
                    e.preventDefault();
                }
            });

            document.addEventListener('mousemove', function(e) {
                if (isDragging && !isFormElement(e.target)) {
                    const deltaX = e.clientX - startX;
                    const deltaY = e.clientY - startY;
                    
                    const jumpHeight = Math.min(Math.abs(deltaY) * 0.5, 50);
                    
                    currentRotationY = (deltaX / window.innerWidth) * 15;
                    currentRotationX = -(deltaY / window.innerHeight) * 15;
                    
                    container.style.transform = `
                        perspective(1000px)
                        translate3d(${deltaX * 0.3}px, ${-jumpHeight}px, 0)
                        rotateX(${currentRotationX}deg)
                        rotateY(${currentRotationY}deg)
                        scale3d(1.01, 1.01, 1.01)
                    `;
                }
            });

            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    
                    container.style.transition = 'transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                    container.style.transform = 'perspective(1000px) translate3d(0, 0, 0) rotateX(0) rotateY(0) rotateZ(0) scale3d(1, 1, 1)';
                    
                    setTimeout(() => {
                        container.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        if (currentJumpAnimation !== 'jumpFloat') {
                            container.classList.add(currentJumpAnimation.toLowerCase());
                        }
                    }, 500);
                }
            });

            // Touch events for jumping movement
            container.addEventListener('touchstart', function(e) {
                if (!isFormElement(e.target)) {
                    isDragging = true;
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                    container.classList.remove('jumping', 'hopping', 'springing');
                    container.style.animation = 'none';
                }
            });

            document.addEventListener('touchmove', function(e) {
                if (isDragging && !isFormElement(e.target)) {
                    const deltaX = e.touches[0].clientX - startX;
                    const deltaY = e.touches[0].clientY - startY;
                    
                    const jumpHeight = Math.min(Math.abs(deltaY) * 0.5, 50);
                    
                    currentRotationY = (deltaX / window.innerWidth) * 15;
                    currentRotationX = -(deltaY / window.innerHeight) * 15;
                    
                    container.style.transform = `
                        perspective(1000px)
                        translate3d(${deltaX * 0.3}px, ${-jumpHeight}px, 0)
                        rotateX(${currentRotationX}deg)
                        rotateY(${currentRotationY}deg)
                        scale3d(1.01, 1.01, 1.01)
                    `;
                }
            });

            document.addEventListener('touchend', function() {
                if (isDragging) {
                    isDragging = false;
                    
                    container.style.transition = 'transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                    container.style.transform = 'perspective(1000px) translate3d(0, 0, 0) rotateX(0) rotateY(0) rotateZ(0) scale3d(1, 1, 1)';
                    
                    setTimeout(() => {
                        container.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        if (currentJumpAnimation !== 'jumpFloat') {
                            container.classList.add(currentJumpAnimation.toLowerCase());
                        }
                    }, 500);
                }
            });

            // Add hover effect with jump
            container.addEventListener('mouseenter', function(e) {
                if (!isDragging && !isFormElement(e.target)) {
                    container.style.transform = 'translateY(-10px) scale(1.02)';
                }
            });

            container.addEventListener('mouseleave', function() {
                if (!isDragging) {
                    container.style.transform = 'translateY(0) scale(1)';
                }
            });

            // Cursor following effect
            document.addEventListener('mousemove', function(e) {
                if (!isDragging && !isFormElement(e.target)) {
                    mouseX = e.clientX;
                    mouseY = e.clientY;
                    
                    targetX = (mouseX - window.innerWidth / 2) * 0.02;
                    targetY = (mouseY - window.innerHeight / 2) * 0.02;
                    
                    targetRotationX = (mouseY - window.innerHeight / 2) * 0.02;
                    targetRotationY = (mouseX - window.innerWidth / 2) * 0.02;
                    
                    containerX += (targetX - containerX) * 0.1;
                    containerY += (targetY - containerY) * 0.1;
                    currentRotationX += (targetRotationX - currentRotationX) * 0.1;
                    currentRotationY += (targetRotationY - currentRotationY) * 0.1;
                    
                    container.style.transform = `
                        perspective(1000px)
                        translate3d(${containerX}px, ${containerY}px, 0)
                        rotateX(${currentRotationX}deg)
                        rotateY(${currentRotationY}deg)
                        scale3d(1.02, 1.02, 1.02)
                    `;
                }
            });

            // Double click for auto-rotate
            container.addEventListener('dblclick', function(e) {
                if (!isFormElement(e.target)) {
                    autoRotate = !autoRotate;
                    if (autoRotate) {
                        startAutoRotate();
                    } else {
                        stopAutoRotate();
                    }
                }
            });

            // Triple click for random spin
            container.addEventListener('click', function(e) {
                if (e.detail === 3 && !isFormElement(e.target)) {
                    randomSpin = !randomSpin;
                    if (randomSpin) {
                        startRandomSpin();
                    } else {
                        stopRandomSpin();
                    }
                }
            });

            function startAutoRotate() {
                let angle = 0;
                spinInterval = setInterval(() => {
                    angle += 2;
                    container.style.transform = `
                        perspective(1000px) 
                        rotateY(${angle}deg)
                        scale3d(1.02, 1.02, 1.02)
                    `;
                }, 30);
            }

            function stopAutoRotate() {
                clearInterval(spinInterval);
                container.style.transition = 'transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                container.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) rotateZ(0) scale3d(1, 1, 1)';
            }

            function startRandomSpin() {
                let spinCount = 0;
                const maxSpins = 3;
                
                function randomRotation() {
                    if (spinCount >= maxSpins) {
                        stopRandomSpin();
                        return;
                    }
                    
                    const randomX = Math.random() * 360;
                    const randomY = Math.random() * 360;
                    const randomZ = Math.random() * 360;
                    
                    container.style.transition = 'transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    container.style.transform = `
                        perspective(1000px) 
                        rotateX(${randomX}deg) 
                        rotateY(${randomY}deg) 
                        rotateZ(${randomZ}deg)
                        scale3d(1.1, 1.1, 1.1)
                    `;
                    
                    spinCount++;
                    setTimeout(randomRotation, 500);
                }
                
                randomRotation();
            }

            function stopRandomSpin() {
                container.style.transition = 'transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                container.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) rotateZ(0) scale3d(1, 1, 1)';
                randomSpin = false;
            }
        });
    </script>
</body>
</html>