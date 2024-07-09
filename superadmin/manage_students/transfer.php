<?php
// update_semesters.php

include '../../db_connect.php';

// Fetch semesters for dropdown
$semesters = get_semesters($conn);
function get_semesters($conn) {
    $sql = "SELECT * FROM semesters";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_semesters'])) {
    $new_semester_id = $_POST['new_semester'];

    // Fetch courses for the new semester
    $sql_courses = "SELECT course_id FROM courses WHERE semester_id = ?";
    $stmt_courses = $conn->prepare($sql_courses);
    if (!$stmt_courses) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt_courses->bind_param("i", $new_semester_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $stmt_courses->close();

    // Update each student's semester and enroll them in courses of the new semester
    $selected_students = $_POST['students'];
    foreach ($selected_students as $student_id) {
        // Update student's semester (Assuming students table has a 'semester_id' column)
        $sql_update_semester = "UPDATE students SET semester_id = ? WHERE student_id = ?";
        $stmt_update_semester = $conn->prepare($sql_update_semester);
        if (!$stmt_update_semester) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt_update_semester->bind_param("ii", $new_semester_id, $student_id);
        $stmt_update_semester->execute();
        $stmt_update_semester->close();

        // Enroll student in courses of the new semester
        foreach ($courses as $course) {
            $course_id = $course['course_id'];
            $sql_enroll = "INSERT INTO enrollments (student_id, course_id, enrollment_date) VALUES (?, ?, NOW())";
            $stmt_enroll = $conn->prepare($sql_enroll);
            if (!$stmt_enroll) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt_enroll->bind_param("ii", $student_id, $course_id);
            $stmt_enroll->execute();
            $stmt_enroll->close();
        }
    }

    // Redirect back to the same page
    header("Location: ./update_semesters.php");
    exit();
}

// Fetch students filtered by current semester if set
$current_semester_filter = isset($_GET['current_semester']) ? $_GET['current_semester'] : '';

$sql_students = "SELECT student_id, first_name, last_name FROM students";
if (!empty($current_semester_filter)) {
    // Assuming 'students' table has a 'semester_id' column
    $sql_students .= " WHERE semester_id = ?";
    $stmt_students = $conn->prepare($sql_students);
    if (!$stmt_students) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt_students->bind_param("i", $current_semester_filter);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
} else {
    $result_students = $conn->query($sql_students);
}

if (!$result_students) {
    die("Error fetching students: " . $conn->error);
}

$students = $result_students->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Semesters</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <h1>Update Semesters</h1>

    <!-- Form to update semesters for selected students -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="new_semester">Select New Semester:</label>
        <select name="new_semester" id="new_semester">
            <?php foreach ($semesters as $semester) : ?>
                <option value="<?= $semester['semester_id'] ?>"><?= $semester['semester_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
        
        <label for="students">Select Students to Update:</label><br>
        <?php foreach ($students as $student) : ?>
            <input type="checkbox" name="students[]" value="<?= $student['student_id'] ?>">
            <?= $student['first_name'] ?> <?= $student['last_name'] ?><br>
        <?php endforeach; ?>
        <br>
        
        <input type="submit" name="update_semesters" value="Update Semesters">
    </form>

    <hr>

    <!-- Form to filter students by current semester -->
    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="current_semester">Filter by Current Semester:</label>
        <select name="current_semester">
            <option value="">All Semesters</option>
            <?php foreach ($semesters as $semester) : ?>
                <option value="<?= $semester['semester_id'] ?>" <?= ($current_semester_filter == $semester['semester_id']) ? 'selected' : '' ?>>
                    <?= $semester['semester_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter">
    </form>

    <!-- Display filtered students -->
    <h2>Filtered Students</h2>
    <ul>
        <?php foreach ($students as $student) : ?>
            <li><?= $student['first_name'] ?> <?= $student['last_name'] ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
