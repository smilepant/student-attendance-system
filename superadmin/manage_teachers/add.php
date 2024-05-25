<?php
// Include database connection file
include '../../db_connect.php';

// Start session
session_start();

// Check if superadmin is logged in, otherwise redirect to login page
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Initialize message variable
$message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
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
