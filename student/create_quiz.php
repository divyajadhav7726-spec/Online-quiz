<?php
session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $difficulty = $_POST["difficulty"] ?? "Easy";
    $duration = intval($_POST["duration"] ?? 0);
    $total_marks = intval($_POST["total_marks"] ?? 0);
    $visibility = $_POST["visibility"] ?? "public";

    if (
        $title === "" ||
        $category_id <= 0 ||
        $duration <= 0 ||
        $total_marks <= 0
    ) {

        $message = "Please fill all required fields correctly.";
        $message_type = "error";

    } elseif (!in_array($difficulty, ["Easy", "Medium", "Hard"])) {

        $message = "Invalid difficulty selected.";
        $message_type = "error";

    } elseif (!in_array($visibility, ["public", "link"])) {

        $message = "Invalid visibility selected.";
        $message_type = "error";

    } else {

        $share_token = null;

        if ($visibility === "link") {
            $share_token = bin2hex(random_bytes(16));
        }

        $sql = "INSERT INTO quizzes
                (
                    category_id,
                    title,
                    description,
                    difficulty,
                    duration_minutes,
                    total_marks,
                    status,
                    created_by,
                    visibility,
                    share_token
                )
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "isssiiiss",
                $category_id,
                $title,
                $description,
                $difficulty,
                $duration,
                $total_marks,
                $user_id,
                $visibility,
                $share_token
            );

            if ($stmt->execute()) {

                $share_url =
                    "http://" .
                    $_SERVER["HTTP_HOST"] .
                    "/online-quiz/student/quiz.php?id=" .
                    $stmt->insert_id;

                if ($visibility === "link") {
                    $share_url .= "&token=" . $share_token;
                }

                $message = "Quiz created successfully! Share Link: " . $share_url;
                $message_type = "success";

            } else {

                $message = "Failed to create quiz.";
                $message_type = "error";
            }

            $stmt->close();

        } else {

            $message = "Database error. Please try again.";
            $message_type = "error";
        }
    }
}

$categories = [];

$category_sql = "SELECT id, name FROM categories ORDER BY name";
$category_result = $conn->query($category_sql);

