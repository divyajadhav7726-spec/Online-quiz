<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../student/dashboard.php");
    exit();
}

require_once "../config/db.php";

$sql = "SELECT id, full_name, email, mobile, role, created_at
        FROM users
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - Online Quiz</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="nav-brand">
        🧠 Online Quiz
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="manage_quizzes.php">
            Quizzes
        </a>

        <a href="manage_questions.php">
            Questions
        </a>

        <a href="view_results.php">
            Results
        </a>

        <a href="manage_users.php" class="active">
            Users
        </a>

        <a href="../logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</nav>


<!-- ================= MAIN CONTENT ================= -->

<div class="container">

    <div class="page-header">

        <div>

            <h1>Manage Users</h1>

            <p>
                View registered students and administrators.
            </p>

        </div>

    </div>


    <!-- USER STATISTICS -->

    <div class="user-summary">

        <?php

        $total_users = 0;
        $total_admins = 0;
        $total_students = 0;

        if ($result) {

            $users_data = [];

            while ($user = mysqli_fetch_assoc($result)) {

                $users_data[] = $user;

                $total_users++;

                if ($user["role"] === "admin") {
                    $total_admins++;
                } else {
                    $total_students++;
                }
            }

        } else {

            $users_data = [];

        }

        ?>


        <div class="user-stat-card">

            <div class="user-stat-icon">
                👥
            </div>

            <div>

                <span>Total Users</span>

                <strong>
                    <?php echo $total_users; ?>
                </strong>

            </div>

        </div>


        <div class="user-stat-card">

            <div class="user-stat-icon admin-icon">
                🛡️
            </div>

            <div>

                <span>Administrators</span>

                <strong>
                    <?php echo $total_admins; ?>
                </strong>

            </div>

        </div>


        <div class="user-stat-card">

            <div class="user-stat-icon student-icon">
                🎓
            </div>

            <div>

                <span>Students</span>

                <strong>
                    <?php echo $total_students; ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- USERS TABLE -->

    <div class="card">

        <div class="card-header">

            <div>

                <h2>👥 Registered Users</h2>

                <p>
                    User accounts registered in the Online Quiz System.
                </p>

            </div>

        </div>


        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>User</th>

                        <th>Email</th>

                        <th>Mobile</th>

                        <th>Role</th>

                        <th>Registered On</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($users_data) > 0): ?>

                    <?php foreach ($users_data as $user): ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <span class="id-badge">

                                    #<?php
                                    echo htmlspecialchars(
                                        $user["id"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- USER -->

                            <td>

                                <div class="student-info">

                                    <div class="student-avatar">

                                        <?php
                                        echo strtoupper(
                                            substr(
                                                $user["full_name"],
                                                0,
                                                1
                                            )
                                        );
                                        ?>

                                    </div>

                                    <div>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $user["full_name"]
                                            );
                                            ?>

                                        </strong>

                                    </div>

                                </div>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <span class="email-text">

                                    <?php
                                    echo htmlspecialchars(
                                        $user["email"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- MOBILE -->

                            <td>

                                <?php if (!empty($user["mobile"])): ?>

                                    <span class="mobile-text">

                                        📱
                                        <?php
                                        echo htmlspecialchars(
                                            $user["mobile"]
                                        );
                                        ?>

                                    </span>

                                <?php else: ?>

                                    <span class="not-available">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ROLE -->

                            <td>

                                <?php if ($user["role"] === "admin"): ?>

                                    <span class="role-badge role-admin">
                                        🛡️ Admin
                                    </span>

                                <?php else: ?>

                                    <span class="role-badge role-student">
                                        🎓 Student
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <span class="date-text">

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $user["created_at"]
                                        )
                                    );

                                    ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">

                            <div class="empty-state">

                                <div class="empty-icon">
                                    👥
                                </div>

                                <h3>
                                    No Users Found
                                </h3>

                                <p>
                                    Registered users will appear here.
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ================= FOOTER ================= -->

<footer class="footer">

    <p>
        © 2026 Online Quiz System. All Rights Reserved.
    </p>

</footer>


</body>
</html>