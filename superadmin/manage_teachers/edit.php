<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Check if instructor ID is provided in the URL
if (!isset($_GET['instructor_id'])) {
    // Redirect if instructor ID is missing
    echo "Id not found";
    exit;
}

$instructor_id = $_GET['instructor_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);

    $sql = "UPDATE Instructors SET first_name='$first_name', last_name='$last_name', email='$email', username='$username', password_hash='$password' WHERE instructor_id='$instructor_id'";
    $result = $conn->query($sql);

    if ($result) {
        $message = "Teacher updated successfully!";
        header("Location: ./");
    } else {
        $message = "Error: " . $conn->error;
    }
}

$sql = "SELECT * FROM Instructors WHERE instructor_id='$instructor_id'";
$result = $conn->query($sql);

// Check if instructor exists
if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    // Redirect if instructor is not found
    echo "instructor is not found";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Teacher</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Edit Teacher</h2>
        <?php include '../components/navbar.php'; ?> 
        <form method="post">
            <input type="hidden" name="instructor_id" value="<?= $teacher['instructor_id']; ?>">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" value="<?= $teacher['first_name']; ?>" required><br>
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" value="<?= $teacher['last_name']; ?>" required><br>
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?= $teacher['email']; ?>" required><br>
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?= $teacher['username']; ?>" required><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br>
            <input type="submit" value="Update Teacher">
        </form>
        <p class="message"><?= $message; ?></p>
    </div>
</body>

</html>