if ($category_result) {

    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Quiz - Online Quiz</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .create-page {
            min-height: calc(100vh - 70px);
            background: #f5f7fb;
            padding: 40px 20px 60px;
        }

        .create-container {
            max-width: 1200px;
            margin: auto;
        }

        .create-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .create-header-left h1 {
            margin: 0;
            font-size: 34px;
            color: #172554;
            font-weight: 800;
        }

        .create-header-left p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 15px;
        }

        .back-btn {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 10px;
            background: white;
            color: #334155;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }

        .create-message {
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .create-message.success {
            background: #ecfdf5;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .create-message.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .create-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
            gap: 30px;
            align-items: start;
        }

        .create-form-card {
            background: white;
            border-radius: 18px;
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.07);
        }

        .section-heading {
            margin-bottom: 22px;
        }

        .section-heading h2 {
            margin: 0;
            color: #172554;
            font-size: 21px;
        }

        .section-heading p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .required {
            color: #dc2626;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
            outline: none;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.10);
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .difficulty-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .difficulty-option input {
            position: absolute;
            opacity: 0;
        }

        .difficulty-option label {
            display: block;
            padding: 13px 10px;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            cursor: pointer;
        }

        .difficulty-option input:checked + label {
            border-color: #4f46e5;
            background: #eef2ff;
            color: #4338ca;
        }

        .visibility-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .visibility-option input {
            position: absolute;
            opacity: 0;
        }

        .visibility-option label {
            display: block;
            padding: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            cursor: pointer;
        }

        .visibility-option input:checked + label {
            border-color: #4f46e5;
            background: #eef2ff;
        }

        .visibility-title {
            display: block;
            color: #172554;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .visibility-description {
            display: block;
            color: #64748b;
            font-size: 13px;
        }

        .create-submit {
            width: 100%;
            margin-top: 8px;
            border: none;
            padding: 15px 20px;
            border-radius: 11px;
            background: #4f46e5;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .create-submit:hover {
            background: #4338ca;
        }

        .preview-column {
            position: sticky;
            top: 20px;
        }

        .preview-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.07);
        }

        .preview-top {
            background: linear-gradient(135deg, #172554, #312e81);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .preview-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.14);
            font-size: 40px;
        }

        .preview-top h2 {
            margin: 0;
            font-size: 22px;
        }

        .preview-top p {
            margin: 7px 0 0;
            color: #c7d2fe;
            font-size: 13px;
        }

        .preview-body {
            padding: 24px;
        }

        .preview-feature {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .preview-feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .preview-feature strong {
            display: block;
            color: #334155;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .preview-feature span {
            color: #64748b;
            font-size: 12px;
        }

        .creator-tip {
            margin-top: 10px;
            padding: 16px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .creator-tip strong {
            display: block;
            color: #172554;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .creator-tip p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .create-grid {
                grid-template-columns: 1fr;
            }

            .preview-column {
                position: static;
            }
        }

        @media (max-width: 650px) {
            .create-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row,
            .difficulty-options,
            .visibility-options {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<nav class="navbar">

    <div class="navbar-brand">
        🧠 <span>Online Quiz</span>
    </div>

    <div class="navbar-links">

        <a href="dashboard.php">Dashboard</a>

        <a href="available_quizzes.php">Quizzes</a>

        <a href="my_quizzes.php">My Quizzes</a>

        <a href="result.php">Results</a>

        <a href="leaderboard.php">Leaderboard</a>

        <a href="../logout.php" class="logout-link">Logout</a>

    </div>

</nav>


<main class="create-page">

    <div class="create-container">

        <div class="create-header">

            <div class="create-header-left">

                <h1>Create a New Quiz</h1>

                <p>
                    Create your own quiz and share it with other students.
                </p>

            </div>

            <a href="my_quizzes.php" class="back-btn">
                ← Back to My Quizzes
            </a>

        </div>


        <?php if ($message !== ""): ?>

            <div class="create-message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <div class="create-grid">

            <div class="create-form-card">

                <div class="section-heading">

                    <h2>Quiz Information</h2>

                    <p>
                        Enter the basic details of your quiz.
                    </p>

                </div>


                <form method="POST">


                    <div class="form-group">

                        <label for="title">
                            Quiz Title <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            placeholder="Example: Python Basics Quiz"
                            value="<?= htmlspecialchars($_POST["title"] ?? "") ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            placeholder="Write a short description..."
                        ><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>

                    </div>


                    <div class="form-group">

                        <label for="category_id">
                            Category <span class="required">*</span>
                        </label>

                        <select
                            id="category_id"
                            name="category_id"
                            required
                        >

                            <option value="">
                                Select Category
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= $category["id"] ?>"
                                    <?= (
                                        isset($_POST["category_id"]) &&
                                        $_POST["category_id"] == $category["id"]
                                    ) ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($category["name"]) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Difficulty</label>

                        <div class="difficulty-options">

                            <div class="difficulty-option">

                                <input
                                    type="radio"
                                    id="easy"
                                    name="difficulty"
                                    value="Easy"
                                    <?= ($_POST["difficulty"] ?? "Easy") === "Easy" ? "checked" : "" ?>
                                >

                                <label for="easy">Easy</label>

                            </div>

                            <div class="difficulty-option">

                                <input
                                    type="radio"
                                    id="medium"
                                    name="difficulty"
                                    value="Medium"
                                    <?= ($_POST["difficulty"] ?? "") === "Medium" ? "checked" : "" ?>
                                >

                                <label for="medium">Medium</label>

                            </div>

                            <div class="difficulty-option">

                                <input
                                    type="radio"
                                    id="hard"
                                    name="difficulty"
                                    value="Hard"
                                    <?= ($_POST["difficulty"] ?? "") === "Hard" ? "checked" : "" ?>
                                >

                                <label for="hard">Hard</label>

                            </div>

                        </div>

                    </div>


                    <div class="section-heading">

                        <h2>Quiz Settings</h2>

                        <p>Set duration and total marks.</p>

                    </div>


                    <div class="form-row">

                        <div class="form-group">

                            <label for="duration">
                                Duration <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="duration"
                                name="duration"
                                min="1"
                                placeholder="Minutes"
                                value="<?= htmlspecialchars($_POST["duration"] ?? "") ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="total_marks">
                                Total Marks <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="total_marks"
                                name="total_marks"
                                min="1"
                                placeholder="Total marks"
                                value="<?= htmlspecialchars($_POST["total_marks"] ?? "") ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>Quiz Visibility</label>

                        <div class="visibility-options">

                            <div class="visibility-option">

                                <input
                                    type="radio"
                                    id="public"
                                    name="visibility"
                                    value="public"
                                    <?= ($_POST["visibility"] ?? "public") === "public" ? "checked" : "" ?>
                                >

                                <label for="public">

                                    <span class="visibility-title">
                                        🌍 Public Quiz
                                    </span>

                                    <span class="visibility-description">
                                        Everyone can see and access this quiz.
                                    </span>

                                </label>

                            </div>


                            <div class="visibility-option">

                                <input
                                    type="radio"
                                    id="link"
                                    name="visibility"
                                    value="link"
                                    <?= ($_POST["visibility"] ?? "") === "link" ? "checked" : "" ?>
                                >

                                <label for="link">

                                    <span class="visibility-title">
                                        🔗 Link Only
                                    </span>

                                    <span class="visibility-description">
                                        Only people with the quiz link can access it.
                                    </span>

                                </label>

                            </div>

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="create-submit"
                    >
                        🚀 Create Quiz
                    </button>

                </form>

            </div>


            <div class="preview-column">

                <div class="preview-card">

                    <div class="preview-top">

                        <div class="preview-icon">
                            🧠
                        </div>

                        <h2>Build Your Quiz</h2>

                        <p>Create • Share • Learn</p>

                    </div>


                    <div class="preview-body">

                        <div class="preview-feature">

                            <div class="preview-feature-icon">
                                ✓
                            </div>

                            <div>
                                <strong>Choose a Category</strong>
                                <span>Organize your quiz by subject.</span>
                            </div>

                        </div>


                        <div class="preview-feature">

                            <div class="preview-feature-icon">
                                ⏱
                            </div>

                            <div>
                                <strong>Set Quiz Duration</strong>
                                <span>Control how much time students get.</span>
                            </div>

                        </div>


                        <div class="preview-feature">

                            <div class="preview-feature-icon">
                                🎯
                            </div>

                            <div>
                                <strong>Set Total Marks</strong>
                                <span>Define the scoring structure.</span>
                            </div>

                        </div>


                        <div class="preview-feature">

                            <div class="preview-feature-icon">
                                🔗
                            </div>

                            <div>
                                <strong>Share Your Quiz</strong>
                                <span>Make it public or share through a private link.</span>
                            </div>

                        </div>


                        <div class="creator-tip">

                            <strong>💡 Creator Tip</strong>

                            <p>
                                After creating the quiz, you can add questions
                                and answer options from your quiz management page.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<footer class="footer">

    <p>
        © <?= date("Y") ?> Online Quiz. All rights reserved.
    </p>

</footer>

</body>

</html>