<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

require_once "../config/db.php";

$user_id = (int)$_SESSION["user_id"];

// Fetch questions only from quizzes created by this user
$stmt = $conn->prepare(
    "SELECT
        q.id AS question_id,
        q.question_text,
        q.marks,
        z.id AS quiz_id,
        z.title AS quiz_title
     FROM questions q
     INNER JOIN quizzes z ON q.quiz_id = z.id
     WHERE z.created_by = ?
     ORDER BY z.id DESC, q.id ASC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Questions - Online Quiz</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        /* =========================
           MY QUESTIONS PAGE
        ========================= */

        .questions-page {
            padding-bottom: 40px;
        }

        .questions-header {
            margin-bottom: 25px;
        }

        .questions-header h1 {
            margin-bottom: 8px;
        }

        .questions-header p {
            margin: 0;
            color: #64748b;
        }

        .questions-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.07);
        }

        /* Quiz Section */

        .quiz-section {
            margin-bottom: 35px;
        }

        .quiz-section:last-child {
            margin-bottom: 0;
        }

        .quiz-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 15px;
        }

        .quiz-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .quiz-section-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 12px;
            font-size: 20px;
        }

        .quiz-section-title h2 {
            margin: 0;
            color: #172554;
            font-size: 18px;
            word-break: break-word;
        }

        .question-count {
            padding: 7px 12px;
            background: #e0e7ff;
            color: #4338ca;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Question */

        .question-item {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
            transition: 0.2s ease;
        }

        .question-item:hover {
            border-color: #c7d2fe;
            box-shadow: 0 5px 18px rgba(79, 70, 229, 0.07);
        }

        .question-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 30px;
            padding: 0 10px;
            background: #172554;
            color: white;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .question-text {
            flex: 1;
            color: #1e293b;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.6;
        }

        .marks-badge {
            padding: 7px 11px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Options */

        .options-list {
            display: grid;
            gap: 9px;
        }

        .question-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #475569;
            font-size: 14px;
        }

        .option-letter {
            width: 27px;
            height: 27px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            background: #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .correct-option {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .correct-option .option-letter {
            background: #16a34a;
            color: white;
        }

        .correct-label {
            margin-left: auto;
            padding: 5px 9px;
            background: #dcfce7;
            color: #15803d;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* =========================
           ACTION BUTTONS
        ========================= */

        .question-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .question-edit-btn,
        .question-delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .question-edit-btn {
            background: #eef2ff;
            color: #4338ca;
        }

        .question-edit-btn:hover {
            background: #e0e7ff;
        }

        .question-delete-btn {
            border: none;
            background: #fef2f2;
            color: #dc2626;
        }

        .question-delete-btn:hover {
            background: #fee2e2;
        }

        .question-actions form {
            margin: 0;
        }

        /* =========================
           EMPTY STATE
        ========================= */

        .questions-empty {
            text-align: center;
            padding: 65px 20px;
        }

        .questions-empty-icon {
            width: 75px;
            height: 75px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            border-radius: 22px;
            font-size: 34px;
        }

        .questions-empty h2 {
            margin: 0 0 8px;
            color: #172554;
        }

        .questions-empty p {
            max-width: 500px;
            margin: 0 auto 22px;
            color: #64748b;
            line-height: 1.6;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .questions-card {
                padding: 18px;
            }

            .quiz-section-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .question-top {
                flex-direction: column;
            }

            .marks-badge {
                align-self: flex-start;
            }

        }

        @media (max-width: 480px) {

            .questions-card {
                padding: 14px;
                border-radius: 14px;
            }

            .question-item {
                padding: 15px;
            }

            .quiz-section-title h2 {
                font-size: 16px;
            }

            .question-text {
                font-size: 15px;
            }

            .question-option {
                align-items: flex-start;
            }

            .correct-label {
                font-size: 10px;
            }

            .question-actions {
                flex-direction: column;
            }

            .question-edit-btn,
            .question-delete-btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

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

        <a href="leaderboard.php">
            Leaderboard
        </a>

        <a href="../logout.php" class="logout-link">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="container questions-page">

    <div class="page-header questions-header">

        <div>

            <span class="page-label">
                Question Library
            </span>

            <h1>
                My Questions
            </h1>

            <p>
                View and manage all questions from your quizzes.
            </p>

        </div>

        <div class="page-header-actions">

            <a href="manage_questions.php"
               class="btn btn-primary">
                + Add Questions
            </a>

        </div>

    </div>


    <div class="questions-card">

        <?php if ($questions->num_rows > 0): ?>

            <?php

            $current_quiz = null;
            $number = 0;
            $quiz_open = false;

            while ($question = $questions->fetch_assoc()):

                /*
                 * New quiz section
                 */

                if ($current_quiz !== $question["quiz_id"]):

                    if ($quiz_open) {
                        echo '</div></div>';
                    }

                    $current_quiz = $question["quiz_id"];
                    $number = 0;
                    $quiz_open = true;

            ?>

                    <div class="quiz-section">

                        <div class="quiz-section-header">

                            <div class="quiz-section-title">

                                <div class="quiz-section-icon">
                                    📝
                                </div>

                                <h2>
                                    <?php
                                    echo htmlspecialchars(
                                        $question["quiz_title"]
                                    );
                                    ?>
                                </h2>

                            </div>

                            <div class="question-count">
                                Quiz Questions
                            </div>

                        </div>

                        <div class="questions-list">

            <?php
                endif;

                $number++;

                /*
                 * Fetch options
                 */

                $options_stmt = $conn->prepare(
                    "SELECT option_text, is_correct
                     FROM options
                     WHERE question_id = ?
                     ORDER BY id ASC"
                );

                $options_stmt->bind_param(
                    "i",
                    $question["question_id"]
                );

                $options_stmt->execute();

                $options = $options_stmt->get_result();

            ?>

                <!-- =========================
                     QUESTION
                ========================= -->

                <div class="question-item">


                    <div class="question-top">

                        <div class="question-number">
                            Q<?php echo $number; ?>
                        </div>

                        <div class="question-text">

                            <?php
                            echo htmlspecialchars(
                                $question["question_text"]
                            );
                            ?>

                        </div>

                        <div class="marks-badge">

                            <?php
                            echo (int)$question["marks"];
                            ?>

                            Marks

                        </div>

                    </div>


                    <!-- OPTIONS -->

                    <div class="options-list">

                        <?php
                        $option_letter = 'A';
                        ?>

                        <?php while ($option = $options->fetch_assoc()): ?>

                            <div class="question-option
                                <?php
                                echo (
                                    (int)$option["is_correct"] === 1
                                )
                                ? "correct-option"
                                : "";
                                ?>"
                            >

                                <span class="option-letter">

                                    <?php
                                    echo $option_letter;
                                    ?>

                                </span>

                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $option["option_text"]
                                    );
                                    ?>

                                </span>


                                <?php if (
                                    (int)$option["is_correct"] === 1
                                ): ?>

                                    <span class="correct-label">
                                        ✓ Correct
                                    </span>

                                <?php endif; ?>

                            </div>

                            <?php
                            $option_letter++;
                            ?>

                        <?php endwhile; ?>

                    </div>


                    <!-- =========================
                         EDIT + DELETE
                    ========================= -->

                    <div class="question-actions">

                        <a
                            href="edit_question.php?id=<?php echo (int)$question["question_id"]; ?>"
                            class="question-edit-btn"
                        >
                            ✏️ Edit
                        </a>


                        <form
                            method="POST"
                            action="delete_question.php"
                            onsubmit="return confirm('Are you sure you want to delete this question?');"
                        >

                            <input
                                type="hidden"
                                name="question_id"
                                value="<?php echo (int)$question["question_id"]; ?>"
                            >

                            <button
                                type="submit"
                                class="question-delete-btn"
                            >
                                🗑️ Delete
                            </button>

                        </form>

                    </div>


                </div>


            <?php

                $options_stmt->close();

            endwhile;


            if ($quiz_open) {
                echo '</div></div>';
            }

            ?>

        <?php else: ?>


            <!-- =========================
                 EMPTY STATE
            ========================= -->

            <div class="questions-empty">

                <div class="questions-empty-icon">
                    📝
                </div>

                <h2>
                    No Questions Yet
                </h2>

                <p>
                    You have not added any questions to your quizzes yet.
                    Create questions to make your quizzes ready for students.
                </p>

                <a
                    href="manage_questions.php"
                    class="btn btn-primary"
                >
                    + Add Questions
                </a>

            </div>


        <?php endif; ?>

    </div>

</main>


<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <p>
        © <?php echo date("Y"); ?> Online Quiz.
        All rights reserved.
    </p>

</footer>


</body>
</html>