<?php
    $file = explode(",", $_POST['content']);
    if ($_POST['week'] == '') {
        $week = 'trial';
    }
    else {
        $week = intval($_POST['week']);
    }
    $mysqli = require __DIR__ . "/database.php";
    for ($i = 0; $i < count($file); ++$i) {
        $line = explode(' ', $file[$i]);
        if ($line[0] == "Naveen-ul-Haq") {
            $sql = "INSERT INTO week_" . $week . " (Player, Runs, Balls, Wickets, RunsC, Overs, Catches) VALUES ('$line[0]', '$line[1]', '$line[2]', '$line[3]', '$line[4]', '$line[5]', '$line[6]')";
        } else {
            $sql = "INSERT INTO week_" . $week . " (Player, Runs, Balls, Wickets, RunsC, Overs, Catches) VALUES ('$line[0] $line[1]', '$line[2]', '$line[3]', '$line[4]', '$line[5]', '$line[6]', '$line[7]')";
        }
        echo $sql;
        echo '<br>';
        $result = $mysqli->query($sql);
    }

    header('Location: admin.php?operation=successful');
?>