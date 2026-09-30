<?php
session_start();

require_once "../config/db.php";

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   STUDENT ROLE CHECK
========================================================= */

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$current_user_id = (int) $_SESSION["user_id"];

/* =========================================================
   LEADERBOARD QUERY
========================================================= */

$sql = "
    SELECT
        u.id,
        u.full_name,
        SUM(qa.score) AS total_score,
        COUNT(qa.id) AS total_attempts
    FROM users u
    INNER JOIN quiz_attempts qa
        ON qa.user_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.id, u.full_name
    ORDER BY
        total_score DESC,
        total_attempts ASC,
        u.full_name ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Leaderboard - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* =====================================================
           LEADERBOARD PAGE
        ===================================================== */

        .leaderboard-page {
            padding-bottom: 50px;
        }

        /* =====================================================
           TOP INTRO
        ===================================================== */

        .leaderboard-top {
            margin-bottom: 22px;
        }

        .leaderboard-intro {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 26px 30px;
            background: linear-gradient(
                135deg,
                #172554,
                #312e81
            );
            border-radius: 20px;
            color: white;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        }

        .leaderboard-intro::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: -60px;
            top: -80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }

        .leaderboard-trophy {
            position: relative;
            z-index: 1;
            width: 68px;
            height: 68px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            font-size: 35px;
        }

        .leaderboard-intro-content {
            position: relative;
            z-index: 1;
        }

        .leaderboard-intro h2 {
            margin: 0;
            font-size: 21px;
            font-weight: 800;
        }

        .leaderboard-intro p {
            margin: 6px 0 0;
            color: rgba(255,255,255,0.75);
            font-size: 14px;
        }

        /* =====================================================
           LEADERBOARD CARD
        ===================================================== */

        .leaderboard-card {
            overflow: hidden;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .leaderboard-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 22px 25px;
            border-bottom: 1px solid #e2e8f0;
        }

        .leaderboard-card-header h2 {
            margin: 0;
            color: #172554;
            font-size: 19px;
        }

        .leaderboard-card-header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .leaderboard-count {
            padding: 8px 12px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        /* =====================================================
           TABLE
        ===================================================== */

        .leaderboard-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .leaderboard-table th {
            padding: 15px 20px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .leaderboard-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .leaderboard-table tbody tr {
            transition: background .2s ease;
        }

        .leaderboard-table tbody tr:hover {
            background: #f8fafc;
        }

        .leaderboard-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* =====================================================
           CURRENT USER
        ===================================================== */

        .leaderboard-table tr.current-student {
            background: #eef2ff;
        }

        .leaderboard-table tr.current-student:hover {
            background: #e0e7ff;
        }

        /* =====================================================
           RANK
        ===================================================== */

        .rank-medal {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 23px;
        }

        .rank-number {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            font-size: 14px;
            font-weight: 800;
        }

        /* =====================================================
           STUDENT
        ===================================================== */

        .leaderboard-student {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .leaderboard-avatar {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #4f46e5;
            color: white;
            font-size: 16px;
            font-weight: 800;
        }

        .leaderboard-student strong {
            color: #172554;
            font-size: 14px;
        }

        .you-badge {
            display: inline-block;
            margin-left: 7px;
            padding: 4px 8px;
            border-radius: 6px;
            background: #4f46e5;
            color: white;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        /* =====================================================
           SCORE
        ===================================================== */

        .leaderboard-score {
            display: inline-flex;
            align-items: baseline;
            gap: 5px;
            color: #172554;
            font-size: 17px;
            font-weight: 800;
        }

        .leaderboard-score small {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
        }

        /* =====================================================
           ATTEMPTS
        ===================================================== */

        .attempt-badge {
            display: inline-block;
            padding: 8px 11px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .leaderboard-empty {
            padding: 65px 25px;
            text-align: center;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .leaderboard-empty-icon {
            width: 78px;
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            border-radius: 22px;
            background: #fefce8;
            font-size: 38px;
        }

        .leaderboard-empty h2 {
            margin: 0;
            color: #172554;
            font-size: 22px;
        }

        .leaderboard-empty p {
            max-width: 500px;
            margin: 9px auto 22px;
            color: #64748b;
            line-height: 1.6;
            font-size: 14px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 700px) {

            .leaderboard-intro {
                padding: 22px;
            }

            .leaderboard-trophy {
                width: 58px;
                height: 58px;
                font-size: 30px;
            }

            .leaderboard-intro h2 {
                font-size: 18px;
            }

            .leaderboard-card-header {
                align-items: flex-start;
            }

        }

        @media (max-width: 500px) {

            .leaderboard-intro {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <div class="nav-brand">
        🧠 Online Quiz
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
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

        <a
            href="leaderboard.php"
            class="active"
        >
            Leaderboard
        </a>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="container leaderboard-page">


    <!-- =====================================================
         PAGE HEADER
    ===================================================== -->

    <div class="page-header">

        <div>

            <span class="welcome-label page-label">
                PERFORMANCE
            </span>

            <h1>
                Student Leaderboard 🏆
            </h1>

            <p>
                View student performance based on total marks earned.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            ← Dashboard
        </a>

    </div>


    <?php if ($result && $result->num_rows > 0): ?>


        <!-- =================================================
             INTRO
        ================================================= -->

        <div class="leaderboard-top">

            <div class="leaderboard-intro">

                <div class="leaderboard-trophy">
                    🏆
                </div>

                <div class="leaderboard-intro-content">

                    <h2>
                        Top Performers
                    </h2>

                    <p>
                        Rankings are calculated from students' total quiz marks.
                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             LEADERBOARD
        ================================================= -->

        <div class="leaderboard-card">


            <div class="leaderboard-card-header">

                <div>

                    <h2>
                        Overall Rankings
                    </h2>

                    <p>
                        All students with completed quiz attempts
                    </p>

                </div>

                <div class="leaderboard-count">

                    <?php
                    echo (int) $result->num_rows;
                    ?>

                    Students

                </div>

            </div>


            <div class="leaderboard-table-wrap">

                <table class="leaderboard-table">

                    <thead>

                        <tr>

                            <th>
                                Rank
                            </th>

                            <th>
                                Student
                            </th>

                            <th>
                                Total Marks
                            </th>

                            <th>
                                Quiz Attempts
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $rank = 1;

                    while (
                        $student = $result->fetch_assoc()
                    ):

                        $student_id =
                            (int) $student["id"];

                        $is_current_user =
                            (
                                $student_id ===
                                $current_user_id
                            );

                        $student_name =
                            trim(
                                (string)
                                $student["full_name"]
                            );

                        $first_letter =
                            $student_name !== ""
                                ? strtoupper(
                                    substr(
                                        $student_name,
                                        0,
                                        1
                                    )
                                )
                                : "?";

                        $total_score =
                            (int)
                            $student["total_score"];

                        $total_attempts =
                            (int)
                            $student["total_attempts"];

                    ?>

                        <tr
                            class="<?php
                                echo $is_current_user
                                    ? 'current-student'
                                    : '';
                            ?>"
                        >

                            <!-- RANK -->

                            <td>

                                <?php if ($rank === 1): ?>

                                    <div class="rank-medal">
                                        🥇
                                    </div>

                                <?php elseif ($rank === 2): ?>

                                    <div class="rank-medal">
                                        🥈
                                    </div>

                                <?php elseif ($rank === 3): ?>

                                    <div class="rank-medal">
                                        🥉
                                    </div>

                                <?php else: ?>

                                    <div class="rank-number">
                                        <?php
                                        echo $rank;
                                        ?>
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- STUDENT -->

                            <td>

                                <div class="leaderboard-student">

                                    <div class="leaderboard-avatar">

                                        <?php
                                        echo htmlspecialchars(
                                            $first_letter
                                        );
                                        ?>

                                    </div>

                                    <div>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $student_name
                                            );
                                            ?>

                                        </strong>


                                        <?php
                                        if ($is_current_user):
                                        ?>

                                            <span class="you-badge">
                                                You
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>


                            <!-- TOTAL SCORE -->

                            <td>

                                <span class="leaderboard-score">

                                    <?php
                                    echo $total_score;
                                    ?>

                                    <small>
                                        marks
                                    </small>

                                </span>

                            </td>


                            <!-- ATTEMPTS -->

                            <td>

                                <span class="attempt-badge">

                                    <?php
                                    echo $total_attempts;
                                    ?>

                                    <?php
                                    echo (
                                        $total_attempts === 1
                                    )
                                        ? " Attempt"
                                        : " Attempts";
                                    ?>

                                </span>

                            </td>

                        </tr>


                    <?php

                        $rank++;

                    endwhile;

                    ?>

                    </tbody>

                </table>

            </div>

        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
        ================================================= -->

        <div class="leaderboard-empty">

            <div class="leaderboard-empty-icon">
                🏆
            </div>

            <h2>
                No Quiz Attempts Yet
            </h2>

            <p>
                Students will appear on the leaderboard
                after completing quizzes.
            </p>

            <a
                href="available_quizzes.php"
                class="btn btn-primary"
            >
                📝 Browse Quizzes
            </a>

        </div>

    <?php endif; ?>


</div>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="footer">

    <p>
        © 2026 Online Quiz System. All Rights Reserved.
    </p>

</footer>


</body>

</html>