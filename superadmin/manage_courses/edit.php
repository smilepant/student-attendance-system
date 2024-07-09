<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $semester_id = $_POST['semester_id'];

    $sql = "UPDATE Courses SET course_name=?, course_description=?, semester=? WHERE course_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $course_name, $course_description, $semester_id, $course_id);

    if ($stmt->execute()) {
        $message = "Course updated successfully!";
    } else {
        $message = "Error updating course: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch course details
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    $sql_course = "SELECT * FROM Courses WHERE course_id=?";
    $stmt_course = $conn->prepare($sql_course);
    $stmt_course->bind_param("i", $course_id);
    $stmt_course->execute();
    $result_course = $stmt_course->get_result();
    $course = $result_course->fetch_assoc();
    $stmt_course->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Course</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Edit Course</h2>
        <?php include '../components/navbar.php'; ?>
        <form method="post">
            <input type="hidden" name="edit_course" value="true">
            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
            <label for="course_name">Course Name:</label>
            <input type="text" name="course_name" value="<?php echo $course['course_name']; ?>" required><br>
            <label for="course_description">Course Description:</label>
            <textarea name="course_description" required><?php echo $course['course_description']; ?></textarea><br>
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
            <input type="submit" value="Update Course">
        </form>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>

</html>