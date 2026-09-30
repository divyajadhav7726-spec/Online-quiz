
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$user_id = (int)$_SESSION["user_id"];
$message = "";

// Add question
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quiz_id = (int)($_POST["quiz_id"] ?? 0);
    $question_text = trim($_POST["question_text"] ?? "");
    $marks = (int)($_POST["marks"] ?? 1);

    $options = [
        trim($_POST["option1"] ?? ""),
        trim($_POST["option2"] ?? ""),
        trim($_POST["option3"] ?? ""),
        trim($_POST["option4"] ?? "")
    ];

    $correct_option = (int)($_POST["correct_option"] ?? 0);

    // Check that this quiz belongs to the logged-in user
    $check = $conn->prepare(
        "SELECT id FROM quizzes
         WHERE id = ? AND created_by = ?"
    );
    $check->bind_param("ii", $quiz_id, $user_id);
    $check->execute();
    $quiz_result = $check->get_result();
    $owned_quiz = $quiz_result->fetch_assoc();
    $check->close();

    if (
        !$owned_quiz ||
        $question_text === "" ||
        $marks <= 0 ||
        in_array("", $options, true) ||
        $correct_option < 1 ||
        $correct_option > 4
    ) {
        $message = "Please fill all fields correctly and select your own quiz.";
    } else {

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "INSERT INTO questions (quiz_id, question_text, marks)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("isi", $quiz_id, $question_text, $marks);
            $stmt->execute();

            $question_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO options (question_id, option_text, is_correct)
                 VALUES (?, ?, ?)"
            );

            foreach ($options as $index => $option_text) {
                $is_correct = (($index + 1) === $correct_option) ? 1 : 0;

                $stmt->bind_param(
                    "isi",
                    $question_id,
                    $option_text,
                    $is_correct
                );
                $stmt->execute();
            }

            $stmt->close();
            $conn->commit();

            $message = "Question and options added successfully!";

        } catch (Throwable $e) {
            $conn->rollback();
            error_log("Question insert failed: " . $e->getMessage());
            $message = "Could not add question. Please check your database.";
        }
    }
}

// Show only quizzes created by this user
$stmt = $conn->prepare(
    "SELECT id, title FROM quizzes
     WHERE created_by = ?
     ORDER BY id DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quizzes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage My Questions - Online Quiz</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #222;
        }

        header {
            background: #0b3d91;
            color: white;
            padding: 18px 30px;
        }

        header a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        .container {
            max-width: 850px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #00000012;
        }

        h2 {
            color: #0b3d91;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            margin-top: 22px;
            padding: 12px 22px;
            background: #0b3d91;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #082c68;
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            background: #dcfce7;
            color: #166534;
            border-radius: 5px;
        }

        .hint {
            color: #64748b;
            font-size: 13px;
            margin-top: 6px;
        }
    </style>
</head>

<body>

<header>
    <a href="dashboard.php">← Student Dashboard</a>
    <strong>Manage My Questions</strong>
</header>

<div class="container">
    <div class="panel">

        <h2>Add Question & Options</h2>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <?php if ($quizzes->num_rows > 0): ?>

            <form method="POST">

                <label>Select Your Quiz *</label>
                <select name="quiz_id" required>
                    <option value="">Choose your quiz</option>

                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                        <option value="<?php echo (int)$quiz["id"]; ?>">
                            <?php echo htmlspecialchars($quiz["title"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Question *</label>
                <textarea name="question_text" required
                    placeholder="Enter your question"></textarea>

                <label>Marks *</label>
                <input type="number" name="marks" min="1" value="1" required>

                <label>Option 1 *</label>
                <input type="text" name="option1" required>

                <label>Option 2 *</label>
                <input type="text" name="option2" required>

                <label>Option 3 *</label>
                <input type="text" name="option3" required>

                <label>Option 4 *</label>
                <input type="text" name="option4" required>

                <label>Correct Answer *</label>
                <select name="correct_option" required>
                    <option value="">Choose correct option</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                    <option value="4">Option 4</option>
                </select>

                <p class="hint">
                    Select which option is the correct answer.
                </p>

                <button type="submit">Save Question</button>

            </form>

        <?php else: ?>

            <p>You have not created any quizzes yet.</p>
            <p style="margin-top:12px;">
                Please create a quiz first, then add questions.
            </p>
            <p style="margin-top:18px;">
                <a href="create_quiz.php">+ Create New Quiz</a>
            </p>

        <?php endif; ?>

    </div>
</div>

</body>
</html>