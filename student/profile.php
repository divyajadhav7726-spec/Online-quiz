<?php
session_start();
require_once "../config/db.php";

/* ================= LOGIN CHECK ================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

/* ================= STUDENT ROLE CHECK ================= */

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$message = "";
$message_type = "";


/* ================= UPDATE PROFILE ================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $new_password = $_POST["new_password"] ?? "";

    if ($full_name === "") {

        $message = "Full name is required.";
        $message_type = "error";

    } else {

        if ($new_password !== "") {

            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "UPDATE users
                 SET full_name = ?, mobile = ?, password = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "sssi",
                $full_name,
                $mobile,
                $hashed_password,
                $user_id
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE users
                 SET full_name = ?, mobile = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ssi",
                $full_name,
                $mobile,
                $user_id
            );
        }

        if ($stmt->execute()) {

            $_SESSION["full_name"] = $full_name;

            $message = "Profile updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update profile.";
            $message_type = "error";
        }

        $stmt->close();
    }
}


/* ================= GET USER DATA ================= */

$stmt = $conn->prepare(
    "SELECT id, full_name, email, mobile, role, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    die("User account not found.");
}

$first_letter = strtoupper(
    substr(trim($user["full_name"]), 0, 1)
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

    <title>My Profile - Online Quiz</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* =====================================================
           PROFILE PAGE
        ===================================================== */

        body {
            background: #f8faff !important;
        }

        .profile-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 38px 45px 60px;
        }


        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .profile-header {
            margin-bottom: 30px;
        }

        .profile-header h1 {
            margin: 0;

            color: #19356f;

            font-size: 34px;
            font-weight: 800;
        }

        .profile-header p {
            margin: 8px 0 0;

            color: #7183a6;

            font-size: 16px;
        }


        /* =====================================================
           MESSAGE
        ===================================================== */

        .profile-message {
            margin-bottom: 25px;

            padding: 14px 18px;

            border-radius: 10px;

            font-size: 14px;
            font-weight: 700;
        }

        .profile-success {
            background: #eaf8f1;
            color: #167653;
            border: 1px solid #c5ead8;
        }

        .profile-error {
            background: #fff0f0;
            color: #b42323;
            border: 1px solid #f2caca;
        }


        /* =====================================================
           PROFILE LAYOUT
        ===================================================== */

        .profile-layout {
            display: grid;

            grid-template-columns:
                330px minmax(0, 1fr);

            gap: 25px;

            align-items: start;
        }


        /* =====================================================
           PROFILE SUMMARY
        ===================================================== */

        .profile-summary {
            background: #ffffff;

            border: 1px solid #e1e8f5;

            border-radius: 18px;

            padding: 30px;

            text-align: center;

            box-shadow:
                0 8px 25px
                rgba(31, 53, 110, 0.055);
        }


        .profile-avatar {
            width: 90px;
            height: 90px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #4169d5,
                    #2d4fa5
                );

            color: #ffffff;

            font-size: 34px;
            font-weight: 800;
        }


        .profile-summary h2 {
            margin: 0;

            color: #19356f;

            font-size: 24px;
            font-weight: 800;
        }


        .profile-email {
            margin-top: 7px;

            color: #7183a6;

            font-size: 14px;

            word-break: break-word;
        }


        .profile-role {
            display: inline-flex;

            margin-top: 15px;

            padding: 7px 14px;

            background: #eaf0ff;

            color: #3157ad;

            border-radius: 20px;

            font-size: 12px;
            font-weight: 800;
        }


        /* =====================================================
           SUMMARY DETAILS
        ===================================================== */

        .profile-details {
            margin-top: 28px;

            border-top: 1px solid #e7ecf5;

            padding-top: 18px;

            text-align: left;
        }


        .profile-detail {
            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding: 12px 0;

            border-bottom: 1px solid #f0f3f8;
        }


        .profile-detail:last-child {
            border-bottom: none;
        }


        .detail-label {
            color: #7b8baa;

            font-size: 13px;
        }


        .detail-value {
            color: #344875;

            font-size: 13px;

            font-weight: 700;

            text-align: right;

            word-break: break-word;
        }


        /* =====================================================
           EDIT CARD
        ===================================================== */

        .profile-edit {
            background: #ffffff;

            border: 1px solid #e1e8f5;

            border-radius: 18px;

            padding: 32px;

            box-shadow:
                0 8px 25px
                rgba(31, 53, 110, 0.055);
        }


        .edit-heading {
            margin-bottom: 25px;
        }


        .edit-heading h2 {
            margin: 0;

            color: #19356f;

            font-size: 24px;
            font-weight: 800;
        }


        .edit-heading p {
            margin: 7px 0 0;

            color: #7183a6;

            font-size: 14px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .profile-form {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;
        }


        .form-group {
            display: flex;
            flex-direction: column;
        }


        .form-group.full-width {
            grid-column: 1 / -1;
        }


        .form-group label {
            margin-bottom: 8px;

            color: #405275;

            font-size: 13px;

            font-weight: 700;
        }


        .form-group input {
            width: 100%;

            box-sizing: border-box;

            padding: 12px 14px;

            border: 1px solid #d4ddea;

            border-radius: 9px;

            background: #ffffff;

            color: #263b68;

            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        .form-group input:focus {
            border-color: #4d6fd3;

            box-shadow:
                0 0 0 3px
                rgba(77, 111, 211, 0.10);
        }


        .form-group input[readonly] {
            background: #f6f8fc;

            color: #7183a6;

            cursor: not-allowed;
        }


        .form-note {
            margin-top: 7px;

            color: #8795ad;

            font-size: 12px;
        }


        /* =====================================================
           BUTTONS
        ===================================================== */

        .profile-form-actions {
            grid-column: 1 / -1;

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 5px;

            padding-top: 20px;

            border-top: 1px solid #e9edf4;
        }


        .profile-button {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            padding: 11px 20px;

            border-radius: 9px;

            border: none;

            font-size: 13px;

            font-weight: 700;

            text-decoration: none;

            cursor: pointer;
        }


        .back-button {
            background: #f0f3f9;

            color: #526582;
        }


        .save-button {
            background: #3f62c9;

            color: #ffffff;
        }


        .save-button:hover {
            background: #3455b6;
        }


        /* =====================================================
           SECURITY NOTE
        ===================================================== */

        .security-note {
            margin-top: 25px;

            padding: 18px 20px;

            background: #f5f8ff;

            border: 1px solid #dfe7fa;

            border-radius: 12px;
        }


        .security-note strong {
            display: block;

            color: #294b99;

            font-size: 14px;

            margin-bottom: 5px;
        }


        .security-note span {
            color: #7183a6;

            font-size: 13px;

            line-height: 1.6;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .profile-page {
                padding: 30px;
            }

            .profile-layout {
                grid-template-columns: 1fr;
            }

            .profile-summary {
                text-align: center;
            }

        }


        @media (max-width: 650px) {

            .profile-page {
                padding: 24px 18px 45px;
            }

            .profile-header h1 {
                font-size: 29px;
            }

            .profile-edit {
                padding: 24px;
            }

            .profile-form {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .profile-form-actions {
                grid-column: auto;

                flex-direction: column-reverse;

                align-items: stretch;
            }

            .profile-button {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

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

        <a
            href="profile.php"
            class="active"
        >
            Profile
        </a>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="profile-page">


    <!-- PAGE HEADER -->

    <div class="profile-header">

        <h1>
            My Profile
        </h1>

        <p>
            Manage your account information and security settings.
        </p>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div
            class="profile-message
            <?php
            echo $message_type === "success"
                ? "profile-success"
                : "profile-error";
            ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- PROFILE LAYOUT -->

    <div class="profile-layout">


        <!-- =================================================
             PROFILE SUMMARY
        ================================================= -->

        <section class="profile-summary">


            <div class="profile-avatar">

                <?php
                echo htmlspecialchars($first_letter);
                ?>

            </div>


            <h2>
                <?php
                echo htmlspecialchars(
                    $user["full_name"]
                );
                ?>
            </h2>


            <div class="profile-email">

                <?php
                echo htmlspecialchars(
                    $user["email"]
                );
                ?>

            </div>


            <div class="profile-role">
                👨‍🎓 Student Account
            </div>


            <div class="profile-details">


                <div class="profile-detail">

                    <span class="detail-label">
                        Account ID
                    </span>

                    <span class="detail-value">
                        #<?php
                        echo (int) $user["id"];
                        ?>
                    </span>

                </div>


                <div class="profile-detail">

                    <span class="detail-label">
                        Mobile
                    </span>

                    <span class="detail-value">

                        <?php
                        echo !empty($user["mobile"])
                            ? htmlspecialchars($user["mobile"])
                            : "Not added";
                        ?>

                    </span>

                </div>


                <div class="profile-detail">

                    <span class="detail-label">
                        Role
                    </span>

                    <span class="detail-value">
                        Student
                    </span>

                </div>


                <div class="profile-detail">

                    <span class="detail-label">
                        Member Since
                    </span>

                    <span class="detail-value">

                        <?php

                        if (!empty($user["created_at"])) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $user["created_at"]
                                )
                            );

                        } else {

                            echo "N/A";

                        }

                        ?>

                    </span>

                </div>


            </div>


        </section>


        <!-- =================================================
             EDIT PROFILE
        ================================================= -->

        <section class="profile-edit">


            <div class="edit-heading">

                <h2>
                    Edit Profile
                </h2>

                <p>
                    Update your personal information below.
                </p>

            </div>


            <form
                method="POST"
                class="profile-form"
            >


                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php
                        echo htmlspecialchars(
                            $user["full_name"]
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        value="<?php
                        echo htmlspecialchars(
                            $user["email"]
                        );
                        ?>"
                        readonly
                    >

                    <span class="form-note">
                        Email cannot be changed here.
                    </span>

                </div>


                <!-- MOBILE -->

                <div class="form-group">

                    <label for="mobile">
                        Mobile Number
                    </label>

                    <input
                        type="text"
                        id="mobile"
                        name="mobile"
                        value="<?php
                        echo htmlspecialchars(
                            $user["mobile"] ?? ""
                        );
                        ?>"
                        maxlength="15"
                        placeholder="Enter mobile number"
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="new_password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="Enter new password"
                        minlength="6"
                    >

                    <span class="form-note">
                        Leave blank to keep your current password.
                    </span>

                </div>


                <!-- SECURITY NOTE -->

                <div class="security-note full-width">

                    <strong>
                        🔐 Account Security
                    </strong>

                    <span>
                        Your password is securely stored using
                        password hashing. Never share your password
                        with anyone.
                    </span>

                </div>


                <!-- ACTIONS -->

                <div class="profile-form-actions">

                    <a
                        href="dashboard.php"
                        class="profile-button back-button"
                    >
                        ← Dashboard
                    </a>

                    <button
                        type="submit"
                        class="profile-button save-button"
                    >
                        💾 Save Changes
                    </button>

                </div>


            </form>


        </section>


    </div>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

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