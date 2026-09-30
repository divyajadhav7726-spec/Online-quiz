<?php
session_start();

require_once "../config/db.php";

/* =========================
   LOGIN + ROLE CHECK
========================= */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

/* =========================
   QUIZ ID
========================= */
$quiz_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$quiz_id || $quiz_id <= 0) {
    http_response_code(400);
    die("Invalid quiz ID.");
}

/* =========================
   QUIZ DETAILS
========================= */
$stmt = $conn->prepare(
    "SELECT id, title, description, duration_minutes,
            total_marks, visibility, share_token
     FROM quizzes
     WHERE id = ? AND status = 1"
);

$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$quiz_result = $stmt->get_result();
$quiz = $quiz_result->fetch_assoc();
$stmt->close();

if (!$quiz) {
    http_response_code(404);
    die("Quiz not found or unavailable.");
}

/* =========================
   LINK QUIZ SECURITY
========================= */
if ($quiz["visibility"] === "link") {

    $provided_token = $_GET["token"] ?? "";

    if (
        !is_string($provided_token) ||
        empty($quiz["share_token"]) ||
        !hash_equals($quiz["share_token"], $provided_token)
    ) {
        http_response_code(403);
        die("This quiz requires a valid share link.");
    }
}

/* =========================
   QUESTIONS
========================= */
$questions = [];

$stmt = $conn->prepare(
    "SELECT id, question_text, marks
     FROM questions
     WHERE quiz_id = ?
     ORDER BY id ASC"
);

$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$stmt->close();

/* =========================
   OPTIONS
========================= */
$options_by_question = [];

foreach ($questions as $question) {

    $question_id = (int)$question["id"];

    $stmt = $conn->prepare(
        "SELECT id, option_text
         FROM options
         WHERE question_id = ?
         ORDER BY id ASC"
    );

    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    $options_result = $stmt->get_result();

    $options_by_question[$question_id] = [];

    while ($option = $options_result->fetch_assoc()) {
        $options_by_question[$question_id][] = $option;
    }

    $stmt->close();
}

$duration_minutes = max(
    1,
    (int)$quiz["duration_minutes"]
);

$total_questions = count($questions);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($quiz["title"]); ?> - Online Quiz
</title>

<link rel="stylesheet" href="../css/style.css">

<style>

/* =====================================================
   PREMIUM QUIZ PAGE
===================================================== */

