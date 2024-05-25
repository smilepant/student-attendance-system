<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle Add Semester Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $semester_name = $_POST['semester_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "INSERT INTO Semesters (semester_name, start_date, end_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $semester_name, $start_date, $end_date);

    if ($stmt->execute()) {
        $message = "Semester added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing semesters
$sql = "SELECT * FROM Semesters";
$result = $conn->query($sql);
$semesters = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Semesters</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Semesters</h2>
        <?php include '../components/navbar.php'; ?> 
        <!-- Add Semester Form -->
        <h3>Add New Semester</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <label for="semester_name">Semester Name:</label>
            <input type="text" name="semester_name" required><br>
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required><br>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" required><br>
            <input type="submit" value="Add Semester">
        </form>

        <!-- Existing Semesters Table -->
        <h3>Existing Semesters</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Semester Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($semesters as $semester): ?>
                <tr>
                    <td><?php echo htmlspecialchars($semester['semester_id']); ?></td>
                    <td><?php echo htmlspecialchars($semester['semester_name']); ?></td>
                    <td><?php echo htmlspecialchars($semester['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($semester['end_date']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $semester['semester_id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $semester['semester_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>
