<?php
$conn = new mysqli("localhost", "root", "", "elearning");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $feedback = trim($_POST["feedback"]);

    if ($feedback == "") {
        $message = "Please enter your feedback.";
    } else {

        $stmt = $conn->prepare("INSERT INTO feedback (feedback) VALUES (?)");
        $stmt->bind_param("s", $feedback);

        if ($stmt->execute()) {
            $message = "Feedback submitted successfully!";
        } else {
            $message = "Failed to submit feedback.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Feedback - E-Learning System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Header -->
    <header>
        <nav>
            <div class="logout">
                <a href="index.php">LOGOUT</a>
            </div>

            <div class="menu">
                <a href="dashboard.html">DASHBOARD</a>
                <a href="subject.php">SUBJECT</a>
                <a href="feedback.php">FEEDBACK</a>
            </div>
        </nav>
    </header>

    <!-- Feedback Page -->

    <div class="feedback-container">

        <h1>FEEDBACK</h1>

        <div class="feedback-content">

            <!-- Feedback Section -->
            <div class="feedback-box">

                <h2>Give Your Feedback</h2>

                <?php if ($message != ""): ?>
                    <p><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <form method="POST" action="feedback.php">

                    <textarea
                        name="feedback"
                        placeholder="Write your feedback here..."
                        required></textarea>

                    <button type="submit">SUBMIT</button>

                </form>

            </div>

            <!-- Developer Information -->
            <div class="developer-box">

                <h2>Developer Information</h2>

                <p><strong>Name:</strong> Samiksha Botre</p>

                <p><strong>Email:</strong> samiksha01@gmail.com</p>

                <p><strong>Phone:</strong> +91 0987654321</p>

                <br>

                <p><strong>Name:</strong> Aarti Lavhe</p>

                <p><strong>Email:</strong> aarti02@gmail.com</p>

                <p><strong>Phone:</strong> +91 5674876543</p>

            </div>

        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 E-Learning System. All Rights Reserved.</p>
    </footer>

</body>

</html>