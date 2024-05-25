<?php
include '../db_connect.php';

session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: ./login.php");
    exit;
}

$instructor_id = $_SESSION['instructor_id'];

// Update the SQL query to include semester_id
$sql = "SELECT Courses.course_id, Courses.course_name, Courses.semester_id, Semesters.semester_name
        FROM Courses
        INNER JOIN Course_Instructors ON Courses.course_id = Course_Instructors.course_id
        INNER JOIN Semesters ON Courses.semester_id = Semesters.semester_id
        WHERE Course_Instructors.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Instructor Dashboard</h2>
        <?php include './components/navbar.php'; ?>

        <h3>Your Courses</h3>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['semester_name']); ?></td>
                        <td>
                            <a href="mark_attendance.php?course_id=<?php echo $course['course_id']; ?>">Mark Attendance</a> |
                            <a href="see_attendance.php?sem_id=<?php echo $course['semester_id']; ?>&course_id=<?php echo $course['course_id']; ?>">See Attendance</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>

</html>
