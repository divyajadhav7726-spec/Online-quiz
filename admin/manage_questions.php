
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

$message = "";
$message_type = "success";

/* ================= ADD QUESTION ================= */

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

    if (
        $quiz_id <= 0 ||
        $question_text === "" ||
        $marks <= 0 ||
        in_array("", $options, true) ||
        $correct_option < 1 ||
        $correct_option > 4
    ) {

        $message = "Please fill all fields correctly.";
        $message_type = "danger";

    } else {

        $conn->begin_transaction();

        try {

            /* Insert Question */

            $stmt = $conn->prepare(
                "INSERT INTO questions
                (quiz_id, question_text, marks)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "isi",
                $quiz_id,
                $question_text,
                $marks
            );

            $stmt->execute();

            $question_id = $conn->insert_id;

            $stmt->close();


            /* Insert Options */

            $stmt = $conn->prepare(
                "INSERT INTO options
                (question_id, option_text, is_correct)
                VALUES (?, ?, ?)"
            );

            foreach ($options as $index => $option_text) {

                $is_correct =
                    (($index + 1) === $correct_option) ? 1 : 0;

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
            $message_type = "success";

        } catch (Exception $e) {

            $conn->rollback();

            $message = "Could not add question. Please check your database tables.";
            $message_type = "danger";
        }
    }
}


/* ================= FETCH QUIZZES ================= */

$quizzes = $conn->query(
    "SELECT id, title
     FROM quizzes
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Questions - Online Quiz</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="container">

        <div class="logo">
            🧠 Online Quiz
        </div>

        <ul class="nav-links">

            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="manage_quizzes.php">Quizzes</a>
            </li>

            <li>
                <a href="manage_questions.php">Questions</a>
            </li>

            <li>
                <a href="view_results.php">Results</a>
            </li>

            <li>
                <a href="manage_users.php">Users</a>
            </li>

            <li>
                <a href="../logout.php">Logout</a>
            </li>

        </ul>

    </div>

</nav>


<!-- ================= MAIN ================= -->

<main class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h1>
                Manage Questions ❓
            </h1>

            <p>
                Create questions and answer options for your quizzes.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            ← Dashboard
        </a>

    </div>


    <!-- ================= MESSAGE ================= -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-<?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ================= TWO COLUMN LAYOUT ================= -->

    <div class="question-layout">


        <!-- ================= LEFT FORM ================= -->

        <div class="card question-form-card">

            <h2>
                ➕ Add Question
            </h2>

            <p class="section-description">
                Add a question, four options and select the correct answer.
            </p>


            <form method="POST">


                <!-- QUIZ -->

                <div class="form-group">

                    <label for="quiz_id">
                        Select Quiz *
                    </label>

                    <select
                        id="quiz_id"
                        name="quiz_id"
                        required
                    >

                        <option value="">
                            Choose a quiz
                        </option>

                        <?php if ($quizzes): ?>

                            <?php while ($quiz = $quizzes->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $quiz['id']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $quiz['title']
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                </div>


                <!-- QUESTION -->

                <div class="form-group">

                    <label for="question_text">
                        Question *
                    </label>

                    <textarea
                        id="question_text"
                        name="question_text"
                        placeholder="Enter your question..."
                        required
                    ></textarea>

                </div>


                <!-- MARKS -->

                <div class="form-group">

                    <label for="marks">
                        Marks *
                    </label>

                    <input
                        type="number"
                        id="marks"
                        name="marks"
                        min="1"
                        value="1"
                        required
                    >

                </div>


                <!-- OPTIONS BOX -->

                <div class="options-box">

                    <h3>
                        Answer Options
                    </h3>

                    <p>
                        Enter four options below.
                    </p>


                    <div class="form-group">

                        <label for="option1">
                            Option 1 *
                        </label>

                        <input
                            type="text"
                            id="option1"
                            name="option1"
                            placeholder="Enter option 1"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="option2">
                            Option 2 *
                        </label>

                        <input
                            type="text"
                            id="option2"
                            name="option2"
                            placeholder="Enter option 2"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="option3">
                            Option 3 *
                        </label>

                        <input
                            type="text"
                            id="option3"
                            name="option3"
                            placeholder="Enter option 3"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="option4">
                            Option 4 *
                        </label>

                        <input
                            type="text"
                            id="option4"
                            name="option4"
                            placeholder="Enter option 4"
                            required
                        >

                    </div>


                    <!-- CORRECT ANSWER -->

                    <div class="form-group">

                        <label for="correct_option">
                            Correct Answer *
                        </label>

                        <select
                            id="correct_option"
                            name="correct_option"
                            required
                        >

                            <option value="">
                                Choose correct option
                            </option>

                            <option value="1">
                                Option 1
                            </option>

                            <option value="2">
                                Option 2
                            </option>

                            <option value="3">
                                Option 3
                            </option>

                            <option value="4">
                                Option 4
                            </option>

                        </select>

                    </div>

                </div>


                <!-- SAVE -->

                <button
                    type="submit"
                    class="btn btn-primary question-save-btn"
                >
                    ✅ Save Question
                </button>

            </form>

        </div>


        <!-- ================= RIGHT SIDE ================= -->

        <div class="question-side">


            <!-- IMAGE CARD -->

            <div class="question-image-card">

    <div class="quiz-illustration">

        <div class="quiz-brain">
            🧠
        </div>

        <div class="quiz-title">
            ONLINE QUIZ
        </div>

        <div class="quiz-subtitle">
            Test Your Knowledge
        </div>

        <div class="quiz-question">
            <span>✓</span>
            Create Questions
        </div>

        <div class="quiz-question">
            <span>✓</span>
            Add Answer Options
        </div>

        <div class="quiz-question">
            <span>✓</span>
            Select Correct Answer
        </div>

        <div class="quiz-button">
            Start Quiz →
        </div>

    </div>

</div>

            <!-- INFO CARD -->

            <div class="card question-info-card">

                <div class="info-icon">
                    🧠
                </div>

                <h3>
                    Build Your Quiz
                </h3>

                <p>
                    Create clear and engaging questions to make your
                    online quiz more useful and interactive.
                </p>


                <div class="info-item">
                    <span>📝</span>
                    <strong>Write Questions</strong>
                </div>

                <div class="info-item">
                    <span>🔤</span>
                    <strong>Add 4 Options</strong>
                </div>

                <div class="info-item">
                    <span>✅</span>
                    <strong>Select Correct Answer</strong>
                </div>

                <div class="info-item">
                    <span>🎯</span>
                    <strong>Test Your Knowledge</strong>
                </div>

            </div>

        </div>

    </div>

</main>


<!-- ================= FOOTER ================= -->

<footer class="footer">

    <div class="container">

        <p>
            © <?php echo date("Y"); ?>
            Online Quiz System. All rights reserved.
        </p>

    </div>

</footer>


</body>
</html>