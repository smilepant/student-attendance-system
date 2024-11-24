<?php
session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: login.php");
    exit;
}
include '../db_connect.php';

// Fetch data from the database
$totalStudents = mysqli_query($conn, "SELECT COUNT(*) as total FROM students");
$totalAttendance = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE status = 'present'");
$totalInstructor = mysqli_query($conn, "SELECT COUNT(*) as total FROM instructors");

$studentsCount = mysqli_fetch_assoc($totalStudents)['total'];
$instrucktorsCount = mysqli_fetch_assoc($totalInstructor)['total'];
$attendanceCount = mysqli_fetch_assoc($totalAttendance)['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>SuperAdmin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/supdashboard.style.css">

</head>

<body>
    <div class="container">
        <h2>SuperAdmin Dashboard</h2>
        <?php include './components/navbar.php'; ?>
        <div class="dashboard">
            <div class="card">
                <h3>Total Students</h3>
                <p><?= $studentsCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Instructors</h3>
                <p><?= $instrucktorsCount; ?></p>
            </div>
            <div class="card">
                <h3>Attendance Recorded</h3>
                <p><?= $attendanceCount; ?></p>
            </div>


        </div>
    </div>
</body>

</html>