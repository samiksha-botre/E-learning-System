<?php

session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "elearning");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

if (isset($_POST["add_pdf"])) {

    $course = $_POST["course"];
    $year = $_POST["year"];
    $subject = trim($_POST["subject"]);

    if ($course == "" || $year == "" || $subject == "") {

        $message = "Please fill all fields.";

    } elseif (!isset($_FILES["pdf"]) || $_FILES["pdf"]["error"] != 0) {

        $message = "Please select a PDF.";

    } else {

        $fileName = $_FILES["pdf"]["name"];
        $tmpName = $_FILES["pdf"]["tmp_name"];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension != "pdf") {

            $message = "Only PDF files are allowed.";

        } else {

            $newFileName = time() . "_" . basename($fileName);

            if ($course == "BCA") {
                $uploadFolder = "pdf/bca/";
            } elseif ($course == "BCS") {
                $uploadFolder = "pdf/bcs/";
            } else {
                $uploadFolder = "";
                $message = "Invalid course selected.";
            }

            if ($uploadFolder != "") {

                if (!is_dir($uploadFolder)) {

                    $message = "Upload folder not found.";

                } elseif (move_uploaded_file($tmpName, $uploadFolder . $newFileName)) {

                    $stmt = $conn->prepare(
                        "INSERT INTO pdfs (course, year, subject, filename)
                         VALUES (?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "ssss",
                        $course,
                        $year,
                        $subject,
                        $newFileName
                    );

                    if ($stmt->execute()) {

                        $stmt->close();
                        $conn->close();

                        header("Location: admin.php?upload=success");
                        exit();

                    } else {

                        unlink($uploadFolder . $newFileName);
                        $message = "Database error.";

                    }

                    $stmt->close();

                } else {

                    $message = "PDF upload failed.";

                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add PDF - E-Learning System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header>
        <nav>

            <div class="logout">
                <a href="index.php">LOGOUT</a>
            </div>

            <div class="menu">
                <a href="admin.php">ADMIN DASHBOARD</a>
                <a href="feedback.php">FEEDBACK</a>
            </div>

        </nav>
    </header>

    <div class="admin-container">

        <h1>ADD PDF</h1>

        <p class="admin-welcome">
            Upload learning resources for BCA and BCS students.
        </p>

        <?php if ($message != "") { ?>
            <p class="admin-message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <div class="admin-box">

            <h2>ADD PDF</h2>

            <form method="POST" enctype="multipart/form-data">

                <label for="course">Course</label>

                <select id="course" name="course" required>
                    <option value="">Select Course</option>
                    <option value="BCA">BCA</option>
                    <option value="BCS">BCS</option>
                </select>

                <label for="year">Year</label>

                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <option value="FY">First Year</option>
                    <option value="SY">Second Year</option>
                    <option value="TY">Third Year</option>
                </select>

                <label for="subject">Subject Name</label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    placeholder="Enter subject name"
                    required
                >

                <label for="pdf">Select PDF</label>

                <input
                    type="file"
                    id="pdf"
                    name="pdf"
                    accept=".pdf,application/pdf"
                    required
                >

                <button type="submit" name="add_pdf">
                    ADD PDF
                </button>

            </form>

            <p>
                <a href="admin.php">Back to Admin Dashboard</a>
            </p>

        </div>

    </div>

    <footer>
        <p>&copy; 2026 E-Learning System. All Rights Reserved.</p>
    </footer>

</body>

</html>

<?php
$conn->close();
?>