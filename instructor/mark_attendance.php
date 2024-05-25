<?php
include '../db_connect.php';
session_start();

// Redirect if user is not logged in as an instructor
if (!isset($_SESSION['instructor_id'])) {
    header("Location: ./login.php");
    exit;
}

$instructor_id = $_SESSION['instructor_id'];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    // Fetch course information
    $sql_course = "SELECT course_name FROM Courses WHERE course_id = ?";
    $stmt_course = $conn->prepare($sql_course);
    if (!$stmt_course) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt_course->bind_param("i", $course_id);
    if (!$stmt_course->execute()) {
        die("Execute failed: " . $stmt_course->error);
    }

    $result_course = $stmt_course->get_result();
    if (!$result_course) {
        die("Get result failed: " . $conn->error);
    }

    $course_info = $result_course->fetch_assoc();
    $course_name = $course_info['course_name'];

    // Fetch instructor's name
    $sql_instructor = "SELECT first_name, last_name FROM Instructors WHERE instructor_id = ?";
    $stmt_instructor = $conn->prepare($sql_instructor);
    if (!$stmt_instructor) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt_instructor->bind_param("i", $instructor_id);
    if (!$stmt_instructor->execute()) {
        die("Execute failed: " . $stmt_instructor->error);
    }

    $result_instructor = $stmt_instructor->get_result();
    if (!$result_instructor) {
        die("Get result failed: " . $conn->error);
    }

    $instructor_info = $result_instructor->fetch_assoc();
    $instructor_name = $instructor_info['first_name'] . " " . $instructor_info['last_name'];

    // Fetch students enrolled in the selected course
    $sql_students = "SELECT Students.student_id, Students.first_name, Students.last_name 
    FROM Students 
    INNER JOIN Enrollments ON Students.student_id = Enrollments.student_id
    WHERE Enrollments.course_id = ?
    ORDER BY Students.first_name";

    $stmt_students = $conn->prepare($sql_students);
    if (!$stmt_students) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt_students->bind_param("i", $course_id);
    if (!$stmt_students->execute()) {
        die("Execute failed: " . $stmt_students->error);
    }

    $result_students = $stmt_students->get_result();
    if (!$result_students) {
        die("Get result failed: " . $conn->error);
    }

    $students = $result_students->fetch_all(MYSQLI_ASSOC);
} else {
    // Redirect with a message if course_id is not provided
    header("Location: instructor_dashboard.php?error=Course ID not provided");
    exit;
}

// Get today's date
$today = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Mark Attendance</h1>
        <?php include './components/navbar.php'; ?> 

        <!-- Display Course and Instructor Information -->
        <h2>Course: <?= $course_name ?></h2>
        <h3>Instructor: <?= $instructor_name ?></h3>
        <!-- Display Students -->
        <form method="post" action="submit_attendance.php">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <input type="hidden" name="attendance_date" value="<?= $today ?>">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>S.N</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Present</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $num = 1; foreach ($students as $student) : ?>
                        <tr>
                            <td><?= $num ?></td>
                            <td><?= $student['student_id'] ?></td>
                            <td><?= $student['first_name'] . " " .  $student['last_name'] ?></td>

                            <td><input type="checkbox" name="present[]" value="<?= $student['student_id'] ?>"></td>
                        </tr>
                    <?php $num++; endforeach; ?>
                </tbody>
            </table>
            <input type="submit" value="Submit Attendance"/>
            <p><?= $_GET['success'] ?></p>
        </form>
    </div>
</body>

</html>