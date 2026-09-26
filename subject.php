<?php
$conn = new mysqli("localhost", "root", "", "elearning");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function showUploadedPDFs($conn, $course, $year)
{
    $stmt = $conn->prepare(
        "SELECT id, subject, filename
         FROM pdfs
         WHERE course = ? AND year = ?
         ORDER BY id ASC"
    );

    $stmt->bind_param("ss", $course, $year);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $subject = htmlspecialchars($row["subject"]);
        $filename = htmlspecialchars($row["filename"]);
        $id = $row["id"];

        if ($course == "BCA") {
            $folder = "bca";
        } else {
            $folder = "bcs";
        }

        echo '<tr>';
        echo '<td>' . $subject . '</td>';
        echo '<td><a href="pdf/' . $folder . '/' . $filename . '" target="_blank">OPEN PDF</a></td>';
        echo '<td><a href="note.php?id=' . $id . '" class="note-button">NOTE</a></td>';
        echo '</tr>';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Subjects - E-Learning System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <nav>
        <div class="logout">
            <a href="index.php">LOGOUT</a>
        </div>

        <div class="menu">
            <a href="dashboard.html">DASHBOARD</a>
            <a href="subject.php">SUBJECT</a>
            <a href="feedback.html">FEEDBACK</a>
        </div>
    </nav>
</header>

<h1>SUBJECTS</h1>


<!-- ================= BCA ================= -->

<table class="subject-table">

    <tr class="stream-row">
        <th colspan="3">BCA</th>
    </tr>


    <!-- BCA First Year -->

    <tr class="year-row">
        <th colspan="3">First Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>

   

    <?php showUploadedPDFs($conn, "BCA", "FY"); ?>


    <!-- BCA Second Year -->

    <tr class="year-row">
        <th colspan="3">Second Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>

    

    <?php showUploadedPDFs($conn, "BCA", "SY"); ?>


    <!-- BCA Third Year -->

    <tr class="year-row">
        <th colspan="3">Third Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>

   

    <?php showUploadedPDFs($conn, "BCA", "TY"); ?>

</table>


<!-- ================= BCS ================= -->

<table class="subject-table">

    <tr class="stream-row">
        <th colspan="3">BCS</th>
    </tr>


    <!-- BCS First Year -->

    <tr class="year-row">
        <th colspan="3">First Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>

    
    <?php showUploadedPDFs($conn, "BCS", "FY"); ?>


    <!-- BCS Second Year -->

    <tr class="year-row">
        <th colspan="3">Second Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>

   

    <?php showUploadedPDFs($conn, "BCS", "SY"); ?>


    <!-- BCS Third Year -->

    <tr class="year-row">
        <th colspan="3">Third Year</th>
    </tr>

    <tr class="heading-row">
        <th>SUBJECT</th>
        <th>PDF</th>
        <th>NOTE</th>
    </tr>


    <?php showUploadedPDFs($conn, "BCS", "TY"); ?>

</table>

</body>

</html>

<?php
$conn->close();
?>