body {
    background:
        radial-gradient(circle at top left, #eef2ff 0, transparent 30%),
        #f8fafc;
}

/* Main */

.quiz-wrapper {
    max-width: 1050px;
    margin: 0 auto;
    padding: 35px 20px 60px;
}

/* =====================================================
   HERO
===================================================== */

.quiz-hero {
    position: relative;
    overflow: hidden;

    background:
        linear-gradient(135deg, #111827, #312e81);

    border-radius: 24px;

    padding: 38px;

    color: white;

    margin-bottom: 24px;

    box-shadow:
        0 20px 50px rgba(15, 23, 42, .16);
}

.quiz-hero::before {
    content: "";

    position: absolute;

    width: 240px;
    height: 240px;

    border-radius: 50%;

    background: rgba(99,102,241,.18);

    top: -100px;
    right: -60px;
}

.quiz-hero::after {
    content: "";

    position: absolute;

    width: 150px;
    height: 150px;

    border-radius: 50%;

    background: rgba(255,255,255,.05);

    bottom: -80px;
    left: 35%;
}

.hero-content {
    position: relative;
    z-index: 2;

    max-width: 760px;
}

.quiz-badge {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    background: rgba(255,255,255,.12);

    border: 1px solid rgba(255,255,255,.16);

    padding: 7px 13px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: 800;

    letter-spacing: .7px;

    text-transform: uppercase;

    margin-bottom: 16px;
}

.quiz-hero h1 {
    margin: 0;

    font-size: 34px;

    line-height: 1.2;

    font-weight: 800;

    letter-spacing: -.6px;
}

.quiz-hero-description {
    margin: 13px 0 0;

    color: #cbd5e1;

    line-height: 1.7;

    font-size: 15px;
}

/* Hero stats */

.hero-stats {
    position: relative;
    z-index: 2;

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 28px;
}

.hero-stat {
    display: flex;

    align-items: center;

    gap: 9px;

    background: rgba(255,255,255,.09);

    border: 1px solid rgba(255,255,255,.10);

    padding: 10px 14px;

    border-radius: 11px;

    font-size: 13px;

    color: #e2e8f0;
}

.hero-stat strong {
    color: white;
}

/* =====================================================
   STICKY CONTROL BAR
===================================================== */

.quiz-control {
    position: sticky;

    top: 12px;

    z-index: 50;

    display: flex;

    align-items: center;

    gap: 20px;

    background: rgba(255,255,255,.96);

    backdrop-filter: blur(14px);

    border: 1px solid #e2e8f0;

    border-radius: 17px;

    padding: 13px 15px;

    margin-bottom: 22px;

    box-shadow:
        0 10px 30px rgba(15,23,42,.08);
}

/* Progress */

.progress-section {
    flex: 1;
}

.progress-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 8px;
}

.progress-title {
    font-size: 12px;

    color: #475569;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.progress-count {
    font-size: 13px;

    color: #4f46e5;

    font-weight: 800;
}

.progress-line {
    height: 7px;

    background: #e2e8f0;

    border-radius: 20px;

    overflow: hidden;
}

.progress-value {
    height: 100%;

    width: 0%;

    background:
        linear-gradient(
            90deg,
            #4f46e5,
            #6366f1
        );

    border-radius: 20px;

    transition: width .3s ease;
}

/* Timer */

.timer-container {
    min-width: 135px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 11px 15px;

    border-radius: 12px;

    background: #eef2ff;

    color: #4338ca;

    font-weight: 900;

    font-size: 17px;
}

.timer-container.warning {
    background: #fff1f2;

    color: #dc2626;

    animation: timerAlert 1s infinite;
}

@keyframes timerAlert {

    50% {
        box-shadow: 0 0 0 5px rgba(220,38,38,.08);
    }

}

/* =====================================================
   QUESTION CARD
===================================================== */

.question-card {
    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 20px;

    padding: 28px;

    margin-bottom: 18px;

    box-shadow:
        0 5px 20px rgba(15,23,42,.045);

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

.question-card:hover {
    border-color: #c7d2fe;

    box-shadow:
        0 9px 28px rgba(15,23,42,.07);
}

/* Question header */

.question-heading {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;
}

.question-number {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 42px;

    height: 32px;

    padding: 0 11px;

    border-radius: 9px;

    background: #eef2ff;

    color: #4338ca;

    font-size: 12px;

    font-weight: 900;

    letter-spacing: .3px;
}

.question-mark {
    font-size: 12px;

    font-weight: 700;

    color: #64748b;

    background: #f8fafc;

    padding: 7px 11px;

    border-radius: 8px;
}

.question-text {
    color: #172554;

    font-size: 19px;

    font-weight: 750;

    line-height: 1.6;

    margin-bottom: 20px;
}

/* =====================================================
   OPTIONS
===================================================== */

.options {
    display: grid;

    gap: 11px;
}

.option {
    position: relative;

    display: flex;

    align-items: center;

    gap: 13px;

    min-height: 58px;

    padding: 10px 15px;

    border: 1px solid #dbe2ea;

    border-radius: 13px;

    background: #fafafa;

    cursor: pointer;

    transition:
        all .18s ease;
}

.option:hover {
    border-color: #818cf8;

    background: #f5f3ff;

    transform: translateY(-1px);
}

.option input {
    position: absolute;

    opacity: 0;

    pointer-events: none;
}

.option-letter {
    flex-shrink: 0;

    width: 34px;
    height: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    background: white;

    border: 1px solid #cbd5e1;

    color: #475569;

    font-size: 13px;

    font-weight: 900;

    transition: .18s ease;
}

.option-text {
    color: #334155;

    font-size: 14px;

    font-weight: 600;

    line-height: 1.5;
}

/* Selected */

.option.selected {
    border-color: #4f46e5;

    background: #f0f0ff;

    box-shadow:
        0 0 0 2px rgba(79,70,229,.08);
}

.option.selected .option-letter {
    background: #4f46e5;

    border-color: #4f46e5;

    color: white;
}

.option.selected .option-text {
    color: #312e81;

    font-weight: 750;
}

/* =====================================================
   EMPTY MESSAGE
===================================================== */

.quiz-message {
    padding: 20px;

    background: #fff7ed;

    border: 1px solid #fed7aa;

    border-radius: 14px;

    color: #9a3412;

    font-size: 14px;

    line-height: 1.6;
}

/* =====================================================
   SUBMIT
===================================================== */

.submit-area {
    display: flex;

    justify-content: center;

    padding: 20px 0 10px;
}

.submit-button {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 9px;

    border: none;

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #4338ca
        );

    color: white;

    padding: 15px 34px;

    min-width: 210px;

    border-radius: 12px;

    font-size: 15px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 25px rgba(79,70,229,.25);

    transition: .2s ease;
}

.submit-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 14px 30px rgba(79,70,229,.32);
}

