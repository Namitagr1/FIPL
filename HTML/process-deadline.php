<?php
$week = $_POST['week'];
$date = $_POST['date'] . " " . $_POST['time'] . ":00";

$mysqli = require __DIR__ . "/database.php";

$sql = "UPDATE deadlines SET deadline = '$date' WHERE week = '$week'";
        
$stmt = $mysqli->stmt_init();

if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}
                  
if ($stmt->execute()) {
    header('Location: admin.php?operation=successful');
} else {
    die($mysqli->error . " " . $mysqli->errno);
}
?>