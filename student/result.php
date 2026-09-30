<?php
session_start();

require_once "../config/db.php";

/* =========================================================
   1. LOGIN CHECK
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   2. STUDENT ROLE CHECK
========================================================= */

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

/* =========================================================
   3. GET ATTEMPT ID
========================================================= */

$attempt_id = isset($_GET["attempt_id"])
    ? (int) $_GET["attempt_id"]
    : 0;

/* =========================================================
   4. SINGLE RESULT
========================================================= */

if ($attempt_id > 0) {

    $stmt = $conn->prepare(
        "SELECT
            qa.id AS attempt_id,
            qa.score,
            q.id AS quiz_id,
            q.title,
            q.total_marks
         FROM quiz_attempts qa
         INNER JOIN quizzes q
            ON qa.quiz_id = q.id
         WHERE qa.id = ?
           AND qa.user_id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $attempt_id,
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $attempt = $result->fetch_assoc();

    $stmt->close();

    /* -----------------------------------------
       Result security check
    ----------------------------------------- */

    if (!$attempt) {
        http_response_code(404);
        die("Result not found or you are not allowed to view this result.");
    }

    $score = (int) $attempt["score"];
    $total_marks = (int) $attempt["total_marks"];

    /* -----------------------------------------
       Percentage calculation
    ----------------------------------------- */

    if ($total_marks > 0) {
        $percentage = round(
            ($score / $total_marks) * 100,
            2
        );
    } else {
        $percentage = 0;
    }

    /* -----------------------------------------
       Performance message
    ----------------------------------------- */

    if ($percentage >= 80) {

        $performance_title = "Excellent Performance! 🎉";
        $performance_message =
            "Great job! You have performed very well.";

    } elseif ($percentage >= 60) {

        $performance_title = "Good Performance! 👍";
        $performance_message =
            "Nice work! Keep practicing to improve further.";

    } elseif ($percentage >= 40) {

        $performance_title = "Keep Practicing! 💪";
        $performance_message =
            "You are making progress. More practice can improve your score.";

    } else {

        $performance_title = "Keep Learning! 📚";
        $performance_message =
            "Review the topics and try the quiz again.";
    }


/* =========================================================
   5. ALL RESULTS
========================================================= */

} else {

    $stmt = $conn->prepare(
        "SELECT
            qa.id,
            qa.score,
            q.title,
            q.total_marks
         FROM quiz_attempts qa
         INNER JOIN quizzes q
            ON qa.quiz_id = q.id
         WHERE qa.user_id = ?
         ORDER BY qa.id DESC"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Quiz Results - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* =====================================================
           RESULT PAGE
        ===================================================== */

        .result-page {
            padding-bottom: 50px;
        }

        /* =====================================================
           SINGLE RESULT HERO
        ===================================================== */

        .result-hero {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 30px;
            align-items: center;
            background: linear-gradient(
                135deg,
                #172554,
                #312e81
            );
            color: white;
            border-radius: 22px;
            padding: 35px;
            margin-bottom: 25px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
        }

        .result-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 7px solid rgba(255,255,255,0.35);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: auto;
        }

        .result-circle strong {
            font-size: 42px;
            line-height: 1;
        }

        .result-circle span {
            margin-top: 7px;
            font-size: 13px;
            opacity: 0.85;
        }

        .result-hero-content small {
            display: inline-block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
            opacity: 0.75;
            margin-bottom: 8px;
        }

        .result-hero-content h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
        }

        .result-hero-content p {
            margin: 10px 0 0;
            color: rgba(255,255,255,0.78);
        }

        /* =====================================================
           RESULT STATS
        ===================================================== */

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .result-stat {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .result-stat-icon {
            font-size: 25px;
            margin-bottom: 8px;
        }

        .result-stat-label {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .result-stat-value {
            display: block;
            margin-top: 5px;
            font-size: 25px;
            font-weight: 800;
            color: #172554;
        }

        /* =====================================================
           PERFORMANCE CARD
        ===================================================== */

        .performance-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
        }

        .performance-card h2 {
            margin: 0;
            color: #172554;
            font-size: 22px;
        }

        .performance-card p {
            color: #64748b;
            margin: 8px 0 0;
            line-height: 1.6;
        }

        .performance-bar {
            height: 12px;
            background: #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 20px;
        }

        .performance-fill {
            height: 100%;
            width: <?php echo min(100, max(0, $percentage)); ?>%;
            background: linear-gradient(
                90deg,
                #4f46e5,
                #7c3aed
            );
            border-radius: 20px;
        }

        /* =====================================================
           RESULT ACTIONS
        ===================================================== */

        .result-page-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* =====================================================
           RESULT HISTORY
        ===================================================== */

        .history-score {
            font-weight: 800;
            color: #172554;
        }

        .history-percentage {
            font-size: 12px;
            color: #64748b;
            margin-left: 5px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 768px) {

            .result-hero {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 25px;
            }

            .result-hero-content h1 {
                font-size: 25px;
            }

            .result-stats {
                grid-template-columns: 1fr;
            }

            .result-page-actions {
                flex-direction: column;
            }

            .result-page-actions .btn {
                width: 100%;
                text-align: center;
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

        <a href="result.php" class="active">
            Results
        </a>

        <a href="leaderboard.php">
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

<div class="container result-page">

<?php if ($attempt_id > 0): ?>

    <!-- =====================================================
         SINGLE RESULT
    ===================================================== -->

    <div class="page-header">

        <div>

            <span class="welcome-label page-label">
                QUIZ COMPLETED
            </span>

            <h1>
                Quiz Result 🎯
            </h1>

            <p>
                Here is your performance for the selected quiz.
            </p>

        </div>

        <a
            href="result.php"
            class="btn btn-secondary"
        >
            ← All Results
        </a>

    </div>


    <!-- =====================================================
         RESULT HERO
    ===================================================== -->

    <div class="result-hero">

        <div class="result-circle">

            <strong>
                <?php echo $percentage; ?>%
            </strong>

            <span>
                SCORE
            </span>

        </div>


        <div class="result-hero-content">

            <small>
                QUIZ RESULT
            </small>

            <h1>
                <?php
                echo htmlspecialchars(
                    $attempt["title"]
                );
                ?>
            </h1>

            <p>
                <?php
                echo htmlspecialchars(
                    $performance_message
                );
                ?>
            </p>

        </div>

    </div>


    <!-- =====================================================
         RESULT STATS
    ===================================================== -->

    <div class="result-stats">

        <div class="result-stat">

            <div class="result-stat-icon">
                🎯
            </div>

            <span class="result-stat-label">
                Your Score
            </span>

            <span class="result-stat-value">
                <?php echo $score; ?>
            </span>

        </div>


        <div class="result-stat">

            <div class="result-stat-icon">
                📋
            </div>

            <span class="result-stat-label">
                Total Marks
            </span>

            <span class="result-stat-value">
                <?php echo $total_marks; ?>
            </span>

        </div>


        <div class="result-stat">

            <div class="result-stat-icon">
                📈
            </div>

            <span class="result-stat-label">
                Percentage
            </span>

            <span class="result-stat-value">
                <?php echo $percentage; ?>%
            </span>

        </div>

    </div>


    <!-- =====================================================
         PERFORMANCE
    ===================================================== -->

    <div class="performance-card">

        <h2>
            <?php echo $performance_title; ?>
        </h2>

        <p>
            <?php
            echo htmlspecialchars(
                $performance_message
            );
            ?>
        </p>

        <div class="performance-bar">

            <div class="performance-fill"></div>

        </div>

    </div>


    <!-- =====================================================
         ACTIONS
    ===================================================== -->

    <div class="result-page-actions">

        <a
            href="available_quizzes.php"
            class="btn btn-primary"
        >
            📝 Take Another Quiz
        </a>

        <a
            href="result.php"
            class="btn btn-secondary"
        >
            📊 All Results
        </a>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            ← Dashboard
        </a>

    </div>


<?php else: ?>


    <!-- =====================================================
         ALL RESULTS
    ===================================================== -->

    <div class="page-header">

        <div>

            <span class="welcome-label page-label">
                PERFORMANCE
            </span>

            <h1>
                My Quiz Results 📊
            </h1>

            <p>
                View your previous quiz attempts and scores.
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

        <div class="results-card">

            <div class="results-card-header">

                <div>

                    <h2>
                        Quiz History
                    </h2>

                    <p>
                        Your completed quiz attempts
                    </p>

                </div>

                <div class="results-header-icon">
                    📈
                </div>

            </div>


            <div class="student-results-table-wrap">

                <table class="student-results-table">

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Quiz Name
                            </th>

                            <th>
                                Score
                            </th>

                            <th>
                                Percentage
                            </th>

                            <th>
                                Result
                            </th>

                            <th>
                                Details
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $counter = 1;

                    while ($row = $result->fetch_assoc()):

                        $history_score =
                            (int) $row["score"];

                        $history_total =
                            (int) $row["total_marks"];

                        if ($history_total > 0) {

                            $history_percentage =
                                round(
                                    ($history_score / $history_total) * 100,
                                    2
                                );

                        } else {

                            $history_percentage = 0;
                        }

                    ?>

                        <tr>

                            <td>

                                <span class="result-number">

                                    <?php
                                    echo $counter++;
                                    ?>

                                </span>

                            </td>


                            <td>

                                <div class="student-result-quiz">

                                    <div class="student-result-icon">
                                        📝
                                    </div>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["title"]
                                        );
                                        ?>

                                    </strong>

                                </div>

                            </td>


                            <td>

                                <span class="student-score-badge">

                                    <?php
                                    echo $history_score;
                                    ?>

                                    /
                                    <?php
                                    echo $history_total;
                                    ?>

                                </span>

                            </td>


                            <td>

                                <strong class="history-score">

                                    <?php
                                    echo $history_percentage;
                                    ?>%

                                </strong>

                            </td>


                            <td>

                                <span class="completed-badge">
                                    ✓ Completed
                                </span>

                            </td>


                            <td>

                                <a
                                    href="result.php?attempt_id=<?php echo (int) $row["id"]; ?>"
                                    class="view-result-btn"
                                >
                                    View Result →
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
        ================================================= -->

        <div class="results-empty">

            <div class="results-empty-icon">
                📭
            </div>

            <h2>
                No Quiz Results Yet
            </h2>

            <p>
                You haven't attempted any quizzes yet.
                Complete a quiz to see your score here.
            </p>

            <a
                href="available_quizzes.php"
                class="btn btn-primary"
            >
                📝 Browse Quizzes
            </a>

        </div>

    <?php endif; ?>

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