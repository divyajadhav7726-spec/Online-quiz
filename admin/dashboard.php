<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../student/dashboard.php");
    exit();
}

$admin_name = $_SESSION["full_name"] ?? "Admin";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* =====================================================
           ADMIN DASHBOARD
        ===================================================== */

        body {
            background: #f5f7fb;
        }

        .admin-page {
            padding-bottom: 60px;
        }

        /* =====================================================
           HERO
        ===================================================== */

        .admin-hero {
            position: relative;
            overflow: hidden;
            margin: 30px 0 35px;
            padding: 38px 42px;
            border-radius: 24px;
            background:
                linear-gradient(
                    135deg,
                    #172554 0%,
                    #312e81 55%,
                    #4338ca 100%
                );
            color: white;
            box-shadow:
                0 20px 45px rgba(49, 46, 129, 0.20);
        }

        .admin-hero::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -90px;
            top: -120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .admin-hero::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: 150px;
            bottom: -120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .admin-hero-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
        }

        .admin-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            margin-bottom: 15px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            color: #e0e7ff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .admin-hero h1 {
            margin: 0;
            font-size: 34px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .admin-hero h1 span {
            color: #c7d2fe;
        }

        .admin-hero p {
            max-width: 620px;
            margin: 12px 0 0;
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.7;
        }

        /* =====================================================
           QUICK STATS
        ===================================================== */

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 35px;
        }

        .admin-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(15,23,42,0.04);
        }

        .admin-stat-icon {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #eef2ff;
            font-size: 22px;
        }

        .admin-stat-text span {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .admin-stat-text strong {
            display: block;
            margin-top: 3px;
            color: #172554;
            font-size: 16px;
        }

        /* =====================================================
           SECTION HEADER
        ===================================================== */

        .admin-section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .admin-section-header h2 {
            margin: 0;
            color: #172554;
            font-size: 22px;
        }

        .admin-section-header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        /* =====================================================
           CARDS
        ===================================================== */

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .admin-card {
            position: relative;
            overflow: hidden;
            padding: 25px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 19px;
            box-shadow: 0 7px 24px rgba(15,23,42,0.05);
            transition:
                transform .25s ease,
                box-shadow .25s ease,
                border-color .25s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            border-color: #c7d2fe;
            box-shadow: 0 18px 35px rgba(15,23,42,0.09);
        }

        .admin-card::after {
            content: "";
            position: absolute;
            width: 90px;
            height: 90px;
            right: -45px;
            bottom: -45px;
            border-radius: 50%;
            background: #eef2ff;
            opacity: .7;
        }

        .admin-card-icon {
            position: relative;
            z-index: 1;
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            border-radius: 15px;
            background: #eef2ff;
            font-size: 25px;
        }

        .admin-card h3 {
            position: relative;
            z-index: 1;
            margin: 0;
            color: #172554;
            font-size: 18px;
            font-weight: 800;
        }

        .admin-card p {
            position: relative;
            z-index: 1;
            min-height: 48px;
            margin: 9px 0 20px;
            color: #64748b;
            line-height: 1.6;
            font-size: 13px;
        }

        .admin-card .btn {
            position: relative;
            z-index: 2;
        }

        /* =====================================================
           CARD COLORS
        ===================================================== */

        .card-purple .admin-card-icon {
            background: #eef2ff;
        }

        .card-blue .admin-card-icon {
            background: #eff6ff;
        }

        .card-green .admin-card-icon {
            background: #ecfdf5;
        }

        .card-orange .admin-card-icon {
            background: #fff7ed;
        }

        .card-red .admin-card-icon {
            background: #fef2f2;
        }

        .card-gray .admin-card-icon {
            background: #f1f5f9;
        }

        /* =====================================================
           ADMIN INFO
        ===================================================== */

        .admin-info {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-top: 28px;
            padding: 20px 22px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 17px;
        }

        .admin-info-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 12px;
            background: #eef2ff;
            font-size: 21px;
        }

        .admin-info h3 {
            margin: 0;
            color: #172554;
            font-size: 15px;
        }

        .admin-info p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1000px) {

            .admin-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .admin-hero {
                margin-top: 20px;
                padding: 28px 24px;
                border-radius: 20px;
            }

            .admin-hero h1 {
                font-size: 27px;
            }

            .admin-hero p {
                font-size: 13px;
            }

            .admin-stats,
            .admin-grid {
                grid-template-columns: 1fr;
            }

            .admin-section-header {
                display: block;
            }

            .admin-info {
                align-items: flex-start;
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

        <a href="manage_quizzes.php">
            Quizzes
        </a>

        <a href="manage_questions.php">
            Questions
        </a>

        <a href="view_results.php">
            Results
        </a>

        <a href="manage_users.php">
            Users
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

<main class="container admin-page">


    <!-- =================================================
         HERO
    ================================================= -->

    <section class="admin-hero">

        <div class="admin-hero-content">

            <div class="admin-tag">
                🛡️ ADMIN CONTROL PANEL
            </div>

            <h1>
                Welcome back,
                <span>
                    <?php
                    echo htmlspecialchars($admin_name);
                    ?>
                </span>
                👋
            </h1>

            <p>
                Manage quizzes, questions, users and student
                performance from your central dashboard.
            </p>

        </div>

    </section>


    <!-- =================================================
         QUICK STATS
    ================================================= -->

    <section class="admin-stats">

        <div class="admin-stat">

            <div class="admin-stat-icon">
                📝
            </div>

            <div class="admin-stat-text">

                <span>
                    Module
                </span>

                <strong>
                    Quizzes
                </strong>

            </div>

        </div>


        <div class="admin-stat">

            <div class="admin-stat-icon">
                ❓
            </div>

            <div class="admin-stat-text">

                <span>
                    Module
                </span>

                <strong>
                    Questions
                </strong>

            </div>

        </div>


        <div class="admin-stat">

            <div class="admin-stat-icon">
                📊
            </div>

            <div class="admin-stat-text">

                <span>
                    Module
                </span>

                <strong>
                    Results
                </strong>

            </div>

        </div>


        <div class="admin-stat">

            <div class="admin-stat-icon">
                👥
            </div>

            <div class="admin-stat-text">

                <span>
                    Module
                </span>

                <strong>
                    Users
                </strong>

            </div>

        </div>

    </section>


    <!-- =================================================
         SECTION TITLE
    ================================================= -->

    <div class="admin-section-header">

        <div>

            <h2>
                System Management
            </h2>

            <p>
                Access and manage all major quiz system modules.
            </p>

        </div>

    </div>


    <!-- =================================================
         MANAGEMENT CARDS
    ================================================= -->

    <section class="admin-grid">


        <!-- QUIZZES -->

        <div class="admin-card card-purple">

            <div class="admin-card-icon">
                📝
            </div>

            <h3>
                Manage Quizzes
            </h3>

            <p>
                Create, view and manage quizzes available
                on the platform.
            </p>

            <a
                href="manage_quizzes.php"
                class="btn btn-primary"
            >
                Open Quizzes →
            </a>

        </div>


        <!-- QUESTIONS -->

        <div class="admin-card card-blue">

            <div class="admin-card-icon">
                ❓
            </div>

            <h3>
                Manage Questions
            </h3>

            <p>
                Add and manage questions and answer options
                for your quizzes.
            </p>

            <a
                href="manage_questions.php"
                class="btn btn-primary"
            >
                Open Questions →
            </a>

        </div>


        <!-- RESULTS -->

        <div class="admin-card card-green">

            <div class="admin-card-icon">
                📊
            </div>

            <h3>
                Student Results
            </h3>

            <p>
                View quiz attempts, scores and student
                performance records.
            </p>

            <a
                href="view_results.php"
                class="btn btn-success"
            >
                View Results →
            </a>

        </div>


        <!-- USERS -->

        <div class="admin-card card-orange">

            <div class="admin-card-icon">
                👥
            </div>

            <h3>
                Manage Users
            </h3>

            <p>
                View registered students and administrator
                accounts.
            </p>

            <a
                href="manage_users.php"
                class="btn btn-primary"
            >
                View Users →
            </a>

        </div>


        <!-- ACCOUNT -->

        <div class="admin-card card-red">

            <div class="admin-card-icon">
                👤
            </div>

            <h3>
                Admin Account
            </h3>

            <p>
                You are currently logged in as
                <strong>
                    <?php
                    echo htmlspecialchars($admin_name);
                    ?>
                </strong>.
            </p>

            <a
                href="../logout.php"
                class="btn btn-danger"
            >
                Logout
            </a>

        </div>


        <!-- SYSTEM -->

        <div class="admin-card card-gray">

            <div class="admin-card-icon">
                ⚙️
            </div>

            <h3>
                System Overview
            </h3>

            <p>
                Access the main quiz management area
                and control your platform.
            </p>

            <a
                href="manage_quizzes.php"
                class="btn btn-secondary"
            >
                Open System →
            </a>

        </div>


    </section>


    <!-- =================================================
         INFO
    ================================================= -->

    <div class="admin-info">

        <div class="admin-info-icon">
            🔐
        </div>

        <div>

            <h3>
                Administrator Access
            </h3>

            <p>
                This dashboard is available only to users
                with administrator privileges.
            </p>

        </div>

    </div>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <p>
        © <?php echo date("Y"); ?> Online Quiz System.
        All Rights Reserved.
    </p>

</footer>


</body>

</html>