<?php

session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "elearning");

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Find user by username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Check password
        if (password_verify($password, $user["password"])) {

            // Store user information in session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            // Check role
            if ($user["role"] == "admin") {

                header("Location: admin.php");
                exit();

            } else {

                header("Location: dashboard.html");
                exit();
            }

        } else {

            $message = "Invalid username or password.";

        }

    } else {

        $message = "Invalid username or password.";

    }

    $stmt->close();
    $conn->close();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Login - E-Learning System</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="login-page">

    <div class="login-box">

        <h2>LOGIN</h2>

        <?php
        if ($message != "") {
            echo "<p style='color:red; text-align:center;'>$message</p>";
        }
        ?>

        <form id="loginForm" method="POST" action="login.php">

            <label for="username">Username</label>

            <input type="text" id="username" name="username">


            <label for="password">Password</label>

            <input type="password" id="password" name="password">


            <input type="submit" value="LOGIN">

        </form>

    </div>


    <script>

        document.getElementById("loginForm").addEventListener("submit", function(event) {

            let username = document.getElementById("username").value.trim();

            let password = document.getElementById("password").value;


            // Username validation

            if (username === "") {

                alert("Please enter your username.");

                event.preventDefault();

                return;

            }


            if (username.length < 4) {

                alert("Username must contain at least 4 characters.");

                event.preventDefault();

                return;

            }


            // Password validation

            if (password === "") {

                alert("Please enter your password.");

                event.preventDefault();

                return;

            }


            if (password.length < 6) {

                alert("Password must contain at least 6 characters.");

                event.preventDefault();

                return;

            }

        });

    </script>

</body>

</html>