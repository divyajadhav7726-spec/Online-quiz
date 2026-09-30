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

$user_id = (int) $_SESSION["user_id"];

/* ================= FETCH MY QUIZZES ================= */
$sql = "SELECT id, title, description, difficulty,
               duration_minutes, total_marks, visibility, share_token
        FROM quizzes
        WHERE created_by = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Quizzes - Online Quiz</title>

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

        <a href="available_quizzes.php">
            Quizzes
        </a>

        <a href="my_quizzes.php" class="active">
            My Quizzes
        </a>

        <a href="result.php">
            Results
        </a>

        <a href="leaderboard.php">
            Leaderboard
        </a>

        <a href="../logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</nav>


<!-- ================= MAIN CONTENT ================= -->

<div class="container">

    <div class="page-header">

        <div>

            <span class="welcome-label page-label">
                QUIZ MANAGEMENT
            </span>

            <h1>
                My Quizzes 📝
            </h1>

            <p>
                View, edit and manage quizzes created by you.
            </p>

        </div>

        <div class="page-header-actions">

            <a href="dashboard.php" class="btn btn-secondary">
                ← Dashboard
            </a>

            <a href="create_quiz.php" class="btn btn-primary">
                + Create Quiz
            </a>

        </div>

    </div>


    <!-- ================= QUIZ GRID ================= -->

    <div class="my-quiz-grid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($quiz = $result->fetch_assoc()): ?>

                <?php

                /* ================= BUILD SHARE URL ================= */

                $shareUrl = "http://" . $_SERVER["HTTP_HOST"]
                          . "/online-quiz/student/quiz.php?id="
                          . (int) $quiz["id"];

                /* Link-only quiz token */

                if (
                    $quiz["visibility"] === "link" &&
                    !empty($quiz["share_token"])
                ) {

                    $shareUrl .= "&token="
                              . urlencode($quiz["share_token"]);

                }

                ?>

                <!-- ================= QUIZ CARD ================= -->

                <div class="my-quiz-card">

                    <!-- Card Top -->

                    <div class="my-quiz-card-top">

                        <div class="my-quiz-icon">
                            📝
                        </div>

                        <?php if ($quiz["visibility"] === "link"): ?>

                            <span class="visibility-badge link-badge">
                                🔗 Link Only
                            </span>

                        <?php else: ?>

                            <span class="visibility-badge public-badge">
                                🌐 Public
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- Quiz Title -->

                    <h2>
                        <?php
                        echo htmlspecialchars($quiz["title"]);
                        ?>
                    </h2>


                    <!-- Description -->

                    <p class="my-quiz-description">

                        <?php

                        echo htmlspecialchars(
                            $quiz["description"] ?? "No description available."
                        );

                        ?>

                    </p>


                    <!-- Quiz Information -->

                    <div class="my-quiz-meta">

                        <div class="my-quiz-meta-item">

                            <span>
                                🎯 Difficulty
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $quiz["difficulty"] ?? "Not specified"
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="my-quiz-meta-item">

                            <span>
                                ⏱️ Duration
                            </span>

                            <strong>
                                <?php
                                echo (int) $quiz["duration_minutes"];
                                ?>
                                min
                            </strong>

                        </div>


                        <div class="my-quiz-meta-item">

                            <span>
                                🏆 Total Marks
                            </span>

                            <strong>
                                <?php
                                echo (int) $quiz["total_marks"];
                                ?>
                            </strong>

                        </div>


                        <div class="my-quiz-meta-item">

                            <span>
                                👁️ Visibility
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    ucfirst($quiz["visibility"] ?? "public")
                                );
                                ?>
                            </strong>

                        </div>

                    </div>


                    <!-- ================= ACTION BUTTONS ================= -->

                    <div class="my-quiz-actions">

                        <!-- Preview -->

                        <a
                            href="quiz.php?id=<?php echo (int)$quiz["id"]; ?>"
                            class="quiz-action-btn preview-btn"
                        >
                            👁 Preview
                        </a>


                        <!-- Edit -->

                        <a
                            href="edit_quiz.php?id=<?php echo (int)$quiz["id"]; ?>"
                            class="quiz-action-btn edit-btn"
                        >
                            ✏ Edit
                        </a>


                        <!-- Copy Share Link -->

                        <button
                            class="quiz-action-btn share-btn"
                            type="button"
                            onclick="copyQuizLink(this)"
                            data-link="<?php
                                echo htmlspecialchars(
                                    $shareUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                        >
                            🔗 Copy Link
                        </button>


                        <!-- Delete -->

                        <form
                            class="delete-form"
                            action="delete_quiz.php"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this quiz?');"
                        >

                            <input
                                type="hidden"
                                name="quiz_id"
                                value="<?php echo (int)$quiz["id"]; ?>"
                            >

                            <button
                                class="quiz-action-btn delete-btn"
                                type="submit"
                            >
                                🗑 Delete
                            </button>

                        </form>

                    </div>


                    <!-- ================= VISIBILITY NOTE ================= -->

                    <?php if ($quiz["visibility"] === "link"): ?>

                        <div class="quiz-share-note link-note">

                            🔗
                            <span>
                                This is a link-only quiz. Share the copied
                                link with students.
                            </span>

                        </div>

                    <?php else: ?>

                        <div class="quiz-share-note public-note">

                            🌐
                            <span>
                                This quiz is public and appears in
                                Available Quizzes.
                            </span>

                        </div>

                    <?php endif; ?>


                </div>

            <?php endwhile; ?>


        <?php else: ?>

            <!-- ================= EMPTY STATE ================= -->

            <div class="my-quiz-empty">

                <div class="empty-quiz-icon">
                    📭
                </div>

                <h2>
                    No Quizzes Created Yet
                </h2>

                <p>
                    You haven't created any quizzes yet.
                    Create your first quiz and manage it from here.
                </p>

                <a
                    href="create_quiz.php"
                    class="btn btn-primary"
                >
                    + Create Your First Quiz
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


<!-- ================= JAVASCRIPT ================= -->

<script>

function copyQuizLink(button) {

    const link = button.dataset.link;

    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(link)

            .then(function () {

                alert(
                    "Quiz link copied successfully! Share it with students."
                );

            })

            .catch(function () {

                showCopyPrompt(link);

            });

    } else {

        showCopyPrompt(link);

    }

}


function showCopyPrompt(link) {

    prompt(
        "Copy this quiz link:",
        link
    );

}

</script>

</body>
</html>