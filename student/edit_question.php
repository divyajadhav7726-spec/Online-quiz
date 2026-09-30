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
$question_id = (int)($_GET["id"] ?? $_POST["question_id"] ?? 0);

if ($question_id <= 0) {
    exit("Invalid question ID.");
}

/* =========================
   FETCH QUESTION
========================= */

$stmt = $conn->prepare(
    "SELECT q.id, q.quiz_id, q.question_text, q.marks,
            z.title AS quiz_title
     FROM questions q
     INNER JOIN quizzes z ON z.id = q.quiz_id
     WHERE q.id = ? AND z.created_by = ?"
);

$stmt->bind_param("ii", $question_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$question = $result->fetch_assoc();

$stmt->close();

if (!$question) {
    exit("Question not found or you are not allowed to edit it.");
}

$message = "";
$message_type = "";

/* =========================
   FETCH OPTIONS
========================= */

$stmt = $conn->prepare(
    "SELECT id, option_text, is_correct
     FROM options
     WHERE question_id = ?
     ORDER BY id ASC"
);

$stmt->bind_param("i", $question_id);
$stmt->execute();

$options_result = $stmt->get_result();

$options = [];

while ($row = $options_result->fetch_assoc()) {
    $options[] = $row;
}

$stmt->close();

if (count($options) !== 4) {
    exit("This question does not have exactly four options.");
}

/* =========================
   SAVE CHANGES
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $question_text = trim($_POST["question_text"] ?? "");
    $marks = (int)($_POST["marks"] ?? 1);

    $option_texts = [
        trim($_POST["option1"] ?? ""),
        trim($_POST["option2"] ?? ""),
        trim($_POST["option3"] ?? ""),
        trim($_POST["option4"] ?? "")
    ];

    $correct_option = (int)($_POST["correct_option"] ?? 0);

    if (
        $question_text === "" ||
        $marks <= 0 ||
        in_array("", $option_texts, true) ||
        $correct_option < 1 ||
        $correct_option > 4
    ) {

        $message = "Please fill all fields correctly.";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            /* Update question */

            $stmt = $conn->prepare(
                "UPDATE questions
                 SET question_text = ?, marks = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "sii",
                $question_text,
                $marks,
                $question_id
            );

            $stmt->execute();
            $stmt->close();

            /* Update options */

            $stmt = $conn->prepare(
                "UPDATE options
                 SET option_text = ?, is_correct = ?
                 WHERE id = ? AND question_id = ?"
            );

            foreach ($options as $index => $option) {

                $text = $option_texts[$index];

                $is_correct =
                    (($index + 1) === $correct_option) ? 1 : 0;

                $option_id = (int)$option["id"];

                $stmt->bind_param(
                    "siii",
                    $text,
                    $is_correct,
                    $option_id,
                    $question_id
                );

                $stmt->execute();
            }

            $stmt->close();

            $conn->commit();

            $message = "Question updated successfully!";
            $message_type = "success";

            /* Update displayed values */

            $question["question_text"] = $question_text;
            $question["marks"] = $marks;

            foreach ($options as $index => &$option) {

                $option["option_text"] = $option_texts[$index];

                $option["is_correct"] =
                    (($index + 1) === $correct_option) ? 1 : 0;
            }

            unset($option);

        } catch (Throwable $e) {

            $conn->rollback();

            error_log(
                "Question update failed: " .
                $e->getMessage()
            );

            $message = "Could not update question. Please try again.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Question - Online Quiz</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        /* =========================
           EDIT QUESTION PAGE
        ========================= */

        .edit-page {
            padding-bottom: 50px;
        }

        .edit-header {
            margin-bottom: 25px;
        }

        .edit-header h1 {
            margin-bottom: 7px;
        }

        .edit-header p {
            margin: 0;
            color: #64748b;
        }

        /* Layout */

        .edit-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 25px;
            align-items: start;
        }

        .edit-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.07);
        }

        /* Quiz info */

        .quiz-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 12px;
        }

        .quiz-info-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4f46e5;
            color: white;
            border-radius: 10px;
            font-size: 19px;
        }

        .quiz-info-text span {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .quiz-info-text strong {
            color: #172554;
            font-size: 15px;
        }

        /* Form */

        .form-section-title {
            margin: 0 0 18px;
            color: #172554;
            font-size: 18px;
        }

        .edit-form-group {
            margin-bottom: 20px;
        }

        .edit-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .edit-form-group textarea,
        .edit-form-group input,
        .edit-form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            color: #1e293b;
            font-size: 14px;
            outline: none;
            transition: 0.2s ease;
        }

        .edit-form-group textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        .edit-form-group textarea:focus,
        .edit-form-group input:focus,
        .edit-form-group select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        /* Options */

        .options-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 25px 0 15px;
        }

        .options-heading h3 {
            margin: 0;
            color: #172554;
            font-size: 17px;
        }

        .option-input {
            position: relative;
            margin-bottom: 13px;
        }

        .option-number {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 27px;
            height: 27px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            z-index: 1;
        }

        .option-input input {
            padding-left: 50px;
        }

        /* Message */

        .edit-message {
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .edit-message.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .edit-message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        /* Buttons */

        .edit-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .save-question-btn {
            border: none;
            cursor: pointer;
            padding: 12px 22px;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .save-question-btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .cancel-question-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .cancel-question-btn:hover {
            background: #e2e8f0;
        }

        /* Right Preview */

        .edit-preview-card {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.07);
        }

        .preview-header {
            padding: 20px;
            background: linear-gradient(135deg, #172554, #4f46e5);
            color: white;
        }

        .preview-header h2 {
            margin: 0 0 5px;
            color: white;
            font-size: 18px;
        }

        .preview-header p {
            margin: 0;
            color: #dbeafe;
            font-size: 13px;
        }

        .preview-body {
            padding: 22px;
        }

        .preview-question-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            background: #eef2ff;
            border-radius: 17px;
            font-size: 28px;
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin-top: 9px;
            background: #f8fafc;
            border-radius: 9px;
            color: #475569;
            font-size: 13px;
        }

        .preview-item span {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .preview-tip {
            margin-top: 20px;
            padding: 13px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            color: #92400e;
            font-size: 12px;
            line-height: 1.5;
        }

        /* Responsive */

        @media (max-width: 850px) {

            .edit-layout {
                grid-template-columns: 1fr;
            }

            .edit-preview-card {
                order: 2;
            }
        }

        @media (max-width: 600px) {

            .edit-card {
                padding: 18px;
                border-radius: 14px;
            }

            .edit-actions {
                flex-direction: column;
            }

            .save-question-btn,
            .cancel-question-btn {
                width: 100%;
                text-align: center;
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
     MAIN
========================= -->

<main class="container edit-page">

    <div class="page-header edit-header">

        <div>

            <span class="page-label">
                Question Management
            </span>

            <h1>
                Edit Question
            </h1>

            <p>
                Update your question, options and correct answer.
            </p>

        </div>

    </div>


    <div class="edit-layout">


        <!-- =========================
             EDIT FORM
        ========================= -->

        <div class="edit-card">

            <div class="quiz-info">

                <div class="quiz-info-icon">
                    📝
                </div>

                <div class="quiz-info-text">

                    <span>
                        Quiz
                    </span>

                    <strong>
                        <?php echo htmlspecialchars($question["quiz_title"]); ?>
                    </strong>

                </div>

            </div>


            <?php if ($message !== ""): ?>

                <div class="edit-message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <h2 class="form-section-title">
                Question Details
            </h2>


            <form method="POST">

                <input
                    type="hidden"
                    name="question_id"
                    value="<?php echo (int)$question["id"]; ?>"
                >


                <!-- Question -->

                <div class="edit-form-group">

                    <label>
                        Question *
                    </label>

                    <textarea
                        name="question_text"
                        required
                        placeholder="Enter your question..."
                    ><?php echo htmlspecialchars($question["question_text"]); ?></textarea>

                </div>


                <!-- Marks -->

                <div class="edit-form-group">

                    <label>
                        Marks *
                    </label>

                    <input
                        type="number"
                        name="marks"
                        min="1"
                        required
                        value="<?php echo (int)$question["marks"]; ?>"
                    >

                </div>


                <!-- Options -->

                <div class="options-heading">

                    <h3>
                        Answer Options
                    </h3>

                    <span class="question-count">
                        4 Options
                    </span>

                </div>


                <?php foreach ($options as $index => $option): ?>

                    <div class="option-input">

                        <span class="option-number">
                            <?php echo chr(65 + $index); ?>
                        </span>

                        <input
                            type="text"
                            name="option<?php echo $index + 1; ?>"
                            required
                            value="<?php echo htmlspecialchars($option["option_text"]); ?>"
                            placeholder="Enter option <?php echo $index + 1; ?>"
                        >

                    </div>

                <?php endforeach; ?>


                <!-- Correct Answer -->

                <div class="edit-form-group">

                    <label>
                        Correct Answer *
                    </label>

                    <select
                        name="correct_option"
                        required
                    >

                        <option value="">
                            Choose correct option
                        </option>

                        <?php foreach ($options as $index => $option): ?>

                            <option
                                value="<?php echo $index + 1; ?>"
                                <?php
                                echo ((int)$option["is_correct"] === 1)
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Option <?php echo chr(65 + $index); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Actions -->

                <div class="edit-actions">

                    <button
                        type="submit"
                        class="save-question-btn"
                    >
                        ✓ Save Changes
                    </button>

                    <a
                        href="my_questions.php"
                        class="cancel-question-btn"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>


        <!-- =========================
             PREVIEW
        ========================= -->

        <div class="edit-preview-card">

            <div class="preview-header">

                <h2>
                    Question Preview
                </h2>

                <p>
                    How your question is structured
                </p>

            </div>

            <div class="preview-body">

                <div class="preview-question-icon">
                    🧠
                </div>

                <strong>
                    Multiple Choice Question
                </strong>

                <div class="preview-item">
                    <span>A</span>
                    Answer Option
                </div>

                <div class="preview-item">
                    <span>B</span>
                    Answer Option
                </div>

                <div class="preview-item">
                    <span>C</span>
                    Answer Option
                </div>

                <div class="preview-item">
                    <span>D</span>
                    Answer Option
                </div>

                <div class="preview-tip">
                    💡 Select exactly one correct answer before saving.
                </div>

            </div>

        </div>

    </div>

</main>


<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <p>
        © <?php echo date("Y"); ?> Online Quiz. All rights reserved.
    </p>

</footer>

</body>

</html>