<?php
include '../db_connect.php';
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the necessary data is provided
    if (isset($_POST['course_id']) && isset($_POST['attendance_date'])) {
        $course_id = $_POST['course_id'];
        $attendance_date = $_POST['attendance_date'];
        $present_students = isset($_POST['present']) ? $_POST['present'] : [];

        // Prepare the SQL statement to fetch all students enrolled in the given course
        $sql_fetch_students = "
            SELECT s.student_id 
            FROM Students s
            INNER JOIN Enrollments e ON s.student_id = e.student_id
            WHERE e.course_id = ?";
        $stmt_fetch_students = $conn->prepare($sql_fetch_students);
        if (!$stmt_fetch_students) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind the course_id parameter and execute the statement
        $stmt_fetch_students->bind_param("i", $course_id);
        if (!$stmt_fetch_students->execute()) {
            die("Execute failed: " . $stmt_fetch_students->error);
        }

        // Fetch the results
        $result = $stmt_fetch_students->get_result();
        $all_students = $result->fetch_all(MYSQLI_ASSOC);

        // Close the statement
        $stmt_fetch_students->close();

        // Prepare the SQL statement to insert attendance records
        $sql_insert_attendance = "INSERT INTO Attendance (student_id, course_id, attendance_date, status) VALUES (?, ?, ?, ?)";
        $stmt_insert_attendance = $conn->prepare($sql_insert_attendance);
        if (!$stmt_insert_attendance) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters
        $stmt_insert_attendance->bind_param("iiss", $student_id, $course_id, $attendance_date, $status);

        // Iterate through all students and set their attendance status
        foreach ($all_students as $student) {
            $student_id = $student['student_id'];
            $status = in_array($student_id, $present_students) ? 'Present' : 'Absent';

            // Execute the statement for each student
            if (!$stmt_insert_attendance->execute()) {
                die("Execute failed: " . $stmt_insert_attendance->error);
            }
        }

        // Close the statement
        $stmt_insert_attendance->close();

        // Redirect back to the attendance marking page with a success message
        header("Location: mark_attendance.php?course_id=$course_id&success=Attendance marked successfully");
        exit;
    } else {
        // Redirect back to the attendance marking page with an error message if data is missing
        header("Location: mark_attendance.php?error=Missing data");
        exit;
    }
} else {
    // Redirect to the home page if accessed directly
    header("Location: index.php");
    exit;
}
?>
