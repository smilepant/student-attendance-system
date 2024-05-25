<?php
session_start();
if (!isset($_SESSION['superadmin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>SuperAdmin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>

    <div class="container">
        <h2>SuperAdmin Dashboard</h2>
        <?php include './components/navbar.php'; ?> 
    </div>
</body>

</html>