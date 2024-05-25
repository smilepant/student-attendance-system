<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle Edit Student Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $enrollment_date = $_POST['enrollment_date'];
    $student_id = $_POST['student_id'];

    $sql = "UPDATE Students SET first_name = ?, last_name = ?, email = ?, date_of_birth = ?, enrollment_date = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $date_of_birth, $enrollment_date, $student_id);

    if ($stmt->execute()) {
        $message = "Student updated successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch student information
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $sql = "SELECT * FROM Students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Student</h2>
        <?php include '../components/navbar.php'; ?> 
        <?php if (!empty($student)): ?>
            <form method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required><br>
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required><br>
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br>
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required><br>
                <label for="enrollment_date">Enrollment Date:</label>
                <input type="date" name="enrollment_date" value="<?php echo htmlspecialchars($student['enrollment_date']); ?>" required><br>
                <input type="submit" value="Save Changes">
            </form>
        <?php else: ?>
            <p>Student not found.</p>
        <?php endif; ?>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>
