<?php
// Include database connection file
include '../../db_connect.php';

// Validate input parameters
$filter_semester = isset($_GET['sem']) ? $_GET['sem'] : null;
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$filter_semester || !$course_id) {
    die("Please select both semester and course.");
}

// Fetching course name
$sql_course_name = "SELECT course_name FROM courses WHERE course_id = ?";
$stmt_course_name = $conn->prepare($sql_course_name);
$stmt_course_name->bind_param("i", $course_id);
$stmt_course_name->execute();
$result_course_name = $stmt_course_name->get_result();
$row_course_name = $result_course_name->fetch_assoc();
$course_name = $row_course_name['course_name'];

// Fetch attendance records for the selected course ID
$sql_attendance = "SELECT Students.first_name, Students.last_name, Attendance.attendance_date, Attendance.status
                    FROM Attendance
                    JOIN Students ON Attendance.student_id = Students.student_id
                    WHERE Attendance.course_id = ?
                    ORDER BY Attendance.attendance_date ASC";

$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("i", $course_id);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

if ($result_attendance->num_rows > 0) {
    $attendance = $result_attendance->fetch_all(MYSQLI_ASSOC);
} else {
    die("No attendance records found for the selected course.");
}

$stmt_attendance->close();

// Define CSV filename
$filename = "attendance_" . date('Ymd') . "_" . $course_name . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open file pointer to write CSV data
$output = fopen('php://output', 'w');
fputcsv($output, ['Attendance Records for ' . $course_name . " of semester " . $filter_semester]);

// Write CSV headers
$headers = ['S.N', 'Date'];
$unique_students = array_unique(array_map(function ($record) {
    return $record['first_name'] . ' ' . $record['last_name'];
}, $attendance));


foreach ($unique_students as $student) {
    $headers[] = $student;
}
fputcsv($output, $headers);

// Write attendance data to CSV
$dates = array_unique(array_column($attendance, 'attendance_date'));


$sn = 1;
foreach ($dates as $date) {
    $row = [$sn++, $date];
    foreach ($unique_students as $student) {
        $status = '-';  // Default to '-' if no record is found
        foreach ($attendance as $record) {
            if ($record['attendance_date'] == $date && $record['first_name'] . ' ' . $record['last_name'] == $student) {
                $status = $record['status'];
                break;
            }
        }
        $row[] = $status;
    }


    fputcsv($output, $row);
}

// Write an empty row
fputcsv($output, []);

// Write total attendance row
$totalAttendance = array_fill(0, count($unique_students), 0);
foreach ($attendance as $record) {
    if ($record['status'] == 'Present') {
        $index = array_search($record['first_name'] . ' ' . $record['last_name'], $unique_students);
        if ($index !== false) {
            $totalAttendance[$index]++;
        }
    }
}


$totalAttendanceRow = array_merge(['', 'Total Attendance:'], $totalAttendance);
fputcsv($output, $totalAttendanceRow);

// Write total days row
$sn--;
$totalDaysRow = ["", "Total Days: $sn"];

fputcsv($output, $totalDaysRow);

fclose($output);
exit;
?>
