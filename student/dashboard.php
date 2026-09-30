<?php
session_start();
require_once "../config/db.php";

/* ================= LOGIN CHECK ================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

/* ================= STUDENT ROLE CHECK ================= */

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$full_name = $_SESSION["full_name"] ?? "Student";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Dashboard - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        body {
            background: #f8faff !important;
        }

        .student-dashboard {
            max-width: 1450px;
            margin: 0 auto;
            padding: 38px 45px 60px;
        }


        /* =====================================================
           HERO
        ===================================================== */

        .student-hero {
            position: relative;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: space-between;

            min-height: 300px;

            padding: 42px 55px;

            border-radius: 24px;

            background:
                linear-gradient(
                    110deg,
                    #eef4ff 0%,
                    #f5f6ff 55%,
                    #e9edff 100%
                );

            border: 1px solid #dbe4fb;

            box-shadow:
                0 12px 35px rgba(31, 53, 110, 0.08);

            margin-bottom: 48px;
        }


        .student-hero::before {
            content: "";

            position: absolute;

            width: 330px;
            height: 330px;

            right: -100px;
            top: -150px;

            border-radius: 50%;

            background: rgba(91, 105, 220, 0.08);
        }


        .student-hero::after {
            content: "";

            position: absolute;

            width: 220px;
            height: 220px;

            right: 220px;
            bottom: -150px;

            border-radius: 50%;

            background: rgba(91, 105, 220, 0.06);
        }


        /* =====================================================
           HERO CONTENT
        ===================================================== */

        .student-hero-content {
            position: relative;
            z-index: 3;

            width: 58%;

            text-align: left;
        }


        .student-label {
            display: inline-flex;
            align-items: center;

            padding: 9px 17px;

            background: #dce8ff;

            color: #2854b8;

            border-radius: 30px;

            font-size: 13px;
            font-weight: 800;

            letter-spacing: 0.5px;

            margin-bottom: 18px;
        }


        .student-hero-content h1 {
            margin: 0;

            color: #1b3475;

            font-size: 46px;
            line-height: 1.12;

            font-weight: 800;

            letter-spacing: -1px;
        }


        .student-hero-content h1 span {
            color: #3157ad;
        }


        .student-hero-content p {
            margin: 18px 0 0;

            max-width: 720px;

            color: #63779f;

            font-size: 18px;

            line-height: 1.65;
        }


        /* =====================================================
           SVG ILLUSTRATION
        ===================================================== */

        .student-hero-visual {
            position: relative;
            z-index: 3;

            width: 380px;
            min-width: 380px;

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .education-illustration {
            width: 350px;
            height: 230px;

            display: block;
        }


        /* =====================================================
           WORKSPACE
        ===================================================== */

        .workspace-title {
            margin-bottom: 25px;
        }


        .workspace-title h2 {
            margin: 0;

            color: #172f69;

            font-size: 30px;

            font-weight: 800;
        }


        .workspace-title p {
            margin: 8px 0 0;

            color: #7183a6;

            font-size: 16px;
        }


        /* =====================================================
           CARDS
        ===================================================== */

        .student-card-grid {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 24px;
        }


        .student-card {
            position: relative;
            overflow: hidden;

            display: flex;
            flex-direction: column;

            min-height: 285px;

            padding: 29px;

            background: #ffffff;

            border: 1px solid #e1e8f5;

            border-radius: 18px;

            text-decoration: none;

            box-shadow:
                0 7px 24px rgba(31, 53, 110, 0.055);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .student-card:hover {
            transform: translateY(-5px);

            box-shadow:
                0 15px 35px rgba(31, 53, 110, 0.11);
        }


        .student-card::after {
            content: "";

            position: absolute;

            width: 110px;
            height: 110px;

            right: -40px;
            top: -40px;

            border-radius: 50%;

            background: #f5f7ff;
        }


        /* =====================================================
           CARD ICON
        ===================================================== */

        .student-card-icon {
            position: relative;
            z-index: 2;

            width: 64px;
            height: 64px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 16px;

            font-size: 30px;

            margin-bottom: 23px;
        }


        .blue-icon {
            background: #e8f0ff;
        }

        .green-icon {
            background: #e5f8ef;
        }

        .purple-icon {
            background: #eee9ff;
        }

        .orange-icon {
            background: #fff1df;
        }

        .pink-icon {
            background: #ffe9f1;
        }

        .cyan-icon {
            background: #e3f7fa;
        }


        /* =====================================================
           CARD TEXT
        ===================================================== */

        .student-card h3 {
            position: relative;
            z-index: 2;

            margin: 0;

            color: #19356f;

            font-size: 23px;

            font-weight: 800;
        }


        .student-card p {
            position: relative;
            z-index: 2;

            margin: 11px 0 24px;

            color: #7183a6;

            font-size: 15px;

            line-height: 1.65;
        }


        /* =====================================================
           CARD BUTTON
        ===================================================== */

        .student-card-button {
            position: relative;
            z-index: 2;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            width: fit-content;

            margin-top: auto;

            padding: 10px 17px;

            border-radius: 9px;

            color: #ffffff;

            font-size: 13px;

            font-weight: 700;

            text-decoration: none;
        }


        .blue-button {
            background: #3569d4;
        }

        .green-button {
            background: #179b76;
        }

        .purple-button {
            background: #6747d9;
        }

        .orange-button {
            background: #df8a2e;
        }

        .pink-button {
            background: #d45183;
        }

        .cyan-button {
            background: #198fa0;
        }


        .button-arrow {
            margin-left: 8px;

            font-size: 16px;
        }


        /* =====================================================
           QUICK TIP
        ===================================================== */

        .student-tip {
            margin-top: 42px;

            padding: 27px 30px;

            background: #ffffff;

            border: 1px solid #e1e8f5;

            border-radius: 18px;

            box-shadow:
                0 7px 24px rgba(31, 53, 110, 0.05);
        }


        .student-tip h3 {
            margin: 0 0 7px;

            color: #19356f;

            font-size: 21px;
        }


        .student-tip p {
            margin: 0;

            color: #7183a6;

            line-height: 1.7;

            font-size: 15px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1150px) {

            .student-dashboard {
                padding: 30px;
            }

            .student-hero {
                padding: 38px;
            }

            .student-hero-content h1 {
                font-size: 39px;
            }

            .student-hero-visual {
                width: 320px;
                min-width: 320px;
            }

            .education-illustration {
                width: 300px;
                height: 200px;
            }

            .student-card-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 800px) {

            .student-dashboard {
                padding: 24px 18px 45px;
            }

            .student-hero {
                flex-direction: column;

                align-items: flex-start;

                padding: 32px 27px;

                min-height: auto;
            }

            .student-hero-content {
                width: 100%;
            }

            .student-hero-content h1 {
                font-size: 34px;
            }

            .student-hero-content p {
                font-size: 16px;
            }

            .student-hero-visual {
                width: 100%;
                min-width: 0;

                margin-top: 25px;
            }

            .student-card-grid {
                grid-template-columns: 1fr;

                gap: 18px;
            }

        }


        @media (max-width: 500px) {

            .student-hero {
                padding: 27px 21px;

                border-radius: 18px;
            }

            .student-hero-content h1 {
                font-size: 29px;
            }

            .student-hero-content p {
                font-size: 15px;
            }

            .education-illustration {
                width: 275px;
                height: 180px;
            }

            .student-card {
                min-height: 270px;

                padding: 25px;
            }

            .workspace-title h2 {
                font-size: 26px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="nav-brand">
        🧠 Online Quiz
    </div>


    <div class="nav-links">

        <a
            href="dashboard.php"
            class="active"
        >
            Dashboard
        </a>

        <a href="available_quizzes.php">
            Quizzes
        </a>

        <a href="my_quizzes.php">
            My Quizzes
        </a>

        <a href="result.php">
            Results
        </a>

        <a href="leaderboard.php">
            Leaderboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="student-dashboard">


    <!-- =================================================
         HERO
    ================================================= -->

    <section class="student-hero">


        <div class="student-hero-content">

            <div class="student-label">
                🎓 STUDENT DASHBOARD
            </div>


            <h1>
                Welcome,
                <span>
                    <?php
                    echo htmlspecialchars($full_name);
                    ?>
                </span>
                ! 👋
            </h1>


            <p>
                Explore quizzes, create your own quizzes,
                track your results and improve your performance.
            </p>

        </div>


        <!-- =================================================
             EDUCATION ILLUSTRATION
        ================================================= -->

        <div class="student-hero-visual">

            <svg
                class="education-illustration"
                viewBox="0 0 400 260"
                xmlns="http://www.w3.org/2000/svg"
                aria-label="Education illustration"
            >

                <!-- Background Circle -->

                <circle
                    cx="205"
                    cy="130"
                    r="105"
                    fill="#e4eaff"
                />

                <circle
                    cx="305"
                    cy="65"
                    r="35"
                    fill="#d4ddff"
                />


                <!-- Books -->

                <rect
                    x="115"
                    y="177"
                    width="175"
                    height="32"
                    rx="8"
                    fill="#345bc7"
                    transform="rotate(-3 115 177)"
                />

                <rect
                    x="105"
                    y="148"
                    width="175"
                    height="32"
                    rx="8"
                    fill="#40aa8b"
                    transform="rotate(3 105 148)"
                />

                <rect
                    x="125"
                    y="120"
                    width="165"
                    height="31"
                    rx="8"
                    fill="#f0b847"
                    transform="rotate(-2 125 120)"
                />


                <!-- Book Lines -->

                <line
                    x1="145"
                    y1="136"
                    x2="245"
                    y2="136"
                    stroke="#ffffff"
                    stroke-width="4"
                    stroke-linecap="round"
                    opacity="0.65"
                />

                <line
                    x1="125"
                    y1="163"
                    x2="235"
                    y2="163"
                    stroke="#ffffff"
                    stroke-width="4"
                    stroke-linecap="round"
                    opacity="0.65"
                />

                <line
                    x1="135"
                    y1="191"
                    x2="245"
                    y2="191"
                    stroke="#ffffff"
                    stroke-width="4"
                    stroke-linecap="round"
                    opacity="0.65"
                />


                <!-- Graduation Cap -->

                <polygon
                    points="200,48 295,82 200,116 105,82"
                    fill="#243d87"
                />

                <path
                    d="M140 93
                       C140 120 165 134 200 134
                       C235 134 260 120 260 93
                       L200 116
                       Z"
                    fill="#3155a9"
                />


                <!-- Cap Tassel -->

                <line
                    x1="295"
                    y1="82"
                    x2="305"
                    y2="125"
                    stroke="#243d87"
                    stroke-width="5"
                />

                <circle
                    cx="305"
                    cy="128"
                    r="7"
                    fill="#f0b847"
                />


                <!-- Small Sparkles -->

                <circle
                    cx="88"
                    cy="92"
                    r="6"
                    fill="#6d82db"
                />

                <circle
                    cx="320"
                    cy="165"
                    r="6"
                    fill="#6d82db"
                />

                <circle
                    cx="95"
                    cy="210"
                    r="4"
                    fill="#40aa8b"
                />

            </svg>

        </div>

    </section>


    <!-- =================================================
         WORKSPACE
    ================================================= -->

    <div class="workspace-title">

        <h2>
            Your Quiz Workspace
        </h2>

        <p>
            Choose an option below to continue.
        </p>

    </div>


    <!-- =================================================
         DASHBOARD CARDS
    ================================================= -->

    <div class="student-card-grid">


        <!-- AVAILABLE QUIZZES -->

        <a
            href="available_quizzes.php"
            class="student-card"
        >

            <div class="student-card-icon blue-icon">
                📄
            </div>

            <h3>
                Available Quizzes
            </h3>

            <p>
                Explore public quizzes and test
                your knowledge.
            </p>

            <span class="student-card-button blue-button">

                Browse Quizzes

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


        <!-- MY QUIZZES -->

        <a
            href="my_quizzes.php"
            class="student-card"
        >

            <div class="student-card-icon green-icon">
                📚
            </div>

            <h3>
                My Quizzes
            </h3>

            <p>
                View and manage the quizzes
                you have created.
            </p>

            <span class="student-card-button green-button">

                Manage Quizzes

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


        <!-- CREATE QUIZ -->

        <a
            href="create_quiz.php"
            class="student-card"
        >

            <div class="student-card-icon purple-icon">
                ✚
            </div>

            <h3>
                Create Quiz
            </h3>

            <p>
                Create a quiz and add your own
                questions and answers.
            </p>

            <span class="student-card-button purple-button">

                Create New Quiz

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


        <!-- RESULTS -->

        <a
            href="result.php"
            class="student-card"
        >

            <div class="student-card-icon orange-icon">
                📊
            </div>

            <h3>
                My Results
            </h3>

            <p>
                View your quiz scores and
                previous attempts.
            </p>

            <span class="student-card-button orange-button">

                View Results

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


        <!-- LEADERBOARD -->

        <a
            href="leaderboard.php"
            class="student-card"
        >

            <div class="student-card-icon pink-icon">
                🏆
            </div>

            <h3>
                Leaderboard
            </h3>

            <p>
                Compare quiz performance
                with other students.
            </p>

            <span class="student-card-button pink-button">

                View Leaderboard

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


        <!-- PROFILE -->

        <a
            href="profile.php"
            class="student-card"
        >

            <div class="student-card-icon cyan-icon">
                👤
            </div>

            <h3>
                My Profile
            </h3>

            <p>
                Manage your account information
                and password.
            </p>

            <span class="student-card-button cyan-button">

                Manage Profile

                <span class="button-arrow">
                    →
                </span>

            </span>

        </a>


    </div>


    <!-- =================================================
         QUICK TIP
    ================================================= -->

    <section class="student-tip">

        <h3>
            💡 Quick Tip
        </h3>

        <p>
            Create your own quizzes, share them with others,
            attempt quizzes and track your performance from
            the Results and Leaderboard sections.
        </p>

    </section>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="container">

        <p>
            © <?php echo date("Y"); ?>
            Online Quiz System.
            All rights reserved.
        </p>

    </div>

</footer>


</body>

</html>