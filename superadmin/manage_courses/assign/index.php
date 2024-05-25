<?php
include '../../../db_connect.php';
session_start();

if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: superadmin_login.php");
    exit;
}

$message = '';

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

// Handling course assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_course'])) {
    $course_id = $_POST['course_id'];
    $instructor_id = $_POST['instructor_id'];

    $sql = "INSERT INTO Course_Instructors (course_id, instructor_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $course_id, $instructor_id);

    if ($stmt->execute()) {
        $message = "Course assigned successfully!";
    } else {
        $message = "Error assigning course: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch assigned courses
$sql_assigned_courses = "SELECT ci.course_instructor_id, c.course_name, i.first_name, i.last_name 
                         FROM Course_Instructors ci
                         JOIN Courses c ON ci.course_id = c.course_id
                         JOIN Instructors i ON ci.instructor_id = i.instructor_id";
$result_assigned_courses = $conn->query($sql_assigned_courses);

if (!$result_assigned_courses) {
    die("Error fetching assigned courses: " . $conn->error);
}

$assigned_courses = $result_assigned_courses->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Assign Courses to Instructors</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Assign Courses to Instructors</h2>
        <?php include '../../components/navbar.php'; ?>

        <!-- Assign Course Form -->
        <h3>Assign Course</h3>
        <form method="post">
            <input type="hidden" name="assign_course" value="true">
            <label for="course_id">Select Course:</label>
            <select name="course_id" required>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="instructor_id">Select Instructor:</label>
            <select name="instructor_id" required>
                <?php foreach ($instructors as $instructor) : ?>
                    <option value="<?= $instructor['instructor_id'] ?>"><?= $instructor['first_name'] ?> <?= $instructor['last_name'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Assign Course">
        </form>

        <p class="message"><?= $message ?></p>

        <!-- Display Assigned Courses -->
        <h3>Assigned Courses</h3>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Instructor Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_courses as $assigned_course) : ?>
                    <tr>
                        <td><?= $assigned_course['course_name'] ?></td>
                        <td><?= $assigned_course['first_name'] ?> <?= $assigned_course['last_name'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $assigned_course['course_instructor_id'] ?>">Edit</a>
                            <a href="delete.php?id=<?= $assigned_course['course_instructor_id'] ?>" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
