<?php
// Include database connection file
include '../../db_connect.php';

// Initialize variables
$message = '';
$attendance = [];
$student_name = '';
$course_name = '';

// Validate input parameters
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$student_id || !$course_id) {
    $message = "Invalid student or course.";
} else {
    // Fetch student name
    $sql_student_name = "SELECT first_name, last_name FROM Students WHERE student_id = ?";
    $stmt_student_name = $conn->prepare($sql_student_name);
    $stmt_student_name->bind_param("i", $student_id);
    $stmt_student_name->execute();
    $result_student_name = $stmt_student_name->get_result();
    $row_student_name = $result_student_name->fetch_assoc();
    $student_name = $row_student_name['first_name'] . ' ' . $row_student_name['last_name'];
    $stmt_student_name->close();

    // Fetch course name
    $sql_course_name = "SELECT course_name FROM courses WHERE course_id = ?";
    $stmt_course_name = $conn->prepare($sql_course_name);
    $stmt_course_name->bind_param("i", $course_id);
    $stmt_course_name->execute();
    $result_course_name = $stmt_course_name->get_result();
    $row_course_name = $result_course_name->fetch_assoc();
    $course_name = $row_course_name['course_name'];
    $stmt_course_name->close();

    // Fetch attendance records for the selected student and course
    $sql_attendance = "SELECT attendance_date, status
                       FROM Attendance
                       WHERE student_id = ? AND course_id = ?
                       ORDER BY attendance_date ASC";

    $stmt_attendance = $conn->prepare($sql_attendance);
    if (!$stmt_attendance) {
        $message = "Prepare failed: " . mysqli_error($conn);
    } else {
        $stmt_attendance->bind_param("ii", $student_id, $course_id);
        $stmt_attendance->execute();
        $result_attendance = $stmt_attendance->get_result();

        if ($result_attendance->num_rows > 0) {
            $attendance = $result_attendance->fetch_all(MYSQLI_ASSOC);
        } else {
            $message = "No attendance records found for the selected student.";
        }

        $stmt_attendance->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student Attendance</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="container">
    <h2>Attendance Records for <?php echo htmlspecialchars($student_name); ?> in <?php echo htmlspecialchars($course_name); ?></h2>
    <?php include '../components/navbar.php'; ?>

    <?php if (!empty($attendance)) : ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>S.N</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sn = 1;
                    $total_present = 0;
                    foreach ($attendance as $record) :
                        if ($record['status'] == 'Present') {
                            $total_present++;
                        }
                    ?>
                        <tr>
                            <td><?= $sn++ ?></td>
                            <td><?= htmlspecialchars($record['attendance_date']) ?></td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>Total Days: <?= $sn-1 ?></strong></td>
                        <td><strong>Total Present</strong></td>
                        <td><strong><?= $total_present ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</body>

</html>
