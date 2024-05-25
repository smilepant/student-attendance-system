<?php
include '../../../db_connect.php';
session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the assignment
    $sql = "DELETE FROM Course_Instructors WHERE course_instructor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Assignment deleted successfully!";
    } else {
        $message = "Error deleting assignment: " . $stmt->error;
    }

    $stmt->close();
} else {
    die("ID not provided.");
}

header("Location: index.php?message=" . urlencode($message));
exit;
