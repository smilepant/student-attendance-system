<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $student_id = $_GET['id'];

    $sql = "DELETE FROM Students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        $message = "Student deleted successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

header("Location: ./"); 
exit;
?>
