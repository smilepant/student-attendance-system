<?php
include '../../../db_connect.php';
session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the current assignment details
    $sql = "SELECT * FROM Course_Instructors WHERE course_instructor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
    } else {
        die("Assignment not found.");
    }

    $stmt->close();
} else {
    die("ID not provided.");
}

// Fetch courses
$sql_courses = "SELECT * FROM Courses";
$result_courses = $conn->query($sql_courses);

if (!$result_courses) {
    die("Error fetching courses: " . $conn->error);
}

$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

// Fetch instructors
$sql_instructors = "SELECT * FROM Instructors";
$result_instructors = $conn->query($sql_instructors);

if (!$result_instructors) {
    die("Error fetching instructors: " . $conn->error);
}

$instructors = $result_instructors->fetch_all(MYSQLI_ASSOC);

// Update assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_assignment'])) {
    $course_id = $_POST['course_id'];
    $instructor_id = $_POST['instructor_id'];

    $sql = "UPDATE Course_Instructors SET course_id = ?, instructor_id = ? WHERE course_instructor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $course_id, $instructor_id, $id);

    if ($stmt->execute()) {
        $message = "Assignment updated successfully!";
    } else {
        $message = "Error updating assignment: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Assignment</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Edit Assignment</h2>
        <?php include '../../components/navbar.php'; ?>

        <!-- Edit Assignment Form -->
        <form method="post">
            <input type="hidden" name="update_assignment" value="true">
            <label for="course_id">Select Course:</label>
            <select name="course_id" required>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?= $course['course_id'] ?>" <?= $course['course_id'] == $assignment['course_id'] ? 'selected' : '' ?>>
                        <?= $course['course_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <label for="instructor_id">Select Instructor:</label>
            <select name="instructor_id" required>
                <?php foreach ($instructors as $instructor) : ?>
                    <option value="<?= $instructor['instructor_id'] ?>" <?= $instructor['instructor_id'] == $assignment['instructor_id'] ? 'selected' : '' ?>>
                        <?= $instructor['first_name'] ?> <?= $instructor['last_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Update Assignment">
        </form>

        <p class="message"><?= $message ?></p>
    </div>
</body>

</html>
