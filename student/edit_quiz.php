
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$user_id = (int)$_SESSION["user_id"];

$quiz_id = (int)($_GET["id"] ?? $_POST["quiz_id"] ?? 0);
$message = "";

if ($quiz_id <= 0) {
    exit("Invalid quiz ID.");
}

// Fetch only the quiz created by the logged-in user
$stmt = $conn->prepare(
    "SELECT id, category_id, title, description, difficulty,
            duration_minutes, total_marks, visibility
     FROM quizzes
     WHERE id = ? AND created_by = ?"
);
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();
$stmt->close();

if (!$quiz) {
    exit("Quiz not found or you are not allowed to edit it.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $difficulty = $_POST["difficulty"] ?? "Easy";
    $duration = (int)($_POST["duration"] ?? 10);
    $total_marks = (int)($_POST["total_marks"] ?? 10);
    $visibility = $_POST["visibility"] ?? "public";

    if (
        $title === "" ||
        $category_id <= 0 ||
        !in_array($difficulty, ["Easy", "Medium", "Hard"], true) ||
        $duration <= 0 ||
        $total_marks <= 0 ||
        !in_array($visibility, ["public", "link"], true)
    ) {
        $message = "Please fill all required fields correctly.";
    } else {

        $update = $conn->prepare(
            "UPDATE quizzes
             SET category_id = ?, title = ?, description = ?,
                 difficulty = ?, duration_minutes = ?,
                 total_marks = ?, visibility = ?
             WHERE id = ? AND created_by = ?"
        );

        $update->bind_param(
            "isssiisii",
            $category_id,
            $title,
            $description,
            $difficulty,
            $duration,
            $total_marks,
            $visibility,
            $quiz_id,
            $user_id
        );

        if ($update->execute()) {
            $message = "Quiz updated successfully!";

            // Refresh quiz values shown in the form
            $quiz["category_id"] = $category_id;
            $quiz["title"] = $title;
            $quiz["description"] = $description;
            $quiz["difficulty"] = $difficulty;
            $quiz["duration_minutes"] = $duration;
            $quiz["total_marks"] = $total_marks;
            $quiz["visibility"] = $visibility;
        } else {
            $message = "Error updating quiz. Please try again.";
        }

        $update->close();
    }
}

$categories = $conn->query(
    "SELECT id, name FROM categories ORDER BY name"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - Smart Quiz</title>

    <style>
        body {
            margin: 0;
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
            max-width: 750px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000012;
        }

        h2 {
            color: #0b3d91;
            margin-top: 0;
        }

        label {
            display: block;
            margin: 15px 0 7px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            margin-top: 22px;
            padding: 13px 22px;
            background: #0b3d91;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #082c68;
        }

        .message {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            overflow-wrap: anywhere;
        }
    </style>
</head>

<body>

<header>
    <a href="my_quizzes.php">← My Quizzes</a>
    <strong>Edit Quiz</strong>
</header>

<div class="container">
    <div class="panel">

        <h2>Edit Your Quiz</h2>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="quiz_id"
                   value="<?php echo (int)$quiz["id"]; ?>">

            <label>Quiz Title *</label>
            <input type="text" name="title" required
                   value="<?php echo htmlspecialchars($quiz["title"]); ?>">

            <label>Description</label>
            <textarea name="description"><?php
                echo htmlspecialchars($quiz["description"] ?? "");
            ?></textarea>

            <label>Category *</label>
            <select name="category_id" required>
                <option value="">Select Category</option>

                <?php if ($categories): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option
                            value="<?php echo (int)$category["id"]; ?>"
                            <?php
                            echo ((int)$quiz["category_id"] ===
                                  (int)$category["id"]) ? "selected" : "";
                            ?>>
                            <?php echo htmlspecialchars($category["name"]); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <label>Difficulty</label>
            <select name="difficulty">
                <?php foreach (["Easy", "Medium", "Hard"] as $level): ?>
                    <option value="<?php echo $level; ?>"
                        <?php echo $quiz["difficulty"] === $level
                            ? "selected" : ""; ?>>
                        <?php echo $level; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Duration (minutes)</label>
            <input type="number" name="duration" min="1" required
                   value="<?php echo (int)$quiz["duration_minutes"]; ?>">

            <label>Total Marks</label>
            <input type="number" name="total_marks" min="1" required
                   value="<?php echo (int)$quiz["total_marks"]; ?>">

            <label>Quiz Visibility</label>
            <select name="visibility" required>
                <option value="public"
                    <?php echo $quiz["visibility"] === "public"
                        ? "selected" : ""; ?>>
                    Public - Everyone can find it
                </option>

                <option value="link"
                    <?php echo $quiz["visibility"] === "link"
                        ? "selected" : ""; ?>>
                    Link-only - Share using a link
                </option>
            </select>

            <button type="submit">Save Changes</button>
        </form>

    </div>
</div>

</body>
</html>