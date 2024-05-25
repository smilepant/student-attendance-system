<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Check if semester ID is provided
if (isset($_GET['id'])) {
    $semester_id = $_GET['id'];

    // Delete semester
    $sql = "DELETE FROM Semesters WHERE semester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $semester_id);

    if ($stmt->execute()) {
        $message = "Semester deleted successfully!";
        header("Location: ./");
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $message = "Invalid semester ID.";
}

header("Location: manage_semesters.php?message=" . urlencode($message));
exit;
?>
