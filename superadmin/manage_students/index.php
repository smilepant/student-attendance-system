<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle Add Student Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $enrollment_date = $_POST['enrollment_date'];

    $sql = "INSERT INTO Students (first_name, last_name, email, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $date_of_birth, $enrollment_date);

    if ($stmt->execute()) {
        $message = "Student added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing students
$sql = "SELECT * FROM Students";
$result = $conn->query($sql);
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Students</h2>
        <?php include '../components/navbar.php'; ?> 
        <!-- Add Student Form -->
        <h3>Add New Student</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" required><br>
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" required><br>
            <label for="email">Email:</label>
            <input type="email" name="email" required><br>
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" name="date_of_birth" required><br>
            <label for="enrollment_date">Enrollment Date:</label>
            <input type="date" name="enrollment_date" required><br>
            <input type="submit" value="Add Student">
        </form>

        <!-- Existing Students Table -->
        <h3>Existing Students</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Date of Birth</th>
                <th>Enrollment Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                    <td><?php echo htmlspecialchars($student['enrollment_date']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $student['student_id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $student['student_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>
