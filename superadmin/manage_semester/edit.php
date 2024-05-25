<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Check if semester ID is provided
if (isset($_GET['id'])) {
    $semester_id = $_GET['id'];

    // Fetch semester details
    $sql = "SELECT * FROM Semesters WHERE semester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $semester = $result->fetch_assoc();
    $stmt->close();
} else {
    $message = "Invalid semester ID.";
}

// Handle Edit Semester Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $semester_name = $_POST['semester_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "UPDATE Semesters SET semester_name = ?, start_date = ?, end_date = ? WHERE semester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $semester_name, $start_date, $end_date, $semester_id);

    if ($stmt->execute()) {
        $message = "Semester updated successfully!";
        header("Location: ./");
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Semester</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Semester</h2>
        <?php include '../components/navbar.php'; ?> 
        <?php if ($semester) : ?>
            <!-- Edit Semester Form -->
            <form method="post">
                <input type="hidden" name="action" value="edit">
                <label for="semester_name">Semester Name:</label>
                <input type="text" name="semester_name" value="<?php echo htmlspecialchars($semester['semester_name']); ?>" required><br>
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($semester['start_date']); ?>" required><br>
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($semester['end_date']); ?>" required><br>
                <input type="submit" value="Update Semester">
            </form>
        <?php else : ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>
