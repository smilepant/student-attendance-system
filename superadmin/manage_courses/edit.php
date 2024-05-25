<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

// Fetch semesters
$sql_semesters = "SELECT * FROM Semesters";
$result_semesters = $conn->query($sql_semesters);
if ($result_semesters === false) {
    die("Error fetching semesters: " . $conn->error);
}
$semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $semester_id = $_POST['semester_id'];

    $sql = "UPDATE Courses SET course_name=?, course_description=?, start_date=?, end_date=?, semester_id=? WHERE course_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $course_name, $course_description, $start_date, $end_date, $semester_id, $course_id);

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
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $course['start_date']; ?>" required><br>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" value="<?php echo $course['end_date']; ?>" required><br>
            <label for="semester">Semester:</label>
            <select name="semester_id" required>
                <?php foreach ($semesters as $semester) : ?>
                    <option value="<?php echo $semester['semester_id']; ?>" <?php if ($semester['semester_id'] == $course['semester_id']) echo "selected"; ?>><?php echo $semester['semester_name']; ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Update Course">
        </form>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>

</html>
