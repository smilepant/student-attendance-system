<?php
include '../../../db_connect.php';
session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = isset($_GET['message']) ? $_GET['message'] : '';

// Fetch students
$sql_students = "SELECT student_id, first_name, last_name FROM Students";
$result_students = $conn->query($sql_students);

if (!$result_students) {
    die("Error fetching students: " . $conn->error);
}

$students = $result_students->fetch_all(MYSQLI_ASSOC);

// Fetch semesters
$sql_semesters = "SELECT semester_id, semester_name FROM Semesters";
$result_semesters = $conn->query($sql_semesters);

if (!$result_semesters) {
    die("Error fetching semesters: " . $conn->error);
}

$semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);

// Fetch courses
$sql_courses = "SELECT course_id, course_name FROM Courses";
$result_courses = $conn->query($sql_courses);

if (!$result_courses) {
    die("Error fetching courses: " . $conn->error);
}

$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

// Handle enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $semester_id = $_POST['semester_id'];

    // Fetch courses for the selected semester
    $sql_courses = "SELECT course_id FROM Courses WHERE semester_id = ?";
    $stmt_courses = $conn->prepare($sql_courses);
    $stmt_courses->bind_param("i", $semester_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $stmt_courses->close();

    // Fetch already enrolled courses for the selected student and semester
    $sql_existing_enrollments = "SELECT course_id FROM Enrollments WHERE student_id = ? AND course_id IN (SELECT course_id FROM Courses WHERE semester_id = ?)";
    $stmt_existing_enrollments = $conn->prepare($sql_existing_enrollments);
    $stmt_existing_enrollments->bind_param("ii", $student_id, $semester_id);
    $stmt_existing_enrollments->execute();
    $result_existing_enrollments = $stmt_existing_enrollments->get_result();
    $existing_enrollments = $result_existing_enrollments->fetch_all(MYSQLI_ASSOC);
    $stmt_existing_enrollments->close();

    // Create an array of existing course IDs
    $existing_course_ids = array_column($existing_enrollments, 'course_id');

    // Enroll student in new courses
    $conn->begin_transaction();
    try {
        foreach ($courses as $course) {
            $course_id = $course['course_id'];
            // Check if the course is not already enrolled
            if (!in_array($course_id, $existing_course_ids)) {
                $sql_enroll = "INSERT INTO Enrollments (student_id, course_id, enrollment_date) VALUES (?, ?, NOW())";
                $stmt_enroll = $conn->prepare($sql_enroll);
                $stmt_enroll->bind_param("ii", $student_id, $course_id);
                $stmt_enroll->execute();
                $stmt_enroll->close();
            }
        }
        $conn->commit();
        $message = "Student enrolled successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error enrolling student: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Enroll Student in Semester</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Enroll Student in Semester</h2>
        <?php include '../../components/navbar.php'; ?>

        <h3>Enroll Student</h3>
        <form method="post">
            <input type="hidden" name="enroll_student" value="true">
            <label for="student_id">Select Student:</label>
            <select name="student_id" required>
                <?php foreach ($students as $student) : ?>
                    <option value="<?= $student['student_id'] ?>"><?= $student['first_name'] ?> <?= $student['last_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="semester_id">Select Semester:</label>
            <select name="semester_id" required>
                <?php foreach ($semesters as $semester) : ?>
                    <option value="<?= $semester['semester_id'] ?>"><?= $semester['semester_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Enroll Student">
        </form>

        <p class="message"><?= $message ?></p>

        <h3>Enrolled Students</h3>
        <!-- Filter Form -->
        <form method="get">
            <label for="filter_student">Filter by Student:</label>
            <select name="filter_student">
                <option value="">All Students</option>
                <?php foreach ($students as $student) : ?>
                    <option value="<?= $student['student_id'] ?>"><?= $student['first_name'] ?> <?= $student['last_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="filter_semester">Filter by Semester:</label>
            <select name="filter_semester">
                <option value="">All Semesters</option>
                <?php foreach ($semesters as $semester) : ?>
                    <option value="<?= $semester['semester_id'] ?>"><?= $semester['semester_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="filter_course">Filter by Course:</label>
            <select name="filter_course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Apply Filters">
        </form>

        <!-- Enrolled Students Table -->
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course Name</th>
                    <th>Semester</th>
                    <th>Enrollment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_enrollments = "SELECT Enrollments.enrollment_id, Students.first_name, Students.last_name, Courses.course_name, Semesters.semester_name, Enrollments.enrollment_date
                                    FROM Enrollments
                                    JOIN Students ON Enrollments.student_id = Students.student_id
                                    JOIN Courses ON Enrollments.course_id =
                                    Courses.course_id
JOIN Semesters ON Courses.semester_id = Semesters.semester_id";
            // Add WHERE clauses based on filter values
            if (isset($_GET['filter_student']) && !empty($_GET['filter_student'])) {
                $filter_student = $_GET['filter_student'];
                $sql_enrollments .= " WHERE Enrollments.student_id = $filter_student";
            }
            if (isset($_GET['filter_semester']) && !empty($_GET['filter_semester'])) {
                $filter_semester = $_GET['filter_semester'];
                if (strpos($sql_enrollments, 'WHERE') !== false) {
                    $sql_enrollments .= " AND Courses.semester_id = $filter_semester";
                } else {
                    $sql_enrollments .= " WHERE Courses.semester_id = $filter_semester";
                }
            }
            if (isset($_GET['filter_course']) && !empty($_GET['filter_course'])) {
                $filter_course = $_GET['filter_course'];
                if (strpos($sql_enrollments, 'WHERE') !== false) {
                    $sql_enrollments .= " AND Courses.course_id = $filter_course";
                } else {
                    $sql_enrollments .= " WHERE Courses.course_id = $filter_course";
                }
            }

            $result_enrollments = $conn->query($sql_enrollments);

            if ($result_enrollments) {
                while ($enrollment = $result_enrollments->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($enrollment['first_name'] . " " . $enrollment['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($enrollment['course_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($enrollment['semester_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($enrollment['enrollment_date']) . "</td>";
                    echo "<td>";
                    // echo "<a href=\"edit.php?id=" . htmlspecialchars($enrollment['enrollment_id']) . "\">Edit</a> ";
                    echo "<a href=\"delete.php?id=" . htmlspecialchars($enrollment['enrollment_id']) . "\" onclick=\"return confirm('Are you sure you want to delete this assignment?')\">Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>

    </table>
</div>
