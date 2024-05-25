<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

$course_id = $_GET['course_id'];


$sql = "DELETE FROM Courses WHERE course_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);

if ($stmt->execute()) {
    $message = "Course deleted successfully!";
} else {
    $message = "Error deleting course: " . $stmt->error;
}
$stmt->close();


// Redirect back to manage courses page after deletion
header("Location: ./");
exit;
