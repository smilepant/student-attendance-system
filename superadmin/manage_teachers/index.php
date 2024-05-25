<?php
include '../../db_connect.php';

session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch list of teachers
$sql = "SELECT * FROM Instructors";
$result = $conn->query($sql);
$teachers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Teachers</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
    <div class="container">
        
        <h2>Manage Teachers</h2>
        <?php include '../components/navbar.php'; ?> 
        <h3>Add New Teacher</h3>
        <form method="post" action="add.php">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" required><br>
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" required><br>
            <label for="email">Email:</label>
            <input type="email" name="email" required><br>
            <label for="username">Username:</label>
            <input type="text" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br>
            <input type="submit" value="Add Teacher">
        </form>

        <h3>Existing Teachers</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($teachers as $teacher) : ?>
                <tr>
                    <td><?= htmlspecialchars($teacher['instructor_id']); ?></td>
                    <td><?= htmlspecialchars($teacher['first_name']); ?></td>
                    <td><?= htmlspecialchars($teacher['last_name']); ?></td>
                    <td><?= htmlspecialchars($teacher['email']); ?></td>
                    <td><?= htmlspecialchars($teacher['username']); ?></td>
                    <td>
                        <a href="edit.php?instructor_id=<?= $teacher['instructor_id']; ?>">Edit</a>
                        <a href="delete.php?instructor_id=<?= $teacher['instructor_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>