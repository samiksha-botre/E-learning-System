<?php
session_start();

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $username = trim($_POST["username"]);
    $gender = $_POST["gender"] ?? "";
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];


    /* ================= DATABASE CONNECTION ================= */

    $conn = new mysqli("localhost", "root", "", "elearning");

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }


    /* ================= CHECK USERNAME ================= */

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $message = "Username already exists.";
        $messageType = "error";

    } else {

        $stmt->close();


        /* ================= CHECK EMAIL ================= */

        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already exists.";
            $messageType = "error";

        } else {

            $stmt->close();


            /* ================= HASH PASSWORD ================= */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* ================= INSERT USER ================= */

            $stmt = $conn->prepare(
                "INSERT INTO users
                (first_name, last_name, username, gender, email, address, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssss",
                $firstName,
                $lastName,
                $username,
                $gender,
                $email,
                $address,
                $hashedPassword
            );


           if ($stmt->execute()) {

    $newUserId = $conn->insert_id;

    session_start();

    $_SESSION["user_id"] = $newUserId;
    $_SESSION["username"] = $username;
    $_SESSION["role"] = "user";

    header("Location: dashboard.html");
    exit();

}
            else {

                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }
        }
    }

    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Register - E-Learning System</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="register-page">

    <div class="register-box">

        <h1>E-Learning System</h1>

        <h2>REGISTER</h2>


        <?php if ($message != "") { ?>

            <p style="color: red; text-align: center;">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php } ?>


        <form id="registerForm" method="POST" action="register.php">

            <label for="firstName">First Name</label>

            <input
                type="text"
                id="firstName"
                name="firstName"
            >


            <label for="lastName">Last Name</label>

            <input
                type="text"
                id="lastName"
                name="lastName"
            >


            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
            >


            <label>Gender</label>

            <input
                type="radio"
                id="male"
                name="gender"
                value="Male"
            >

            <label for="male">Male</label>


            <input
                type="radio"
                id="female"
                name="gender"
                value="Female"
            >

            <label for="female">Female</label>


            <label for="email">Email</label>

            <input
                type="text"
                id="email"
                name="email"
            >


            <label for="address">Address</label>

            <input
                type="text"
                id="address"
                name="address"
            >


            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
            >


            <label for="confirmPassword">Confirm Password</label>

            <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
            >


            <input
                type="submit"
                value="REGISTER"
            >

        </form>

    </div>


    <script>

        document.getElementById("registerForm").addEventListener("submit", function(event) {

            let firstName = document.getElementById("firstName").value.trim();

            let lastName = document.getElementById("lastName").value.trim();

            let username = document.getElementById("username").value.trim();

            let email = document.getElementById("email").value.trim();

            let address = document.getElementById("address").value.trim();

            let password = document.getElementById("password").value;

            let confirmPassword = document.getElementById("confirmPassword").value;

            let gender = document.querySelector('input[name="gender"]:checked');


            /* ================= FIRST NAME VALIDATION ================= */

            if (firstName === "") {

                alert("Please enter your first name.");

                event.preventDefault();

                return;
            }

            if (!/^[A-Za-z]+$/.test(firstName)) {

                alert("First name should contain only letters.");

                event.preventDefault();

                return;
            }


            /* ================= LAST NAME VALIDATION ================= */

            if (lastName === "") {

                alert("Please enter your last name.");

                event.preventDefault();

                return;
            }

            if (!/^[A-Za-z]+$/.test(lastName)) {

                alert("Last name should contain only letters.");

                event.preventDefault();

                return;
            }


            /* ================= USERNAME VALIDATION ================= */

            if (username === "") {

                alert("Please enter a username.");

                event.preventDefault();

                return;
            }

            if (username.length < 4) {

                alert("Username must contain at least 4 characters.");

                event.preventDefault();

                return;
            }


            /* ================= GENDER VALIDATION ================= */

            if (!gender) {

                alert("Please select your gender.");

                event.preventDefault();

                return;
            }


            /* ================= EMAIL VALIDATION ================= */

            if (email === "") {

                alert("Please enter your email.");

                event.preventDefault();

                return;
            }

            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(email)) {

                alert("Please enter a valid email address.");

                event.preventDefault();

                return;
            }


            /* ================= ADDRESS VALIDATION ================= */

            if (address === "") {

                alert("Please enter your address.");

                event.preventDefault();

                return;
            }


            /* ================= PASSWORD VALIDATION ================= */

            if (password === "") {

                alert("Please enter a password.");

                event.preventDefault();

                return;
            }

            if (password.length < 6) {

                alert("Password must contain at least 6 characters.");

                event.preventDefault();

                return;
            }


            /* ================= CONFIRM PASSWORD ================= */

            if (confirmPassword === "") {

                alert("Please confirm your password.");

                event.preventDefault();

                return;
            }

            if (password !== confirmPassword) {

                alert("Password and Confirm Password do not match.");

                event.preventDefault();

                return;
            }


            /* ================= ALLOW PHP SUBMISSION ================= */

            // Validation passed.
            // Form will now be submitted to register.php.

        });

    </script>

</body>

</html>