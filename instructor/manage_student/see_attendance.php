<?php
// Include database connection file
include '../../db_connect.php';

// Initialize variables
$message = '';
$attendance = [];
$course_name = '';

// Validate input parameters
$filter_semester = isset($_GET['sem_id']) ? $_GET['sem_id'] : null;
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

// Check if both semester and course ID are provided
if (!$filter_semester || !$course_id) {
    $message = "Please select both semester and course.";
} else {
    // Fetch course name
    $sql_course_name = "SELECT course_name FROM courses WHERE course_id = ?";
    $stmt_course_name = $conn->prepare($sql_course_name);
    $stmt_course_name->bind_param("i", $course_id);
    $stmt_course_name->execute();
    $result_course_name = $stmt_course_name->get_result();
    $row_course_name = $result_course_name->fetch_assoc();
    $course_name = $row_course_name['course_name'];
    $stmt_course_name->close();

    // Fetch attendance records for the selected course ID
    $sql_attendance = "SELECT Students.first_name, Students.student_id, Students.last_name, Attendance.attendance_date, Attendance.status
                        FROM Attendance
                        JOIN Students ON Attendance.student_id = Students.student_id
                        WHERE Attendance.course_id = ?
                        ORDER BY Attendance.attendance_date ASC";

    $stmt_attendance = $conn->prepare($sql_attendance);

    if (!$stmt_attendance) {
        // Error handling for prepare() method failure
        $message = "Prepare failed: " . mysqli_error($conn);
    } else {
        $stmt_attendance->bind_param("i", $course_id);
        $stmt_attendance->execute();
        $result_attendance = $stmt_attendance->get_result();

        // Check if attendance records are found
        if ($result_attendance->num_rows > 0) {
            $attendance = $result_attendance->fetch_all(MYSQLI_ASSOC);
        } else {
            $message = "No attendance records found for the selected course.";
        }

        $stmt_attendance->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Check Student Attendance</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="container">
    <h2>Check Student Attendance</h2>
    <?php include '../components/navbar.php'; ?>

    <?php if (!empty($attendance)) : ?>
        <h3>Attendance Records for <?php echo $course_name; ?></h3>
        <a class="btn" href="generate_csv.php?course_id=<?= $course_id ?>&sem_id=<?= $filter_semester ?>">
            Export to CSV
        </a>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>S.N</th>
                        <th>Date</th>
                        <?php $unique_students = array_unique(array_map(function ($record) {
                            return $record['first_name'] . ' ' . $record['last_name'];
                        }, $attendance)); ?>
                        <?php foreach ($unique_students as $student) : ?>
                            <?php
                            $student_id = '';
                            foreach ($attendance as $record) {
                                if ($record['first_name'] . ' ' . $record['last_name'] == $student) {
                                    $student_id = $record['student_id'];
                                    break;
                                }
                            }
                            ?>
                            <th>
                                <a href="student_attendance.php?student_id=<?= $student_id ?>&course_id=<?= $course_id ?>">
                                    <?= htmlspecialchars($student) ?>
                                </a>
                            </th>
                        <?php endforeach; ?>

                    </tr>
                </thead>
                <tbody>

                    <?php $dates = array_unique(array_column($attendance, 'attendance_date')); ?>
                    <?php $sn = 1 ?>
                    <?php foreach ($dates as $date) : ?>
                        <tr>
                            <td><?= $sn++; ?></td>
                            <td><?= $date ?></td>
                            <?php foreach ($unique_students as $student) : ?>
                                <?php $status = ''; ?>
                                <?php foreach ($attendance as $record) : ?>
                                    <?php if ($record['attendance_date'] == $date && $record['first_name'] . ' ' . $record['last_name'] == $student) : ?>
                                        <?php $status = htmlspecialchars($record['status']); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td><?= $status ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Total Days: <?= $sn - 1 ?></td>
                        <td>Total Attendance</td>
                        <?php
                        // Initialize array to hold total attendance count for each student
                        $total_attendance = array_fill_keys($unique_students, 0);
                        foreach ($attendance as $record) {
                            if ($record['status'] == 'Present') {
                                $student_name = $record['first_name'] . ' ' . $record['last_name'];
                                $total_attendance[$student_name]++;
                            }
                        }
                        ?>
                        <?php foreach ($unique_students as $student) : ?>
                            <td><?= $total_attendance[$student] ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p><?= $message ?></p>
    <?php endif; ?>
</body>

</html>