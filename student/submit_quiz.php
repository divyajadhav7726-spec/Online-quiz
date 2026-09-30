<?php
session_start();

require_once "../config/db.php";

/* =========================================================
   1. LOGIN + ROLE CHECK
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

/* =========================================================
   2. ONLY POST REQUEST ALLOWED
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

/* =========================================================
   3. GET QUIZ ID
========================================================= */

$quiz_id = filter_input(
    INPUT_POST,
    "quiz_id",
    FILTER_VALIDATE_INT
);

if (!$quiz_id || $quiz_id <= 0) {
    http_response_code(400);
    die("Invalid quiz ID.");
}

/* =========================================================
   4. GET QUIZ DETAILS
========================================================= */

$stmt = $conn->prepare(
    "SELECT 
        id,
        title,
        duration_minutes,
        visibility,
        share_token
     FROM quizzes
     WHERE id = ?
       AND status = 1
     LIMIT 1"
);

$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$quiz = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$quiz) {
    http_response_code(404);
    die("Quiz not found or unavailable.");
}

/* =========================================================
   5. CHECK LINK-ONLY QUIZ TOKEN
========================================================= */

if ($quiz["visibility"] === "link") {

    $provided_token = $_POST["share_token"] ?? "";

    if (
        !is_string($provided_token) ||
        empty($quiz["share_token"]) ||
        !hash_equals(
            (string) $quiz["share_token"],
            $provided_token
        )
    ) {
        http_response_code(403);
        die("Invalid or missing quiz share token.");
    }
}

/* =========================================================
   6. CHECK ANSWERS
========================================================= */

if (
    !isset($_POST["answers"]) ||
    !is_array($_POST["answers"])
) {
    die("Please answer all questions.");
}

$submitted_answers = $_POST["answers"];

/* =========================================================
   7. GET ALL QUESTIONS
========================================================= */

$stmt = $conn->prepare(
    "SELECT
        id,
        marks
     FROM questions
     WHERE quiz_id = ?
     ORDER BY id ASC"
);

$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$result = $stmt->get_result();

$questions = [];

while ($row = $result->fetch_assoc()) {

    $questions[(int) $row["id"]] = [
        "id" => (int) $row["id"],
        "marks" => (int) $row["marks"]
    ];
}

$stmt->close();

/* =========================================================
   8. CHECK QUESTIONS EXIST
========================================================= */

if (empty($questions)) {
    die("No questions found for this quiz.");
}

/* =========================================================
   9. CHECK ALL QUESTIONS ARE ANSWERED
========================================================= */

if (count($submitted_answers) !== count($questions)) {
    die("Please answer all questions.");
}

foreach ($questions as $question_id => $question) {

    if (!array_key_exists($question_id, $submitted_answers)) {
        die("Please answer all questions.");
    }
}

/* =========================================================
   10. CALCULATE SCORE
========================================================= */

$total_score = 0;

$answers_to_save = [];

foreach ($questions as $question_id => $question) {

    $raw_option_id = $submitted_answers[$question_id];

    /* -----------------------------------------
       Validate option ID
    ----------------------------------------- */

    if (
        !is_scalar($raw_option_id) ||
        !ctype_digit((string) $raw_option_id)
    ) {
        die("Invalid answer option.");
    }

    $selected_option_id = (int) $raw_option_id;

    if ($selected_option_id <= 0) {
        die("Invalid answer option.");
    }

    /* -----------------------------------------
       Check option belongs to question
    ----------------------------------------- */

    $stmt = $conn->prepare(
        "SELECT
            id,
            is_correct
         FROM options
         WHERE id = ?
           AND question_id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $selected_option_id,
        $question_id
    );

    $stmt->execute();

    $option = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$option) {
        die("Invalid answer option.");
    }

    /* -----------------------------------------
       Correct / Wrong
    ----------------------------------------- */

    $is_correct = (int) $option["is_correct"];

    if ($is_correct === 1) {

        $total_score += (int) $question["marks"];
    }

    /* -----------------------------------------
       Prepare answer for database
    ----------------------------------------- */

    $answers_to_save[] = [
        "question_id" => $question_id,
        "selected_option_id" => $selected_option_id,
        "is_correct" => $is_correct
    ];
}

/* =========================================================
   11. SAVE ATTEMPT + ANSWERS
   ONE TRANSACTION
========================================================= */

$conn->begin_transaction();

try {

    /* -----------------------------------------
       Save quiz attempt
    ----------------------------------------- */

    $stmt = $conn->prepare(
        "INSERT INTO quiz_attempts
        (
            user_id,
            quiz_id,
            score
        )
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "iii",
        $user_id,
        $quiz_id,
        $total_score
    );

    $stmt->execute();

    $attempt_id = $conn->insert_id;

    $stmt->close();

    if (!$attempt_id) {
        throw new Exception("Attempt could not be created.");
    }

    /* -----------------------------------------
       Save individual answers
    ----------------------------------------- */

    $stmt = $conn->prepare(
        "INSERT INTO answers
        (
            attempt_id,
            question_id,
            selected_option_id,
            is_correct
        )
        VALUES (?, ?, ?, ?)"
    );

    foreach ($answers_to_save as $answer) {

        $question_id = (int) $answer["question_id"];

        $selected_option_id =
            (int) $answer["selected_option_id"];

        $is_correct =
            (int) $answer["is_correct"];

        $stmt->bind_param(
            "iiii",
            $attempt_id,
            $question_id,
            $selected_option_id,
            $is_correct
        );

        $stmt->execute();
    }

    $stmt->close();

    /* -----------------------------------------
       Commit transaction
    ----------------------------------------- */

    $conn->commit();

    /* -----------------------------------------
       Redirect to result page
    ----------------------------------------- */

    header(
        "Location: result.php?attempt_id=" .
        urlencode((string) $attempt_id)
    );

    exit();

} catch (Throwable $e) {

    /* -----------------------------------------
       Rollback if anything fails
    ----------------------------------------- */

    $conn->rollback();

    error_log(
        "Quiz submission error: " .
        $e->getMessage()
    );

    http_response_code(500);

    die(
        "Could not save quiz result. Please try again."
    );
}
?>