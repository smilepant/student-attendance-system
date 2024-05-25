<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve instructor ID from POST data
    $instructor_id = isset($_POST['instructor_id']) ? $_POST['instructor_id'] : null;

    // Prepare and execute delete query
    $sql = "DELETE FROM Instructors WHERE instructor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $instructor_id);
    if ($stmt->execute()) {
        $message = "Teacher deleted successfully!";
        header("Location: ./");
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch teacher data for confirmation message
$instructor_id = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : null;
$sql = "SELECT * FROM Instructors WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Teacher</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Delete Teacher</h2>
        <p>Are you sure you want to delete the following teacher?</p>
        <p><strong>ID:</strong> <?= $teacher['instructor_id']; ?></p>
        <p><strong>Name:</strong> <?= $teacher['first_name'] . ' ' . $teacher['last_name']; ?></p>
        <form method="post">
            <input type="hidden" name="instructor_id" value="<?= $teacher['instructor_id']; ?>">
            <input type="submit" value="Yes, Delete Teacher">
        </form>
        <p class="message"><?= $message; ?></p>
    </div>
</body>
</html>
