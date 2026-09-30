

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

/* ================= ADD QUIZ ================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $difficulty = $_POST["difficulty"] ?? "Easy";
    $duration = (int)($_POST["duration"] ?? 10);
    $total_marks = (int)($_POST["total_marks"] ?? 10);

    if (
        $title === "" ||
        $category_id <= 0 ||
        $duration <= 0 ||
        $total_marks <= 0
    ) {

        $message = "Please fill all required fields correctly.";
        $message_type = "danger";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO quizzes
            (category_id, title, description, difficulty,
             duration_minutes, total_marks, status)
            VALUES (?, ?, ?, ?, ?, ?, 1)"
        );

        $stmt->bind_param(
            "isssii",
            $category_id,
            $title,
            $description,
            $difficulty,
            $duration,
            $total_marks
        );

        if ($stmt->execute()) {

            $message = "Quiz added successfully!";
            $message_type = "success";

        } else {

            $message = "Error adding quiz: " . $stmt->error;
            $message_type = "danger";
        }

        $stmt->close();
    }
}


/* ================= FETCH CATEGORIES ================= */

$categories = $conn->query(
    "SELECT id, name
     FROM categories
     ORDER BY name"
);


/* ================= FETCH QUIZZES ================= */

$quizzes = $conn->query(
    "SELECT quizzes.*, categories.name AS category_name
     FROM quizzes
     LEFT JOIN categories
     ON quizzes.category_id = categories.id
     ORDER BY quizzes.id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Quizzes - Online Quiz</title>

    <!-- Global CSS -->
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
                <a href="dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="manage_quizzes.php">
                    Quizzes
                </a>
            </li>

            <li>
                <a href="manage_questions.php">
                    Questions
                </a>
            </li>

            <li>
                <a href="view_results.php">
                    Results
                </a>
            </li>

            <li>
                <a href="manage_users.php">
                    Users
                </a>
            </li>

            <li>
                <a href="../logout.php">
                    Logout
                </a>
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
                Manage Quizzes 📝
            </h1>

            <p>
                Create and manage quizzes available on the platform.
            </p>

        </div>

        <a href="dashboard.php"
           class="btn btn-secondary">

            ← Dashboard

        </a>

    </div>


    <!-- ================= MESSAGE ================= -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-<?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ================= ADD QUIZ ================= -->

    <div class="card">

        <h2>
            ➕ Add New Quiz
        </h2>

        <p style="color:#64748b; margin-bottom:20px;">
            Enter the basic information for your new quiz.
        </p>


        <form method="POST">


            <!-- TITLE -->

            <div class="form-group">

                <label for="title">
                    Quiz Title *
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Example: Python Basics Quiz"
                    required
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Write a short description about this quiz..."
                ></textarea>

            </div>


            <!-- CATEGORY -->

            <div class="grid grid-2">

                <div class="form-group">

                    <label for="category_id">
                        Category *
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php if ($categories): ?>

                            <?php while ($category = $categories->fetch_assoc()): ?>

                                <option value="<?php echo $category['id']; ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $category['name']
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                </div>


                <!-- DIFFICULTY -->

                <div class="form-group">

                    <label for="difficulty">
                        Difficulty
                    </label>

                    <select
                        id="difficulty"
                        name="difficulty"
                    >

                        <option value="Easy">
                            Easy
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="Hard">
                            Hard
                        </option>

                    </select>

                </div>

            </div>


            <!-- DURATION + MARKS -->

            <div class="grid grid-2">

                <div class="form-group">

                    <label for="duration">
                        Duration (Minutes)
                    </label>

                    <input
                        type="number"
                        id="duration"
                        name="duration"
                        min="1"
                        value="10"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="total_marks">
                        Total Marks
                    </label>

                    <input
                        type="number"
                        id="total_marks"
                        name="total_marks"
                        min="1"
                        value="10"
                        required
                    >

                </div>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="btn btn-primary"
            >

                ➕ Add Quiz

            </button>


        </form>

    </div>


    <!-- ================= QUIZ LIST ================= -->

    <div class="card">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-bottom:20px;
            flex-wrap:wrap;
        ">

            <div>

                <h2>
                    📚 Available Quizzes
                </h2>

                <p style="color:#64748b;">
                    All quizzes currently stored in the system.
                </p>

            </div>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Quiz Title
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Difficulty
                        </th>

                        <th>
                            Duration
                        </th>

                        <th>
                            Marks
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($quizzes && $quizzes->num_rows > 0): ?>

                        <?php while ($quiz = $quizzes->fetch_assoc()): ?>

                            <tr>

                                <td>

                                    <strong>
                                        #<?php echo $quiz['id']; ?>
                                    </strong>

                                </td>


                                <td>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $quiz['title']
                                        );
                                        ?>
                                    </strong>

                                    <?php if (!empty($quiz['description'])): ?>

                                        <br>

                                        <small style="color:#64748b;">

                                            <?php
                                            echo htmlspecialchars(
                                                $quiz['description']
                                            );
                                            ?>

                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <span class="badge badge-info">

                                        <?php
                                        echo htmlspecialchars(
                                            $quiz['category_name'] ?? "Unknown"
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    $difficulty = $quiz['difficulty'];

                                    if ($difficulty === "Easy") {
                                        $badge_class = "badge-success";
                                    } elseif ($difficulty === "Medium") {
                                        $badge_class = "badge-warning";
                                    } else {
                                        $badge_class = "badge-danger";
                                    }
                                    ?>

                                    <span class="badge <?php echo $badge_class; ?>">

                                        <?php
                                        echo htmlspecialchars(
                                            $difficulty
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    ⏱️
                                    <?php
                                    echo (int)$quiz['duration_minutes'];
                                    ?>
                                    min

                                </td>


                                <td>

                                    <strong>
                                        <?php
                                        echo (int)$quiz['total_marks'];
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php if ((int)$quiz['status'] === 1): ?>

                                        <span class="badge badge-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                style="text-align:center; padding:35px;"
                            >

                                <div class="empty-state">

                                    <h3>
                                        No Quizzes Found
                                    </h3>

                                    <p>
                                        Create your first quiz using
                                        the form above.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>


<!-- ================= FOOTER ================= -->

<footer class="footer">

    <div class="container">

        <p>

            © <?php echo date("Y"); ?>
            Online Quiz System.
            All rights reserved.

        </p>

    </div>

</footer>


</body>
</html>