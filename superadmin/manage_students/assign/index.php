<?php
include '../../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../../login.php");
    exit;
}

$message = '';

// Handle form submission for updating semesters
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['bulk_update'])) {
        // Update selected students to a new semester
        $student_ids = $_POST['student_ids'];
        $new_semester = $_POST['new_semester'];

        if (!empty($student_ids) && !empty($new_semester)) {
            $ids = implode(',', array_map('intval', $student_ids));
            $sql = "UPDATE students SET semester=? WHERE student_id IN ($ids)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $new_semester);

            if ($stmt->execute()) {
                $message = "Records updated successfully";
            } else {
                $message = "Error updating records: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['semester_update'])) {
        // Update all students of a specific semester to another semester
        $current_semester = $_POST['current_semester'];
        $new_semester = $_POST['new_semester'];

        if (!empty($current_semester) && !empty($new_semester)) {
            $sql = "UPDATE students SET semester=? WHERE semester=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_semester, $current_semester);

            if ($stmt->execute()) {
                $message = "Records updated successfully";
            } else {
                $message = "Error updating records: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle form submission for filtering students
$whereClauses = [];
$filter_first_name = '';
$filter_last_name = '';
$filter_semester = '';

if (isset($_POST['filter'])) {
    if (!empty($_POST['first_name'])) {
        $filter_first_name = $_POST['first_name'];
        $whereClauses[] = "first_name LIKE '%" . $conn->real_escape_string($filter_first_name) . "%'";
    }
    if (!empty($_POST['last_name'])) {
        $filter_last_name = $_POST['last_name'];
        $whereClauses[] = "last_name LIKE '%" . $conn->real_escape_string($filter_last_name) . "%'";
    }
    if (!empty($_POST['semester'])) {
        $filter_semester = $_POST['semester'];
        $whereClauses[] = "semester=" . intval($filter_semester);
    }
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = " WHERE " . implode(' AND ', $whereClauses);
}

// Fetch students
$sql = "SELECT student_id, first_name, last_name, semester FROM students" . $whereSQL;
$result = $conn->query($sql);
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Semester</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Update Semester of Students</h2>
        <?php include '../../components/navbar.php'; ?> 
        
        <!-- Filter Form -->
        <form method="POST">
            <h3>Filter Students</h3>
            <label>First Name:</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($filter_first_name); ?>">
            <br>
            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($filter_last_name); ?>">
            <br>
            <label>Semester:</label>
            <select name="semester">
                <option value="">All Semesters</option>
                <option value="1" <?php echo ($filter_semester == '1') ? 'selected' : ''; ?>>First Semester</option>
                <option value="2" <?php echo ($filter_semester == '2') ? 'selected' : ''; ?>>Second Semester</option>
                <option value="3" <?php echo ($filter_semester == '3') ? 'selected' : ''; ?>>Third Semester</option>
                <option value="4" <?php echo ($filter_semester == '4') ? 'selected' : ''; ?>>Fourth Semester</option>
                <option value="5" <?php echo ($filter_semester == '5') ? 'selected' : ''; ?>>Fifth Semester</option>
                <option value="6" <?php echo ($filter_semester == '6') ? 'selected' : ''; ?>>Sixth Semester</option>
                <option value="7" <?php echo ($filter_semester == '7') ? 'selected' : ''; ?>>Seventh Semester</option>
                <option value="8" <?php echo ($filter_semester == '8') ? 'selected' : ''; ?>>Eighth Semester</option>
            </select>
            <br><br>
            <input type="submit" name="filter" value="Filter Students">
        </form>

        <!-- Bulk Update Form -->
        <form method="POST">
            <h3>Bulk Update by Selected Students</h3>
            <label>Select Students:</label><br>
            <?php
            if (count($students) > 0) {
                foreach ($students as $row) {
                    echo '<input style="width: auto;" type="checkbox" name="student_ids[]" value="'.$row['student_id'].'"> '.$row['first_name'].' '.$row['last_name'].' (Semester: '.$row['semester'].')<br>';
                }
            } else {
                echo "No students found";
            }
            ?>
            <br>
            <label>Select New Semester:</label>
            <select name="new_semester">
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
                <option value="3">Third Semester</option>
                <option value="4">Fourth Semester</option>
                <option value="5">Fifth Semester</option>
                <option value="6">Sixth Semester</option>
                <option value="7">Seventh Semester</option>
                <option value="8">Eighth Semester</option>
            </select>
            <br><br>
            <input type="submit" name="bulk_update" value="Update Selected Students">
        </form>

        <hr>

        <!-- Update by Semester Form -->
        <form method="POST">
            <h3>Update by Semester</h3>
            <label>Current Semester:</label>
            <select name="current_semester">
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
                <option value="3">Third Semester</option>
                <option value="4">Fourth Semester</option>
                <option value="5">Fifth Semester</option>
                <option value="6">Sixth Semester</option>
                <option value="7">Seventh Semester</option>
                <option value="8">Eighth Semester</option>
            </select>
            <br>
            <label>New Semester:</label>
            <select name="new_semester">
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
                <option value="3">Third Semester</option>
                <option value="4">Fourth Semester</option>
                <option value="5">Fifth Semester</option>
                <option value="6">Sixth Semester</option>
                <option value="7">Seventh Semester</option>
                <option value="8">Eighth Semester</option>
            </select>
            <br><br>
            <input type="submit" name="semester_update" value="Update Semester">
        </form>

        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>
