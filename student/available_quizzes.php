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


/* ================= FETCH PUBLIC QUIZZES ================= */

$sql = "SELECT q.id, q.title, q.description, q.difficulty,
               q.duration_minutes, q.total_marks,
               c.name AS category_name,
               u.full_name AS creator_name
        FROM quizzes q
        LEFT JOIN categories c ON q.category_id = c.id
        LEFT JOIN users u ON q.created_by = u.id
        WHERE q.status = 1
          AND q.visibility = 'public'
        ORDER BY q.id DESC";

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

    <title>Available Quizzes - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="nav-brand">
        🧠 Online Quiz
    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a
            href="available_quizzes.php"
            class="active"
        >
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

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- ================= MAIN CONTENT ================= -->

<div class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <span class="welcome-label page-label">
                QUIZ LIBRARY
            </span>

            <h1>
                Available Quizzes 📝
            </h1>

            <p>
                Explore public quizzes and test your knowledge.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-primary"
        >
            ← Dashboard
        </a>

    </div>



    <!-- QUIZ GRID -->

    <div class="available-quiz-grid">


        <?php if ($result && $result->num_rows > 0): ?>


            <?php while ($quiz = $result->fetch_assoc()): ?>


                <div class="available-quiz-card">


                    <!-- CARD TOP -->

                    <div class="quiz-card-top">

                        <div class="available-quiz-icon">
                            📝
                        </div>


                        <span class="public-badge">
                            🌐 Public
                        </span>

                    </div>



                    <!-- QUIZ TITLE -->

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $quiz["title"]
                        );
                        ?>

                    </h2>



                    <!-- DESCRIPTION -->

                    <p class="quiz-description">

                        <?php

                        echo htmlspecialchars(
                            $quiz["description"] ?? ""
                        );

                        ?>

                    </p>



                    <!-- QUIZ INFORMATION -->

                    <div class="quiz-meta-grid">


                        <div class="quiz-meta-item">

                            <span>
                                📂 Category
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $quiz["category_name"]
                                    ?? "General"
                                );

                                ?>

                            </strong>

                        </div>



                        <div class="quiz-meta-item">

                            <span>
                                🎯 Difficulty
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $quiz["difficulty"]
                                    ?? "Not specified"
                                );

                                ?>

                            </strong>

                        </div>



                        <div class="quiz-meta-item">

                            <span>
                                ⏱️ Duration
                            </span>

                            <strong>

                                <?php
                                echo (int)
                                    $quiz["duration_minutes"];
                                ?>

                                min

                            </strong>

                        </div>



                        <div class="quiz-meta-item">

                            <span>
                                🏆 Total Marks
                            </span>

                            <strong>

                                <?php
                                echo (int)
                                    $quiz["total_marks"];
                                ?>

                            </strong>

                        </div>


                    </div>



                    <!-- CREATOR -->

                    <div class="quiz-creator">

                        <span>
                            Created by
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $quiz["creator_name"]
                                ?? "Admin"
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- START BUTTON -->

                    <a
                        href="quiz.php?id=<?php echo (int)$quiz["id"]; ?>"
                        class="start-quiz-btn"
                    >
                        Start Quiz →
                    </a>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="quiz-empty-state">

                <div class="quiz-empty-icon">
                    📭
                </div>

                <h2>
                    No Public Quizzes Available
                </h2>

                <p>
                    There are no public quizzes available right now.
                    Please check again later.
                </p>

                <a
                    href="dashboard.php"
                    class="btn btn-primary"
                >
                    ← Back to Dashboard
                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<!-- ================= FOOTER ================= -->

<footer class="footer">

    <p>
        © 2026 Online Quiz System. All Rights Reserved.
    </p>

</footer>


</body>

</html>