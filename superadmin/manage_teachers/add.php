<?php
include '../../db_connect.php';

session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = hash('sha256', $_POST['password']); // Hash password
    $hire_date = date('Y-m-d H:i:s');

    // Insert data into database
    $query = "INSERT INTO Instructors (first_name, last_name, email, username, password_hash, hire_date) VALUES ('$first_name', '$last_name', '$email', '$username', '$password', '$hire_date')";
    if (mysqli_query($conn, $query)) {
        $message = "Teacher added successfully!";
        header("Location: ./");
        exit;
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
echo $message;
?>