.submit-button:disabled {
    opacity: .65;

    cursor: not-allowed;

    transform: none;
}

/* Back */

.back-dashboard {
    text-align: center;

    margin-top: 18px;
}

.back-dashboard a {
    color: #4f46e5;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;
}

.back-dashboard a:hover {
    text-decoration: underline;
}

/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    .quiz-wrapper {
        padding: 22px 12px 45px;
    }

    .quiz-hero {
        padding: 27px 22px;

        border-radius: 19px;
    }

    .quiz-hero h1 {
        font-size: 27px;
    }

    .hero-stats {
        display: grid;

        grid-template-columns: 1fr 1fr;
    }

    .quiz-control {
        flex-direction: column;

        align-items: stretch;

        gap: 12px;

        top: 5px;
    }

    .timer-container {
        width: 100%;
    }

    .question-card {
        padding: 21px 17px;

        border-radius: 17px;
    }

    .question-text {
        font-size: 17px;
    }

    .submit-button {
        width: 100%;
    }
}

@media (max-width: 480px) {

    .hero-stats {
        grid-template-columns: 1fr;
    }

    .quiz-hero h1 {
        font-size: 24px;
    }

    .question-heading {
        align-items: flex-start;
    }

    .option {
        padding: 10px 12px;
    }

}

</style>

</head>

<body>

<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="logo">
            🧠 Online Quiz
        </a>

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

            <a href="../logout.php" class="logout-btn">
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =====================================================
     PAGE
===================================================== -->

