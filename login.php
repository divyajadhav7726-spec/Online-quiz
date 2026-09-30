
<?php

session_start();

include "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Find user by email, including role
    $stmt = $conn->prepare(
        "SELECT id, full_name, email, password, role
         FROM users
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user["password"])) {

            // Create session
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            // Redirect according to user role
            if ($user["role"] === "admin") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }

            exit();

        } else {

            $message = "Incorrect password!";

        }

    } else {

        $message = "Email not registered!";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Smart Quiz</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .login-container {
            width: 380px;
            max-width: calc(100% - 70px);
            margin: 80px auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            font-size: 40px;
        }

        h1 {
            text-align: center;
            color: #172554;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .register-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .home-link {
            text-align: center;
            margin-top: 15px;
        }

        .home-link a {
            color: #172554;
            text-decoration: none;
        }

        .message {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="logo">🧠</div>

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Login to your Smart Quiz account
        </p>

        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" action="">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

        <div class="register-link">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </div>

        <div class="home-link">

            <a href="index.php">
                ← Back to Home
            </a>

        </div>

    </div>

</body>

</html>