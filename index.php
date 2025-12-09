<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #0a1023;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Video background styling */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .video-background video {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            top: 0;
            left: 0;
        }
        
        .video-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(10, 16, 35, 0.7), rgba(10, 16, 35, 0.5));
        }
        
        .main-content {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 10vh;
            position: relative;
            z-index: 1;
        }
        
        .bank-title {
            color: #fff;
            font-size: 5.5rem;
            font-weight: 900;
            letter-spacing: 3px;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.7), 0 0 30px rgba(137, 82, 255, 0.6);
            margin-bottom: 0.5rem;
            text-align: center;
            text-transform: uppercase;
            -webkit-text-stroke: 1px rgba(255,255,255,0.3);
            animation: titleFloat 6s ease-in-out infinite, glow 2s ease-in-out infinite alternate;
            position: relative;
        }
        
        @keyframes titleFloat {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0);
            }
        }
        
        @keyframes glow {
            from {
                text-shadow: 2px 2px 15px rgba(0,0,0,0.7), 0 0 20px rgba(137, 82, 255, 0.6);
            }
            to {
                text-shadow: 2px 2px 15px rgba(0,0,0,0.7), 0 0 30px rgba(137, 82, 255, 0.9), 0 0 40px rgba(175, 91, 250, 0.6);
            }
        }
        
        .welcome-text {
            font-size: 1.8rem;
            color: #fff;
            text-align: center;
            font-weight: 400;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        
        .login-container {
            position: fixed;
            top: 50%;
            right: 2rem;
            transform: translateY(-50%);
            background: rgba(23, 32, 63, 0.65);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.5), inset 0 0 20px rgba(137, 82, 255, 0.2);
            padding: 2rem 1.8rem;
            max-width: 320px;
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(137, 82, 255, 0.3);
            z-index: 10;
            animation: float 6s ease-in-out infinite, shake 15s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(-50%) translateX(0);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.5), inset 0 0 20px rgba(137, 82, 255, 0.2);
            }
            25% {
                transform: translateY(-55%) translateX(-10px);
                box-shadow: 0 15px 40px 0 rgba(31, 38, 135, 0.6), inset 0 0 25px rgba(137, 82, 255, 0.3);
            }
            50% {
                transform: translateY(-52%) translateX(5px);
                box-shadow: 0 12px 36px 0 rgba(31, 38, 135, 0.55), inset 0 0 22px rgba(137, 82, 255, 0.25);
            }
            75% {
                transform: translateY(-48%) translateX(-5px);
                box-shadow: 0 10px 34px 0 rgba(31, 38, 135, 0.5), inset 0 0 20px rgba(137, 82, 255, 0.2);
            }
            100% {
                transform: translateY(-50%) translateX(0);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.5), inset 0 0 20px rgba(137, 82, 255, 0.2);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateY(-50%) rotate(0deg);
            }
            10% {
                transform: translateY(-50%) rotate(1deg);
            }
            20% {
                transform: translateY(-51%) rotate(-1deg);
            }
            30% {
                transform: translateY(-49%) rotate(0.5deg);
            }
            40% {
                transform: translateY(-50%) rotate(-0.5deg);
            }
            50% {
                transform: translateY(-49%) rotate(0deg);
            }
            60% {
                transform: translateY(-51%) rotate(0.5deg);
            }
            70% {
                transform: translateY(-50%) rotate(-1deg);
            }
            80% {
                transform: translateY(-49%) rotate(1deg);
            }
            90% {
                transform: translateY(-50%) rotate(0deg);
            }
        }
        
        .login-title {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.4), 0 0 15px rgba(137, 82, 255, 0.5);
            animation: pulse 2s infinite, titleSway 5s ease-in-out infinite;
            position: relative;
        }
        
        @keyframes titleSway {
            0%, 100% {
                transform: rotate(-2deg);
            }
            50% {
                transform: rotate(2deg);
            }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .btn-login {
            background: linear-gradient(90deg, #8952ff 0%, #af5bfa 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 50px;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4), 0 0 15px rgba(139, 92, 246, 0.2);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 800px;
        }
        
        .btn-login:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: all 0.6s;
        }
        
        .btn-login:hover:before {
            left: 100%;
        }
        
        .btn-login:hover {
            background: linear-gradient(90deg, #af5bfa 0%, #8952ff 100%);
            transform: translateY(-8px) scale(1.05) rotateX(5deg);
            box-shadow: 0 15px 25px rgba(139, 92, 246, 0.6), 0 0 20px rgba(139, 92, 246, 0.4);
            animation: buttonWobble 1s infinite alternate;
        }
        
        @keyframes buttonWobble {
            0% {
                transform: translateY(-8px) scale(1.05) rotateX(5deg) rotateY(0deg);
            }
            100% {
                transform: translateY(-8px) scale(1.05) rotateX(5deg) rotateY(2deg);
            }
        }
        
        .btn-login:active {
            transform: translateY(2px);
            box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3);
        }
        
        .login-icon {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            margin-right: 0.8rem;
            transition: transform 0.3s ease;
        }
        
        .btn-login:hover .login-icon {
            transform: rotate(10deg) scale(1.2);
        }
        
        .tagline-container {
            margin-top: 1.5rem;
        }
        
        .tagline {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 8px rgba(0,0,0,0.5);
            letter-spacing: 2px;
            position: relative;
            animation: taglineFloat 7s ease-in-out infinite 0.5s;
        }
        
        .tagline span {
            display: inline-block;
            margin: 0 8px;
            position: relative;
        }
        
        .tagline span:nth-child(1) {
            animation: wordPulse 2s infinite 0.1s;
            color: #c2f0ff;
        }
        
        .tagline span:nth-child(2) {
            animation: wordPulse 2s infinite 0.7s;
            color: #ffd8fa;
        }
        
        .tagline span:nth-child(3) {
            animation: wordPulse 2s infinite 1.3s;
            color: #d8ffea;
        }
        
        @keyframes wordPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.9;
            }
        }
        
        @keyframes taglineFloat {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
            100% {
                transform: translateY(0);
            }
        }
        
        /* Button animation classes */
        .btn-1 {
            animation: fadeInUp 0.7s both;
        }
        
        .btn-2 {
            animation: fadeInUp 0.7s 0.2s both;
        }
        
        .btn-3 {
            animation: fadeInUp 0.7s 0.4s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 40px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        /* Login panel glow effect */
        .login-container::after {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            top: -10%;
            left: -10%;
            background: linear-gradient(45deg, rgba(137, 82, 255, 0.1), rgba(255, 255, 255, 0), rgba(175, 91, 250, 0.1));
            z-index: -1;
            animation: rotateGlow 10s linear infinite;
            border-radius: 2rem;
        }
        
        @keyframes rotateGlow {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        @media (max-width: 992px) {
            .bank-title {
                font-size: 3.5rem;
            }
            .tagline {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                position: relative;
                right: auto;
                top: auto;
                transform: none;
                margin: 2rem auto;
                animation: none;
            }
            .login-container::after {
                animation: none;
            }
            .bank-title {
                font-size: 3rem;
                margin-top: 2rem;
            }
            .main-content {
                height: auto;
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop playsinline preload="auto" disablePictureInPicture>
            <source src="videos/banking_background.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="bank-title animate__animated animate__fadeInDown">ELITE BANK</h1>
                <div class="tagline-container mt-2 animate__animated animate__fadeIn animate__delay-1s">
                    <p class="tagline"><span>Secure</span> • <span>Innovative</span> • <span>Trustworthy</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Panel -->
    <div class="login-container">
        <h3 class="login-title">Choose your login type</h3>
        <div class="d-grid gap-3">
            <a href="user_login.php" class="btn btn-login btn-1" id="userBtn">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                    </svg>
                </div>
                <span>User Login</span>
            </a>
            <a href="manager_login.php" class="btn btn-login btn-2" id="managerBtn">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                    </svg>
                </div>
                <span>Manager Login</span>
            </a>
            <a href="cashier_login.php" class="btn btn-login btn-3" id="cashierBtn">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                        <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                    </svg>
                </div>
                <span>Cashier Login</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add interactive hover effects
        const buttons = document.querySelectorAll('.btn-login');
        
        buttons.forEach(button => {
            button.addEventListener('mouseover', function() {
                // Add a jump and shake effect to other buttons
                buttons.forEach(otherBtn => {
                    if (otherBtn !== button) {
                        otherBtn.style.transform = 'translateY(-10px) rotate(-2deg)';
                        setTimeout(() => {
                            otherBtn.style.transform = 'translateY(-5px) rotate(2deg)';
                            setTimeout(() => {
                                otherBtn.style.transform = 'translateY(0) rotate(0deg)';
                            }, 200);
                        }, 200);
                    }
                });
            });
            
            // Add click animation
            button.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.92) translateY(5px) rotateX(10deg)';
            });
            
            button.addEventListener('mouseup', function() {
                this.style.transform = 'scale(1.1) translateY(-10px) rotateX(-5deg)';
                setTimeout(() => {
                    this.style.transform = 'scale(1.05) translateY(-5px) rotateX(3deg)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(0) rotateX(0deg)';
                    }, 200);
                }, 200);
            });
        });
        
        // Add some random movement to icons on page load
        window.addEventListener('load', function() {
            animateIcons();
            
            // Make the title and tagline float up with delay on load
            setTimeout(() => {
                document.querySelector('.bank-title').classList.add('animate__animated', 'animate__bounceInDown');
                setTimeout(() => {
                    document.querySelector('.tagline-container').classList.add('animate__animated', 'animate__fadeInUp');
                }, 500);
            }, 500);
        });
        
        function animateIcons() {
            setTimeout(() => {
                const icons = document.querySelectorAll('.login-icon');
                icons.forEach((icon, index) => {
                    const randomDegree = Math.floor(Math.random() * 40) - 20;
                    const randomScale = 1 + Math.random() * 0.3;
                    icon.style.transform = `rotate(${randomDegree}deg) scale(${randomScale})`;
                    setTimeout(() => {
                        icon.style.transform = 'rotate(0deg) scale(1)';
                    }, 600 + index * 100);
                });
            }, 2000);
            
            // Repeat the animation every 15 seconds
            setTimeout(animateIcons, 15000);
        }
        
        // Add floating effect to login buttons
        function floatButtons() {
            const buttons = document.querySelectorAll('.btn-login');
            const buttonDelay = 300;
            
            buttons.forEach((button, index) => {
                setTimeout(() => {
                    button.style.transform = 'translateY(-15px) rotateX(10deg) rotateY(5deg)';
                    setTimeout(() => {
                        button.style.transform = 'translateY(-8px) rotateX(5deg) rotateY(-3deg)';
                        setTimeout(() => {
                            button.style.transform = 'translateY(0) rotateX(0deg) rotateY(0deg)';
                        }, 400);
                    }, 300);
                }, index * buttonDelay);
            });
        }
        
        // Run float animation every 12 seconds
        setInterval(floatButtons, 12000);
        
        // Run initial float animation after a delay
        setTimeout(floatButtons, 4000);
        
        // Add a random shake effect to login container occasionally
        function randomShake() {
            const loginContainer = document.querySelector('.login-container');
            const random = Math.random();
            
            if (random > 0.7) { // Only shake sometimes
                const intensity = 3 + Math.random() * 5;
                loginContainer.style.transform = `translateY(-50%) translateX(${intensity}px)`;
                
                setTimeout(() => {
                    loginContainer.style.transform = `translateY(-50%) translateX(-${intensity}px)`;
                    
                    setTimeout(() => {
                        loginContainer.style.transform = `translateY(-50%) translateX(${intensity/2}px)`;
                        
                        setTimeout(() => {
                            loginContainer.style.transform = `translateY(-50%) translateX(-${intensity/2}px)`;
                            
                            setTimeout(() => {
                                loginContainer.style.transform = 'translateY(-50%) translateX(0)';
                            }, 100);
                        }, 100);
                    }, 100);
                }, 100);
            }
            
            // Schedule next random shake
            setTimeout(randomShake, 5000 + Math.random() * 5000);
        }
        
        // Start random shake effect after some time
        setTimeout(randomShake, 7000);
    </script>
</body>
</html>