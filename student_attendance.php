<?php
// Include database connection file
include 'db_connect.php';

// Initialize variables
$message = '';
$attendance = [];
$filter_semester = '';
$filter_email = '';
$courses = [];

// Fetch semesters
$sql_semesters = "SELECT semester_id, semester_name FROM Semesters";
$result_semesters = $conn->query($sql_semesters);

// Check if semesters fetched successfully
if (!$result_semesters) {
    die("Error fetching semesters: " . $conn->error);
}

// Store semesters in an associative array
$semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get selected semester and student email from form
    $filter_semester = $_POST['semester_id'];
    $filter_email = $_POST['student_email'];

    // Fetch courses for the selected semester
    $sql_courses = "SELECT course_id, course_name FROM Courses WHERE semester_id = ?";
    $stmt_courses = $conn->prepare($sql_courses);
    $stmt_courses->bind_param("i", $filter_semester);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $stmt_courses->close();

    // Fetch attendance records for the selected semester and student email
    $sql_attendance = "SELECT Students.first_name, Students.last_name, Courses.course_id, Courses.course_name, Attendance.attendance_date, Attendance.status
                        FROM Students
                        JOIN Attendance ON Students.student_id = Attendance.student_id
                        JOIN Courses ON Attendance.course_id = Courses.course_id
                        WHERE Courses.semester_id = ? AND Students.email = ?
                        ORDER BY Attendance.attendance_date ASC";

    $stmt_attendance = $conn->prepare($sql_attendance);
    $stmt_attendance->bind_param("is", $filter_semester, $filter_email);
    $stmt_attendance->execute();
    $result_attendance = $stmt_attendance->get_result();
    $attendance = $result_attendance->fetch_all(MYSQLI_ASSOC);
    $stmt_attendance->close();
}

$dates = [];

// Extract unique dates from attendance records
foreach ($attendance as $record) {
    if ($record['attendance_date'] !== null) {
        $dates[$record['attendance_date']] = true;
    }
}

$dates = array_keys($dates);
sort($dates); // Sort dates to ensure chronological order
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Student Attendance</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .last-col {
            background-color: #f1f3f5 !important ;
        
        }
    </style>
</head>
<body class="container" >
    <h2>Check Student Attendance</h2>
    <form method="post">
        <label for="semester_id">Select Semester:</label>
        <select name="semester_id" required>
            <?php foreach ($semesters as $semester) : ?>
                <option value="<?= $semester['semester_id'] ?>" <?= ($filter_semester == $semester['semester_id']) ? 'selected' : '' ?>>
                    <?= $semester['semester_name'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <label for="student_email">Enter Student Email:</label>
        <input type="email" name="student_email" required value="<?= htmlspecialchars($filter_email) ?>"><br><br>
        <input type="submit" name="check_attendance" value="Check Attendance">
    </form>

    <?php if (!empty($attendance)) : ?>
        <h3>Attendance Records for <?= htmlspecialchars($attendance[0]['first_name']) ?> <?= htmlspecialchars($attendance[0]['last_name']) ?></h3>
        <div style="overflow-x: auto;" >
            <table >
         
                    <tbody>
                    <tr>
                        <th>Date</th>
                        <?php foreach ($courses as $course) : ?>
                            <th><?= htmlspecialchars($course['course_name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($dates as $date) : ?>
                        <tr>
                            <td><?= $date ?></td>
                            <?php foreach ($courses as $course) : ?>
                                <?php 
                                $found = false;
                                foreach ($attendance as $record) {
                                    if ($record['attendance_date'] == $date && $record['course_id'] == $course['course_id']) {
                                        echo '<td>' . htmlspecialchars($record['status']) . '</td>';
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    echo '<td>-</td>';
                                }
                                ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td  class="last-col">Total Present Days</td>
                        <?php foreach ($courses as $course) : ?>
                            <?php 
                                $total_present = 0;
                                foreach ($attendance as $record) {
                                    if ($record['status'] == 'Present'
                                    && $record['course_id'] == $course['course_id']) {
                                        $total_present++;
                                    }
                                }
                                echo '<td class="last-col">' . $total_present . '</td>';
                            ?>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>
