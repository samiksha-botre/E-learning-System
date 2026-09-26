<?php

session_start();

/* Check whether logged-in user is admin */

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}


/* Database connection */

$conn = new mysqli("localhost", "root", "", "elearning");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


$message = "";


/* ================= ADD PDF ================= */

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


        /* Allow only PDF */

        if ($fileExtension != "pdf") {

            $message = "Only PDF files are allowed.";

        } else {

            /* Create unique filename */

            $newFileName = time() . "_" . basename($fileName);


            /* Select folder */

            if ($course == "BCA") {
                $uploadFolder = "pdf/bca/";
            } else {
                $uploadFolder = "pdf/bcs/";
            }


            /* Move PDF */

            if (move_uploaded_file($tmpName, $uploadFolder . $newFileName)) {

                /* Store PDF information in database */

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

                    $message = "PDF added successfully!";

                } else {

                    /* If database insertion fails, remove uploaded file */

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


/* ================= DELETE PDF ================= */

if (isset($_POST["delete_pdf"])) {

    $pdf_id = intval($_POST["pdf_id"]);


    /* Get PDF information */

    $stmt = $conn->prepare(
        "SELECT course, filename FROM pdfs WHERE id = ?"
    );

    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows == 1) {

        $pdf = $result->fetch_assoc();

        $course = $pdf["course"];
        $filename = $pdf["filename"];


        /* Select folder */

        if ($course == "BCA") {
            $filePath = "pdf/bca/" . $filename;
        } else {
            $filePath = "pdf/bcs/" . $filename;
        }


        /* Delete physical PDF file */

        if (file_exists($filePath)) {
            unlink($filePath);
        }


        /* Delete database record */

        $deleteStmt = $conn->prepare(
            "DELETE FROM pdfs WHERE id = ?"
        );

        $deleteStmt->bind_param("i", $pdf_id);

        if ($deleteStmt->execute()) {

            $message = "PDF deleted successfully!";

        } else {

            $message = "Unable to delete PDF.";

        }

        $deleteStmt->close();

    } else {

        $message = "PDF not found.";

    }

    $stmt->close();
}


/* ================= GET ALL PDFs ================= */

$pdfResult = $conn->query(
    "SELECT id, course, year, subject, filename, uploaded_at
     FROM pdfs
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Admin Dashboard - E-Learning System</title>

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

                <a href="admin.php">ADMIN DASHBOARD</a>

                <a href="feedback.html">FEEDBACK</a>

            </div>

        </nav>

    </header>


    <!-- Admin Container -->

    <div class="admin-container">

        <h1>ADMIN DASHBOARD</h1>

        <p class="admin-welcome">
            Welcome Admin! Manage the learning resources from here.
        </p>


        <?php if ($message != "") { ?>

            <p class="admin-message">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php } ?>


        <!-- ================= ADD PDF ================= -->

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
                    accept=".pdf"
                    required
                >


                <button type="submit" name="add_pdf">
                    ADD PDF
                </button>

            </form>

        </div>


        <!-- ================= MANAGE PDFs ================= -->

        <div class="admin-box">

            <h2>MANAGE PDFs</h2>

            <?php if ($pdfResult->num_rows > 0) { ?>

                <table class="admin-pdf-table">

                    <tr>
                        <th>COURSE</th>
                        <th>YEAR</th>
                        <th>SUBJECT</th>
                        <th>PDF</th>
                        <th>ACTION</th>
                    </tr>


                    <?php while ($pdf = $pdfResult->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($pdf["course"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pdf["year"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pdf["subject"]); ?>
                            </td>

                            <td>

                                <?php
                                if ($pdf["course"] == "BCA") {
                                    $folder = "bca";
                                } else {
                                    $folder = "bcs";
                                }
                                ?>

                                <a
                                    href="pdf/<?php echo $folder; ?>/<?php echo urlencode($pdf["filename"]); ?>"
                                    target="_blank"
                                >
                                    OPEN PDF
                                </a>

                            </td>

                            <td>

                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this PDF?');">

                                    <input
                                        type="hidden"
                                        name="pdf_id"
                                        value="<?php echo $pdf["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_pdf"
                                        class="delete-button"
                                    >
                                        DELETE
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php } ?>

                </table>

            <?php } else { ?>

                <p class="no-pdf">
                    No PDFs have been uploaded yet.
                </p>

            <?php } ?>

        </div>

    </div>


    <!-- Footer -->

    <footer>

        <p>&copy; 2026 E-Learning System. All Rights Reserved.</p>

    </footer>

</body>

</html>

<?php

$conn->close();

?>