<main class="quiz-wrapper">


    <!-- =================================================
         HERO
    ================================================== -->

    <section class="quiz-hero">

        <div class="hero-content">

            <div class="quiz-badge">
                📝 Quiz in Progress
            </div>

            <h1>
                <?php
                echo htmlspecialchars($quiz["title"]);
                ?>
            </h1>

            <p class="quiz-hero-description">

                <?php if (!empty($quiz["description"])): ?>

                    <?php
                    echo htmlspecialchars(
                        $quiz["description"]
                    );
                    ?>

                <?php else: ?>

                    Answer each question carefully
                    and submit your quiz before the timer ends.

                <?php endif; ?>

            </p>

        </div>


        <div class="hero-stats">

            <div class="hero-stat">
                📋
                <span>
                    Questions
                    <strong>
                        <?php echo $total_questions; ?>
                    </strong>
                </span>
            </div>

            <div class="hero-stat">
                ⭐
                <span>
                    Marks
                    <strong>
                        <?php
                        echo (int)$quiz["total_marks"];
                        ?>
                    </strong>
                </span>
            </div>

            <div class="hero-stat">
                ⏱️
                <span>
                    Time
                    <strong>
                        <?php echo $duration_minutes; ?> min
                    </strong>
                </span>
            </div>

        </div>

    </section>


    <?php if ($total_questions === 0): ?>

        <div class="quiz-message">

            No questions found for this quiz yet.

        </div>

    <?php else: ?>


    <!-- =================================================
         FORM
    ================================================== -->

    <form
        id="quizForm"
        action="submit_quiz.php"
        method="POST"
    >

        <input
            type="hidden"
            name="quiz_id"
            value="<?php echo (int)$quiz_id; ?>"
        >


        <?php if ($quiz["visibility"] === "link"): ?>

            <input
                type="hidden"
                name="share_token"
                value="<?php
                echo htmlspecialchars(
                    $quiz["share_token"]
                );
                ?>"
            >

        <?php endif; ?>


        <!-- =============================================
             CONTROL BAR
        ============================================== -->

        <div class="quiz-control">

            <div class="progress-section">

                <div class="progress-header">

                    <span class="progress-title">
                        Your Progress
                    </span>

                    <span
                        class="progress-count"
                        id="progressText"
                    >
                        0 / <?php echo $total_questions; ?>
                    </span>

                </div>

                <div class="progress-line">

                    <div
                        class="progress-value"
                        id="progressFill"
                    ></div>

                </div>

            </div>


            <div
                class="timer-container"
                id="timer"
            >

                ⏱️

                <span id="timerText">
                    <?php
                    echo sprintf(
                        "%02d:00",
                        $duration_minutes
                    );
                    ?>
                </span>

            </div>

        </div>


        <!-- =============================================
             QUESTIONS
        ============================================== -->

        <?php foreach ($questions as $index => $question): ?>

            <?php

            $question_id =
                (int)$question["id"];

            $question_options =
                $options_by_question[$question_id]
                ?? [];

            ?>

            <section
                class="question-card"
            >

                <div class="question-heading">

                    <span class="question-number">

                        QUESTION
                        <?php echo $index + 1; ?>

                    </span>

                    <span class="question-mark">

                        <?php
                        echo (int)$question["marks"];
                        ?>

                        mark<?php
                        echo (
                            (int)$question["marks"] != 1
                            ? "s"
                            : ""
                        );
                        ?>

                    </span>

                </div>


                <div class="question-text">

                    <?php
                    echo htmlspecialchars(
                        $question["question_text"]
                    );
                    ?>

                </div>


                <?php if (count($question_options) === 0): ?>

                    <div class="quiz-message">

                        No options found for this question.

                    </div>

                <?php else: ?>

                    <div class="options">

                        <?php

                        $letters = [
                            "A",
                            "B",
                            "C",
                            "D"
                        ];

                        foreach (
                            $question_options
                            as $option_index => $option
                        ):

                        ?>

                            <label class="option">

                                <input
                                    type="radio"
                                    name="answers[<?php
                                    echo $question_id;
                                    ?>]"
                                    value="<?php
                                    echo (int)$option["id"];
                                    ?>"
                                    required
                                >

                                <span
                                    class="option-letter"
                                >
                                    <?php
                                    echo $letters[
                                        $option_index
                                    ] ?? "?";
                                    ?>
                                </span>

                                <span
                                    class="option-text"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $option["option_text"]
                                    );
                                    ?>
                                </span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        <?php endforeach; ?>


        <!-- =============================================
             SUBMIT
        ============================================== -->

        <div class="submit-area">

            <button
                type="submit"
                class="submit-button"
                id="submitBtn"
            >

                Submit Quiz

                <span>→</span>

            </button>

        </div>

    </form>

    <?php endif; ?>


    <div class="back-dashboard">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>

