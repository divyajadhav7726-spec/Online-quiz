<?php
session_start();

$is_logged_in = isset($_SESSION["user_id"]);
$role = $_SESSION["role"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Quiz System</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* =========================
           HOME PAGE
        ========================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #172554;
        }

        /* =========================
           NAVBAR
        ========================= */

        .home-navbar {
            background: #07194f;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        }

        .home-nav-container {
            max-width: 1200px;
            margin: auto;
            min-height: 76px;
            padding: 0 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .home-brand {
            display: flex;
            align-items: center;
            gap: 10px;

            color: white;
            text-decoration: none;

            font-size: 25px;
            font-weight: 800;
            white-space: nowrap;
        }

        .home-brand-icon {
            width: 44px;
            height: 44px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                135deg,
                #60a5fa,
                #6366f1
            );

            border-radius: 13px;
            font-size: 24px;

            box-shadow:
                0 8px 20px rgba(79, 70, 229, 0.35);
        }

        .home-brand span {
            color: #8b9cff;
        }

        .home-nav-links {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .home-nav-links a {
            text-decoration: none;
            color: #e0e7ff;

            padding: 11px 18px;
            border-radius: 9px;

            font-size: 15px;
            font-weight: 700;

            transition: 0.2s ease;
        }

        .home-nav-links a:hover {
            background: rgba(255,255,255,0.10);
            color: white;
        }

        .home-login-btn {
            border: 1px solid rgba(255,255,255,0.35);
        }

        .home-register-btn {
            background: linear-gradient(
                135deg,
                #6366f1,
                #4f46e5
            );

            color: white !important;

            padding-left: 22px !important;
            padding-right: 22px !important;

            box-shadow:
                0 7px 20px rgba(79,70,229,0.30);
        }

        .home-register-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(
                135deg,
                #818cf8,
                #6366f1
            ) !important;
        }

        .home-logout-btn {
            background: #ef4444;
            color: white !important;
        }

        /* =========================
           HERO
        ========================= */

        .home-hero {
            background:
                radial-gradient(
                    circle at 80% 30%,
                    rgba(99,102,241,0.35),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #07194f,
                    #172554 55%,
                    #312e81
                );

            color: white;
            padding: 75px 20px 80px;

            overflow: hidden;
        }

        .home-hero-container {
            max-width: 1200px;
            margin: auto;

            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 55px;
            align-items: center;
        }

        .hero-content {
            padding-left: 10px;
        }

        .hero-small-badge {
            display: inline-flex;
            align-items: center;

            padding: 9px 16px;

            border: 1px solid rgba(255,255,255,0.30);
            border-radius: 30px;

            color: #dbeafe;
            background: rgba(255,255,255,0.06);

            font-size: 14px;
            font-weight: 600;

            margin-bottom: 22px;
        }

        .hero-content h1 {
            font-size: 56px;
            line-height: 1.08;

            margin: 0 0 20px;

            font-weight: 800;
            letter-spacing: -1.5px;
        }

        .hero-content h1 span {
            display: block;

            background: linear-gradient(
                90deg,
                #60a5fa,
                #a78bfa
            );

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            max-width: 600px;

            color: #dbeafe;

            font-size: 18px;
            line-height: 1.7;

            margin: 0 0 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .hero-primary-btn,
        .hero-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 14px 24px;

            border-radius: 11px;

            text-decoration: none;

            font-weight: 800;
            font-size: 15px;

            transition: 0.2s ease;
        }

        .hero-primary-btn {
            background: white;
            color: #312e81;

            box-shadow:
                0 10px 25px rgba(0,0,0,0.18);
        }

        .hero-primary-btn:hover {
            transform: translateY(-3px);
            background: #eef2ff;
        }

        .hero-secondary-btn {
            color: white;

            border: 1px solid rgba(255,255,255,0.35);

            background: rgba(255,255,255,0.08);
        }

        .hero-secondary-btn:hover {
            background: rgba(255,255,255,0.15);
        }

        /* =========================
           HERO IMAGE
        ========================= */

        .hero-image-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-image-card {
            width: 100%;
            max-width: 570px;

            padding: 10px;

            border-radius: 25px;

            background: rgba(255,255,255,0.08);

            border: 1px solid rgba(255,255,255,0.18);

            box-shadow:
                0 25px 60px rgba(0,0,0,0.28);

            backdrop-filter: blur(8px);
        }

        .hero-image {
            display: block;

            width: 100%;
            height: auto;

            border-radius: 18px;
        }

        /* =========================
           FEATURES
        ========================= */

        .home-section {
            padding: 80px 20px;
            background: white;
        }

        .home-section.alt {
            background: #f8fafc;
        }

        .home-container {
            max-width: 1200px;
            margin: auto;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 45px;
        }

        .section-heading span {
            color: #4f46e5;

            font-size: 13px;
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-heading h2 {
            margin: 10px 0;

            font-size: 35px;
            color: #172554;
        }

        .section-heading p {
            max-width: 650px;
            margin: auto;

            color: #64748b;

            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .feature-card {
            padding: 30px 23px;

            background: white;

            border: 1px solid #e2e8f0;
            border-radius: 17px;

            text-align: center;

            transition: 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-7px);

            box-shadow:
                0 18px 40px rgba(15,23,42,0.10);
        }

        .feature-icon {
            width: 58px;
            height: 58px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 16px;

            background: #eef2ff;

            font-size: 28px;
        }

        .feature-card h3 {
            color: #172554;
            margin: 0 0 10px;
        }

        .feature-card p {
            color: #64748b;

            line-height: 1.6;
            font-size: 14px;

            margin: 0;
        }

        /* =========================
           HOW IT WORKS
        ========================= */

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .step-card {
            text-align: center;
            padding: 30px;
        }

        .step-number {
            width: 58px;
            height: 58px;

            margin: 0 auto 18px;

            border-radius: 50%;

            background: linear-gradient(
                135deg,
                #6366f1,
                #4f46e5
            );

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;
            font-weight: 800;

            box-shadow:
                0 10px 22px rgba(79,70,229,0.25);
        }

        .step-card h3 {
            color: #172554;
            margin-bottom: 10px;
        }

        .step-card p {
            color: #64748b;
            line-height: 1.6;
        }

        /* =========================
           CATEGORIES
        ========================= */

        .category-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        .category-card {
            padding: 25px 15px;

            background: white;

            border: 1px solid #e2e8f0;
            border-radius: 15px;

            text-align: center;

            font-weight: 700;
            color: #334155;

            transition: 0.2s ease;
        }

        .category-card:hover {
            transform: translateY(-4px);

            border-color: #a5b4fc;

            box-shadow:
                0 12px 28px rgba(15,23,42,0.08);
        }

        .category-card div {
            font-size: 30px;
            margin-bottom: 9px;
        }

        /* =========================
           CTA
        ========================= */

        .home-cta {
            max-width: 1000px;

            margin: auto;

            padding: 60px 30px;

            border-radius: 25px;

            text-align: center;

            background:
                linear-gradient(
                    135deg,
                    #172554,
                    #4f46e5
                );

            color: white;

            box-shadow:
                0 20px 45px rgba(49,46,129,0.20);
        }

        .home-cta h2 {
            margin: 0 0 12px;
            font-size: 34px;
        }

        .home-cta p {
            color: #dbeafe;
            margin-bottom: 25px;
        }

        /* =========================
           FOOTER
        ========================= */

        .home-footer {
            background: #071127;

            color: #cbd5e1;

            padding: 35px 20px;

            text-align: center;
        }

        .home-footer strong {
            color: white;
            font-size: 18px;
        }

        .home-footer p {
            margin: 8px 0 0;
            font-size: 14px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 950px) {

            .home-hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-image-wrapper {
                order: -1;
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .category-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 650px) {

            .home-nav-container {
                min-height: auto;
                padding: 14px 18px;

                flex-direction: column;
                gap: 14px;
            }

            .home-brand {
                font-size: 21px;
            }

            .home-nav-links {
                width: 100%;
                justify-content: center;
            }

            .home-nav-links a {
                padding: 9px 12px;
                font-size: 13px;
            }

            .hero-content h1 {
                font-size: 40px;
            }

            .home-hero {
                padding: 55px 18px 60px;
            }

            .feature-grid,
            .steps-grid,
            .category-grid {
                grid-template-columns: 1fr;
            }

            .section-heading h2 {
                font-size: 28px;
            }

            .home-cta h2 {
                font-size: 27px;
            }
        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<header class="home-navbar">

    <div class="home-nav-container">

        <a href="index.php" class="home-brand">

            <div class="home-brand-icon">
                🧠
            </div>

            Online <span>Quiz</span>

        </a>


        <nav class="home-nav-links">

            <a href="index.php">
                🏠 Home
            </a>

            <?php if ($is_logged_in): ?>

                <?php if ($role === "admin"): ?>

                    <a href="admin/dashboard.php">
                        Dashboard
                    </a>

                <?php else: ?>

                    <a href="student/dashboard.php">
                        Dashboard
                    </a>

                <?php endif; ?>

                <a href="logout.php" class="home-logout-btn">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php" class="home-login-btn">
                    👤 Login
                </a>

                <a href="register.php" class="home-register-btn">
                    👤 Register
                </a>

            <?php endif; ?>

        </nav>

    </div>

</header>



<!-- =========================
     HERO
========================= -->

<section class="home-hero">

    <div class="home-hero-container">


        <div class="hero-content">

            <div class="hero-small-badge">
                Learn &nbsp;•&nbsp; Practice &nbsp;•&nbsp; Compete &nbsp;•&nbsp; Improve
            </div>


            <h1>

                Test Your

                <span>
                    Knowledge
                </span>

            </h1>


            <p>

                Take online quizzes, improve your skills,
                challenge yourself and track your performance
                with our professional Online Quiz System.

            </p>


            <div class="hero-buttons">

                <?php if ($is_logged_in): ?>

                    <?php if ($role === "admin"): ?>

                        <a
                            href="admin/dashboard.php"
                            class="hero-primary-btn"
                        >
                            Open Dashboard →
                        </a>

                    <?php else: ?>

                        <a
                            href="student/dashboard.php"
                            class="hero-primary-btn"
                        >
                            Start Quiz →
                        </a>

                    <?php endif; ?>

                <?php else: ?>

                    <a
                        href="register.php"
                        class="hero-primary-btn"
                    >
                        Get Started →
                    </a>

                    <a
                        href="login.php"
                        class="hero-secondary-btn"
                    >
                        Login
                    </a>

                <?php endif; ?>

            </div>

        </div>



        <!-- HERO IMAGE -->

        <div class="hero-image-wrapper">

            <div class="hero-image-card">

                <img
                    src="images/quiz-hero.png"
                    alt="Online Quiz System"
                    class="hero-image"
                >

            </div>

        </div>


    </div>

</section>



<!-- =========================
     FEATURES
========================= -->

<section class="home-section">

    <div class="home-container">

        <div class="section-heading">

            <span>
                Our Features
            </span>

            <h2>
                Everything You Need for Online Quizzes
            </h2>

            <p>
                Create, attempt and manage quizzes
                from one professional platform.
            </p>

        </div>


        <div class="feature-grid">


            <div class="feature-card">

                <div class="feature-icon">
                    📝
                </div>

                <h3>
                    Create Quizzes
                </h3>

                <p>
                    Create your own quizzes with questions,
                    options, marks and difficulty levels.
                </p>

            </div>



            <div class="feature-card">

                <div class="feature-icon">
                    🎯
                </div>

                <h3>
                    Take Quizzes
                </h3>

                <p>
                    Attempt available quizzes with a
                    simple and interactive quiz interface.
                </p>

            </div>



            <div class="feature-card">

                <div class="feature-icon">
                    📊
                </div>

                <h3>
                    Track Results
                </h3>

                <p>
                    View your scores, percentages and
                    previous quiz attempts.
                </p>

            </div>



            <div class="feature-card">

                <div class="feature-icon">
                    🏆
                </div>

                <h3>
                    Leaderboard
                </h3>

                <p>
                    Compare quiz performance through
                    the leaderboard system.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- =========================
     HOW IT WORKS
========================= -->

<section class="home-section alt">

    <div class="home-container">

        <div class="section-heading">

            <span>
                How It Works
            </span>

            <h2>
                Start Your Quiz Journey
            </h2>

            <p>
                Follow three simple steps to begin
                learning and testing yourself.
            </p>

        </div>


        <div class="steps-grid">


            <div class="step-card">

                <div class="step-number">
                    1
                </div>

                <h3>
                    Create an Account
                </h3>

                <p>
                    Register and securely login
                    to your account.
                </p>

            </div>



            <div class="step-card">

                <div class="step-number">
                    2
                </div>

                <h3>
                    Choose a Quiz
                </h3>

                <p>
                    Select a quiz based on your
                    preferred category and difficulty.
                </p>

            </div>



            <div class="step-card">

                <div class="step-number">
                    3
                </div>

                <h3>
                    Get Your Result
                </h3>

                <p>
                    Submit your quiz and instantly
                    view your performance.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- =========================
     CATEGORIES
========================= -->

<section class="home-section">

    <div class="home-container">

        <div class="section-heading">

            <span>
                Quiz Categories
            </span>

            <h2>
                Explore Different Subjects
            </h2>

            <p>
                Practice your knowledge across
                multiple computer-related subjects.
            </p>

        </div>


        <div class="category-grid">


            <div class="category-card">

                <div>🐍</div>

                Python

            </div>



            <div class="category-card">

                <div>☕</div>

                Java

            </div>



            <div class="category-card">

                <div>🗄️</div>

                DBMS

            </div>



            <div class="category-card">

                <div>🌐</div>

                Web Development

            </div>



            <div class="category-card">

                <div>💻</div>

                Computer Fundamentals

            </div>


        </div>

    </div>

</section>



<!-- =========================
     CTA
========================= -->

<section class="home-section alt">

    <div class="home-cta">

        <h2>
            Ready to Test Your Knowledge?
        </h2>

        <p>
            Create an account and start exploring
            online quizzes today.
        </p>


        <?php if (!$is_logged_in): ?>

            <a
                href="register.php"
                class="hero-primary-btn"
            >
                Create Free Account →
            </a>

        <?php else: ?>

            <?php if ($role === "admin"): ?>

                <a
                    href="admin/dashboard.php"
                    class="hero-primary-btn"
                >
                    Open Dashboard →
                </a>

            <?php else: ?>

                <a
                    href="student/dashboard.php"
                    class="hero-primary-btn"
                >
                    Open Dashboard →
                </a>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer class="home-footer">

    <strong>
        🧠 Online Quiz System
    </strong>

    <p>
        Learn • Practice • Test • Improve
    </p>

    <p>
        © <?php echo date("Y"); ?>
        Online Quiz System.
        All rights reserved.
    </p>

</footer>


</body>

</html>