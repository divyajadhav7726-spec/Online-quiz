<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../student/dashboard.php");
    exit();
}

require_once "../config/db.php";

$sql = "SELECT
            qa.id AS attempt_id,
            u.full_name,
            u.email,
            q.title AS quiz_title,
            qa.score,
            q.total_marks,
            qa.started_at
        FROM quiz_attempts qa
        INNER JOIN users u ON qa.user_id = u.id
        INNER JOIN quizzes q ON qa.quiz_id = q.id
        ORDER BY qa.started_at DESC";

$results = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Results - Online Quiz</title>

    <link rel="stylesheet" href="../css/style.css">
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

        <a href="manage_quizzes.php">
            Quizzes
        </a>

        <a href="manage_questions.php">
            Questions
        </a>

        <a href="view_results.php" class="active">
            Results
        </a>

        <a href="manage_users.php">
            Users
        </a>

        <a href="../logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</nav>


<!-- ================= MAIN CONTENT ================= -->

<div class="container">

    <!-- Page Header -->

    <div class="page-header">

        <div>
            <h1>Student Results</h1>

            <p>
                View all quiz attempts and student performance.
            </p>
        </div>

    </div>


    <!-- Results Panel -->

    <div class="card">

        <div class="card-header">

            <div>
                <h2>📊 All Student Quiz Results</h2>

                <p>
                    Monitor student scores, quiz attempts and performance.
                </p>
            </div>

        </div>


        <!-- Table -->

        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>Attempt ID</th>

                        <th>Student</th>

                        <th>Email</th>

                        <th>Quiz</th>

                        <th>Score</th>

                        <th>Total Marks</th>

                        <th>Date & Time</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($results && $results->num_rows > 0): ?>

                    <?php while ($row = $results->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <span class="id-badge">
                                    #<?php echo htmlspecialchars($row['attempt_id']); ?>
                                </span>
                            </td>


                            <td>

                                <div class="student-info">

                                    <div class="student-avatar">
                                        <?php
                                        echo strtoupper(
                                            substr($row['full_name'], 0, 1)
                                        );
                                        ?>
                                    </div>

                                    <div>
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $row['full_name']
                                            );
                                            ?>
                                        </strong>
                                    </div>

                                </div>

                            </td>


                            <td>
                                <span class="email-text">
                                    <?php
                                    echo htmlspecialchars(
                                        $row['email']
                                    );
                                    ?>
                                </span>
                            </td>


                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['quiz_title']
                                    );
                                    ?>
                                </strong>
                            </td>


                            <td>

                                <span class="score-badge">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['score']
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['total_marks']
                                );
                                ?>

                            </td>


                            <td>

                                <span class="date-text">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['started_at']
                                    );
                                    ?>

                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>

                        <td colspan="7">

                            <div class="empty-state">

                                <div class="empty-icon">
                                    📊
                                </div>

                                <h3>
                                    No Student Results
                                </h3>

                                <p>
                                    Student quiz results will appear here
                                    after they complete a quiz.
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

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