<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $mobile = $_POST["mobile"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check password
    if ($password != $confirm_password) {

        $message = "Passwords do not match!";

    } else {

        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered!";

        } else {

            // Secure password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, mobile, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $mobile,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed!";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Smart Quiz</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .container {
            width: 400px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #172554;
        }

        p {
            text-align: center;
            color: #666;
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

        .login-link {
            margin-top: 20px;
            text-align: center;
        }

        .login-link a {
            color: #2563eb;
            text-decoration: none;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>🧠 Create Account</h1>

        <p>Register for Smart Online Quiz System</p>

        <?php if ($message != "") { ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST" action="">

            <label>Full Name</label>
            <input type="text"
                   name="full_name"
                   placeholder="Enter your full name"
                   required>

            <label>Email</label>
            <input type="email"
                   name="email"
                   placeholder="Enter your email"
                   required>

            <label>Mobile Number</label>
            <input type="tel"
                   name="mobile"
                   placeholder="Enter your mobile number"
                   required>

            <label>Password</label>
            <input type="password"
                   name="password"
                   placeholder="Create password"
                   required>

            <label>Confirm Password</label>
            <input type="password"
                   name="confirm_password"
                   placeholder="Confirm password"
                   required>

            <button type="submit">Create Account</button>

        </form>

        <div class="login-link">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </div>

</body>
</html>