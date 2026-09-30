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

/* =========================
   ONLY POST ALLOWED
========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my_questions.php");
    exit();
}

$question_id = (int)($_POST["question_id"] ?? 0);

if ($question_id <= 0) {
    exit("Invalid question ID.");
}


/* =========================
   VERIFY QUESTION OWNER
========================= */

$check = $conn->prepare(
    "SELECT q.id, q.quiz_id
     FROM questions q
     INNER JOIN quizzes z ON z.id = q.quiz_id
     WHERE q.id = ? AND z.created_by = ?"
);

$check->bind_param(
    "ii",
    $question_id,
    $user_id
);

$check->execute();

$result = $check->get_result();
$question = $result->fetch_assoc();

$check->close();


if (!$question) {
    exit("Question not found or you are not allowed to delete it.");
}


/* =========================
   DELETE QUESTION
========================= */

try {

    $conn->begin_transaction();


    /*
     * Delete answers related to this question.
     * This keeps quiz attempt data from
     * pointing to a deleted question.
     */

    $stmt = $conn->prepare(
        "DELETE FROM answers
         WHERE question_id = ?"
    );

    $stmt->bind_param(
        "i",
        $question_id
    );

    $stmt->execute();
    $stmt->close();


    /*
     * Delete the four options
     */

    $stmt = $conn->prepare(
        "DELETE FROM options
         WHERE question_id = ?"
    );

    $stmt->bind_param(
        "i",
        $question_id
    );

    $stmt->execute();
    $stmt->close();


    /*
     * Finally delete the question
     */

    $stmt = $conn->prepare(
        "DELETE FROM questions
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $question_id
    );

    $stmt->execute();


    if ($stmt->affected_rows !== 1) {
        throw new Exception("Question could not be deleted.");
    }

    $stmt->close();


    $conn->commit();


    header("Location: my_questions.php?deleted=1");
    exit();


} catch (Throwable $e) {

    $conn->rollback();

    error_log(
        "Question deletion failed: " .
        $e->getMessage()
    );

    exit(
        "Could not delete this question. Please try again."
    );
}
?>