</main>


<script>

/* =====================================================
   ELEMENTS
===================================================== */

const quizForm =
    document.getElementById("quizForm");

const timer =
    document.getElementById("timer");

const timerText =
    document.getElementById("timerText");

const submitBtn =
    document.getElementById("submitBtn");

const progressFill =
    document.getElementById("progressFill");

const progressText =
    document.getElementById("progressText");


let timeLeft =
    <?php echo $duration_minutes; ?> * 60;

let countdown = null;

let submitted = false;


/* =====================================================
   TIMER
===================================================== */

function updateTimer() {

    if (
        !quizForm ||
        !timer ||
        submitted
    ) {
        return;
    }

    const minutes =
        Math.floor(timeLeft / 60);

    const seconds =
        timeLeft % 60;


    timerText.textContent =
        String(minutes).padStart(2, "0")
        + ":"
        + String(seconds).padStart(2, "0");


    if (timeLeft <= 60) {

        timer.classList.add("warning");

    }


    if (timeLeft <= 0) {

        clearInterval(countdown);

        submitted = true;

        timerText.textContent =
            "00:00";


        quizForm
            .querySelectorAll("[required]")
            .forEach(function(field) {

                field.removeAttribute("required");

            });


        if (submitBtn) {

            submitBtn.disabled = true;

            submitBtn.textContent =
                "Submitting...";

        }


        quizForm.submit();

        return;
    }


    timeLeft--;

}


/* =====================================================
   PROGRESS
===================================================== */

function updateProgress() {

    if (
        !quizForm ||
        !progressFill ||
        !progressText
    ) {
        return;
    }


    const total =
        <?php echo $total_questions; ?>;


    const answered =
        quizForm.querySelectorAll(
            'input[type="radio"]:checked'
        ).length;


    const percent =
        total > 0
        ? (answered / total) * 100
        : 0;


    progressFill.style.width =
        percent + "%";


    progressText.textContent =
        answered + " / " + total;

}


/* =====================================================
   OPTION SELECT
===================================================== */

document
    .querySelectorAll(".option")
    .forEach(function(option) {

        const radio =
            option.querySelector(
                'input[type="radio"]'
            );


        radio.addEventListener(
            "change",
            function() {

                const name =
                    radio.name;


                document
                    .querySelectorAll(
                        'input[name="' +
                        name +
                        '"]'
                    )
                    .forEach(function(input) {

                        const parent =
                            input.closest(".option");

                        if (parent) {

                            parent.classList.remove(
                                "selected"
                            );

                        }

                    });


                option.classList.add(
                    "selected"
                );


                updateProgress();

            }
        );

    });


/* =====================================================
   START TIMER
===================================================== */

if (quizForm && timer) {

    updateTimer();

    countdown =
        setInterval(
            updateTimer,
            1000
        );


    /* =============================================
       SUBMIT CONFIRMATION
    ============================================== */

    quizForm.addEventListener(
        "submit",
        function(event) {

            if (submitted) {
                return;
            }


            const unanswered =
                <?php echo $total_questions; ?>
                -
                quizForm.querySelectorAll(
                    'input[type="radio"]:checked'
                ).length;


            if (unanswered > 0) {

                const confirmSubmit =
                    confirm(
                        "You have " +
                        unanswered +
                        " unanswered question(s). Submit anyway?"
                    );


                if (!confirmSubmit) {

                    event.preventDefault();

                    return;

                }

            } else {

                const confirmSubmit =
                    confirm(
                        "Are you sure you want to submit the quiz?"
                    );


                if (!confirmSubmit) {

                    event.preventDefault();

                    return;

                }

            }


            submitted = true;


            if (countdown) {

                clearInterval(countdown);

            }


            if (submitBtn) {

                submitBtn.disabled = true;

                submitBtn.textContent =
                    "Submitting...";

            }

        }
    );

}


updateProgress();

</script>

</body>
</html>