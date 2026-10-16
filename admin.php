
<?php

session_start();

/* Check admin access */

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

/* Upload success message */

if (isset($_GET["upload"]) && $_GET["upload"] == "success") {
    $message = "PDF added successfully!";
}

/* ================= DELETE PDF ================= */

if (isset($_POST["delete_pdf"])) {

    $pdf_id = intval($_POST["pdf_id"]);

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

        $filePath = ($course == "BCA")
            ? "pdf/bca/" . $filename
            : "pdf/bcs/" . $filename;

        $deleteStmt = $conn->prepare(
            "DELETE FROM pdfs WHERE id = ?"
        );

        $deleteStmt->bind_param("i", $pdf_id);

        if ($deleteStmt->execute()) {

            if (file_exists($filePath)) {
                unlink($filePath);
            }

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

if (!$pdfResult) {
    die("Unable to load PDFs.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - E-Learning System</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        .pdf-search-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        .pdf-search-form select,
        .pdf-search-form input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .pdf-search-form input {
            flex: 1;
            min-width: 180px;
        }

        .pdf-search-form button,
        .pdf-search-form .reset-search {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .pdf-search-form .reset-search {
            background: #eee;
            color: #222;
        }

        .pdf-search-form button {
            cursor: pointer;
        }

        .no-search-results {
            display: none;
            margin: 15px 0;
        }

        @media (max-width: 600px) {
            .pdf-search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .pdf-search-form select,
            .pdf-search-form input,
            .pdf-search-form button,
            .pdf-search-form .reset-search {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>

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
                <a href="add_pdf.php">ADD PDF</a>
                <a href="feedback.php">FEEDBACK</a>
            </div>

        </nav>
    </header>

    <!-- Admin Dashboard -->

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

        <!-- Manage PDFs -->

        <div class="admin-box">

            <h2>MANAGE PDFs</h2>

            <!-- Course Filter and Subject Search -->

            <div class="pdf-search-form">

                <select id="courseFilter">
                    <option value="">All Courses</option>
                    <option value="BCA">BCA</option>
                    <option value="BCS">BCS</option>
                </select>

                <input
                    type="text"
                    id="subjectSearch"
                    placeholder="Search subject name..."
                    autocomplete="off"
                >

                <button type="button" id="resetSearch">
                    RESET
                </button>

            </div>

            <?php if ($pdfResult->num_rows > 0) { ?>

                <table class="admin-pdf-table">

                    <thead>
                        <tr>
                            <th>COURSE</th>
                            <th>YEAR</th>
                            <th>SUBJECT</th>
                            <th>PDF</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>

                    <tbody id="pdfTableBody">

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
                                    $folder = ($pdf["course"] == "BCA")
                                        ? "bca"
                                        : "bcs";
                                    ?>

                                    <a
                                        href="pdf/<?php echo $folder; ?>/<?php echo rawurlencode($pdf["filename"]); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        OPEN PDF
                                    </a>

                                </td>

                                <td>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this PDF?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="pdf_id"
                                            value="<?php echo (int) $pdf["id"]; ?>"
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

                    </tbody>

                </table>

                <p id="noSearchResults" class="no-search-results">
                    No matching PDFs found.
                </p>

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

    <!-- Search and Filter Logic -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const courseFilter = document.getElementById("courseFilter");
            const subjectSearch = document.getElementById("subjectSearch");
            const resetSearch = document.getElementById("resetSearch");
            const tableBody = document.getElementById("pdfTableBody");
            const noResults = document.getElementById("noSearchResults");

            if (!courseFilter || !subjectSearch || !resetSearch || !tableBody) {
                return;
            }

            const rows = Array.from(tableBody.querySelectorAll("tr"));

            function filterPDFs() {

                const selectedCourse = courseFilter.value.trim().toUpperCase();
                const searchText = subjectSearch.value.trim().toLowerCase();

                let visibleCount = 0;

                rows.forEach(function (row) {

                    const course = row.cells[0].textContent.trim().toUpperCase();
                    const subject = row.cells[2].textContent.trim().toLowerCase();

                    const courseMatches =
                        selectedCourse === "" || course === selectedCourse;

                    const subjectMatches =
                        subject.includes(searchText);

                    const showRow = courseMatches && subjectMatches;

                    row.style.display = showRow ? "" : "none";

                    if (showRow) {
                        visibleCount++;
                    }
                });

                if (noResults) {
                    noResults.style.display =
                        visibleCount === 0 ? "block" : "none";
                }
            }

            courseFilter.addEventListener("change", filterPDFs);
            subjectSearch.addEventListener("input", filterPDFs);

            resetSearch.addEventListener("click", function () {
                courseFilter.value = "";
                subjectSearch.value = "";
                filterPDFs();
            });

        });
    </script>

</body>
</html>

<?php
$conn->close();
?>
