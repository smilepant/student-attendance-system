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
    $semester_id = $_POST['semester_id'];

    $sql = "INSERT INTO Courses (course_name, course_description, semester_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $course_name, $course_description, $semester_id);

    if ($stmt->execute()) {
        $message = "Course added successfully!";
    } else {
        $message = "Error adding course: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing courses and semesters
$sql_courses = "SELECT * FROM Courses";
$result_courses = $conn->query($sql_courses);
if ($result_courses === false) {
    die("Error fetching courses: " . $conn->error);
}
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);
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
            <label for="semester">Semester:</label>
            <select name="semester_id" required>
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
                <option value="3">Third Semester</option>
                <option value="4">Forth Semester</option>
                <option value="5">Fifth Semester</option>
                <option value="6">Sixth Semester</option>
                <option value="7">Seventh Semester</option>
                <option value="8">Eight Semester</option>
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
                <th>Semester</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($courses as $course) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                    <td><?php echo htmlspecialchars($course['semester']); ?></td>
                    <td>
                        <a href="edit.php?course_id=<?php echo $course['course_id']; ?>">Edit</a>
                        <a href="delete.php?course_id=<?php echo $course['course_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>

</html>