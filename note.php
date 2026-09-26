<?php
$conn = new mysqli("localhost", "root", "", "elearning");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$id = $_GET["id"] ?? 0;

$stmt = $conn->prepare("SELECT subject FROM pdfs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Subject not found.");
}

$row = $result->fetch_assoc();
$subject = $row["subject"];

$noteKey = "uploaded-note-" . $id;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Notes - <?php echo htmlspecialchars($subject); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="notes-page">

<div class="notes-box">

    <h1>
        <?php echo htmlspecialchars($subject); ?> - MY NOTES
    </h1>

    <textarea
        id="notes"
        placeholder="Write your notes here..."
    ></textarea>

    <br>

    <button onclick="saveNotes()">SAVE NOTES</button>

    <a href="subject.php">BACK TO SUBJECT</a>

</div>

<script>

function saveNotes() {

    localStorage.setItem(
        "<?php echo $noteKey; ?>",
        document.getElementById("notes").value
    );

    alert("Notes saved successfully!");
}

document.getElementById("notes").value =
    localStorage.getItem("<?php echo $noteKey; ?>") || "";

</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>