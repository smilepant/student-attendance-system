<?php
include '../../../db_connect.php';
session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ./login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $enrollment_id = $_GET['id'];

    // Delete the enrollment record from the database
    $sql_delete = "DELETE FROM Enrollments WHERE enrollment_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $enrollment_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Redirect back to the page where enrollments are listed
    header("Location: ./");
    exit;
}
?>
