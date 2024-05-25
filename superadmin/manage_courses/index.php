<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

// Add Course functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $semester_id = $_POST['semester_id'];

    $sql = "INSERT INTO Courses (course_name, course_description, start_date, end_date, semester_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $course_name, $course_description, $start_date, $end_date, $semester_id);

    if ($stmt->execute()) {
        $message = "Course added successfully!";
    } else {
        $message = "Error adding course: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing courses and semesters
$sql_courses = "SELECT c.*, s.semester_name FROM Courses c INNER JOIN Semesters s ON c.semester_id = s.semester_id";
$result_courses = $conn->query($sql_courses);
if ($result_courses === false) {
    die("Error fetching courses: " . $conn->error);
}
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

$sql_semesters = "SELECT * FROM Semesters";
$result_semesters = $conn->query($sql_semesters);
if ($result_semesters === false) {
    die("Error fetching semesters: " . $conn->error);
}
$semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Manage Courses</h2>
        <?php include '../components/navbar.php'; ?> 
        <!-- Add Course Form -->
        <h3>Add Course</h3>
        <form method="post">
            <input type="hidden" name="add_course" value="true">
            <label for="course_name">Course Name:</label>
            <input type="text" name="course_name" required><br>
            <label for="course_description">Course Description:</label>
            <textarea name="course_description" required></textarea><br>
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required><br>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" required><br>
            <label for="semester">Semester:</label>
            <select name="semester_id" required>
                <?php foreach ($semesters as $semester) : ?>
                    <option value="<?php echo $semester['semester_id']; ?>"><?php echo $semester['semester_name']; ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Add Course">
        </form>

        <!-- Existing Courses -->
        <h3>Existing Courses</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Course Name</th>
                <th>Course Description</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($courses as $course) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                    <td><?php echo htmlspecialchars($course['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($course['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($course['semester_name']); ?></td>
                    <td>
                        <form method="get" action="edit.php" style="display:inline;">
                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                            <input type="submit" value="Edit">
                        </form>
                        <form method="get" action="delete.php" style="display:inline;">
                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                            <input type="submit" value="Delete">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>

</html>
