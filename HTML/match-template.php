<?php

session_start();

function curPageURL() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"])) {
    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
     $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
     $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

if (isset(parse_url(curPageURL())['query'])) {
    $_SESSION['MATCH'] = intval(explode('=', parse_url(curPageURL())['query'])[1]);
    header('Location: match-template.php');
}

$_SESSION['preferences'] = [
    'Team 3' => "background-color: #f1c232;",
    'Team 6' => "background-color: #ff9900;",
    'Team 2' => "background-color: #000000; color: red;",
    'Team 1' => "background-color: #073763; color: white;",
    'Team 4' => "background-color: #4a86e8;",
    'Team 5' => "background-color: #ff00ff;"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $week = intdiv(intval($_POST['match-select']) - 1, 3) + 1;
    date_default_timezone_set('America/New_York');
    $datetime = new DateTime(date('Y-m-d H:i:s'));
    $mysqli = require __DIR__ . "/database.php";

    $sql = "SELECT deadline FROM deadlines WHERE week = '$week'";
        
    $comparison = new DateTime($mysqli->query($sql)->fetch_assoc()['deadline']);

    if ($datetime > $comparison) {
        header('Location: team.php?deadline=passed');
    }

    $counter = 0;

    for ($i = 1; $i < 12; ++$i) {
        if ($_POST['player-' . $i . '-role'] == "Batsman") {
            ++$counter;
        }
    }

    if ($counter > 6) {
        $_SESSION['invalid2'] = true;
        header("Location: team.php");
        exit;
    }

    $_SESSION['MATCH'] = intval($_POST['match-select']);

	if (explode(' ', $_POST['team-name'])[0] == $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4]) {
		$up = 1;
	}
	else if (explode(' ', $_POST['team-name'])[0] == $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2]) {
		$up = 2;
	}
	else {
		die("Error in finding match");
	}

    $mysqli = require __DIR__ . "/database.php";

    for ($i = 1; $i < 26; ++$i) {
        $sql = "UPDATE match_" . $_SESSION['MATCH'] . " SET Player" . $i . "_" . $up . "='" . $_POST['player-' . $i] . "' WHERE id=1";
        $result = $mysqli->query($sql);
    }

    for ($i = 1; $i < 26; ++$i) {
        $sql = "UPDATE match_" . $_SESSION['MATCH'] . " SET Player" . $i . "_" . $up . "='" . $_POST['player-' . $i . '-role'] . "' WHERE id=2";
        $result = $mysqli->query($sql);
    }
}

if (isset($_SESSION["user_id"])) {
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

if (isset($_SESSION['MATCH'])) {
    echo '<script>var done = false;</script>';

    $_XIs = [];

    $mysqli = require __DIR__ . "/database.php";
    $sql = "SELECT * FROM match_" . $_SESSION['MATCH'] . " WHERE id = 1";        
    $result = $mysqli->query($sql);
    $player_list = $result->fetch_assoc();
    unset($player_list['id']);
    $player_list = array_values($player_list);
    $player_list1 = array_slice($player_list, 0, 25);
    $player_list2 = array_slice($player_list, 25);

    $sql = "SELECT * FROM match_" . $_SESSION['MATCH'] . " WHERE id = 2";        
    $result = $mysqli->query($sql);
    $player_list = $result->fetch_assoc();
    unset($player_list['id']);
    $player_list = array_values($player_list);
    $player_role1 = array_slice($player_list, 0, 25);
    $player_role2 = array_slice($player_list, 25);

    $_XIs[$_SESSION['MATCH'] * 4 - 4] = array_fill(0, 25, array('', ''));
    $_XIs[$_SESSION['MATCH'] * 4 - 3] = array_fill(0, 25, array('', ''));
    $_XIs[$_SESSION['MATCH'] * 4 - 2] = array_fill(0, 25, array('', ''));
    $_XIs[$_SESSION['MATCH'] * 4 - 1] = array_fill(0, 25, array('', ''));

    for ($i = 0; $i < 25; ++$i) {
        if (trim($player_list1[$i]) === '') {
            $player_role1[$i] = ' ';
        }
        if (trim($player_list2[$i]) === ' ') {
            $player_role2[$i] = ' ';
        }
    }

    for ($i = 0; $i < 25; ++$i) {
        $_XIs[$_SESSION['MATCH'] * 4 - 4][$i][0] = $player_list1[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 4][$i][1] = $player_role1[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 3][$i][0] = $player_list2[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 3][$i][1] = $player_role2[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 2][$i][0] = $player_list1[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 2][$i][1] = $player_role1[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 1][$i][0] = $player_list2[$i];
        $_XIs[$_SESSION['MATCH'] * 4 - 1][$i][1] = $player_role2[$i];
    }

    $sql = "SELECT COUNT(*) AS num_rows FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1);
    $result1 = $mysqli->query($sql);
    $result2 = $result1->fetch_assoc();
    $row_count = $result2['num_rows'];

    if ($row_count >= 110) {
        for ($i = 0; $i < 11 && count($_XIs[$_SESSION['MATCH'] * 4 - 2]) >= 12; ++$i) {
            $sql = "SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player='" . $_XIs[$_SESSION['MATCH'] * 4 - 2][$i][0] . "'";
            $result = $mysqli->query($sql);
            $rel_row = $result->fetch_all(MYSQLI_ASSOC);
            if ($rel_row) {
                continue;
            }
            $_XIs[$_SESSION['MATCH'] * 4 - 2][$i] = $_XIs[$_SESSION['MATCH'] * 4 - 2][11];
            unset($_XIs[$_SESSION['MATCH'] * 4 - 2][11]);
            $_XIs[$_SESSION['MATCH'] * 4 - 2] = array_values($_XIs[$_SESSION['MATCH'] * 4 - 2]);
            --$i;
        }
        for ($i = 0; $i < 11 && count($_XIs[$_SESSION['MATCH'] * 4 - 1]) >= 12; ++$i) {
            $sql = "SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player='" . $_XIs[$_SESSION['MATCH'] * 4 - 1][$i][0] . "'";
            $result = $mysqli->query($sql);
            $rel_row = $result->fetch_all(MYSQLI_ASSOC);
            if ($rel_row) {
                continue;
            }
            $_XIs[$_SESSION['MATCH'] * 4 - 1][$i] = $_XIs[$_SESSION['MATCH'] * 4 - 1][11];
            unset($_XIs[$_SESSION['MATCH'] * 4 - 1][11]);
            $_XIs[$_SESSION['MATCH'] * 4 - 1] = array_values($_XIs[$_SESSION['MATCH'] * 4 - 1]);
            --$i;
        }
        echo '<script>done = true;</script>';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>FIPL T20</title>
	<link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../css/sheet.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style type="text/css">
        .ritz .waffle a { color: inherit; }.ritz .waffle .s13{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#ffffff;text-align:right;font-weight:bold;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s15{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;}.ritz .waffle .s7{border-bottom:1px SOLID #ffffff;border-right:3px SOLID #000000;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s3{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #000000;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s16{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:center;font-weight:bold;color:#000000;font-size:20pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s4{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#6aa84f;text-align:center;font-weight:bold;color:#000000;font-size:12pt;vertical-align:middle;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s12{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#93c47d;text-align:center;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s18{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:center;font-weight:bold;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s2{border-bottom:3px SOLID #000000;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s9{border-bottom:3px SOLID #000000;border-right:3px SOLID #000000;background-color:#ffffff;text-align:center;font-weight:bold;color:#000000;font-size:11pt;vertical-align:middle;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s0{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s5{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#b7b7b7;text-align:center;font-weight:bold;color:#000000;font-size:10pt;vertical-align:bottom;white-space:normal;overflow:hidden;word-wrap:break-word;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s1{border-bottom:1px SOLID #000000;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s11{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#000000;text-align:center;font-weight:bold;color:#ffffff;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s17{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;font-weight:bold;font-style:italic;text-decoration:underline;-webkit-text-decoration-skip:none;text-decoration-skip-ink:none;color:#000000;font-size:36pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s20{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;font-weight:bold;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s8{border-bottom:3px SOLID #000000;border-right:3px SOLID #000000;background-color:#b7b7b7;text-align:center;font-weight:bold;color:#000000;font-size:10pt;vertical-align:bottom;white-space:normal;overflow:hidden;word-wrap:break-word;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s10{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;color:#000000;font-size:10pt;vertical-align:middle;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s14{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#93c47d;text-align:center;color:#000000;font-size:10pt;vertical-align:middle;white-space:normal;overflow:hidden;word-wrap:break-word;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s19{border-bottom:1px SOLID #ffffff;border-right:1px SOLID #ffffff;background-color:#ffffff;text-align:left;font-weight:bold;text-decoration:underline;-webkit-text-decoration-skip:none;text-decoration-skip-ink:none;color:#000000;font-size:10pt;vertical-align:bottom;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}.ritz .waffle .s6{border-bottom:1px SOLID #000000;border-right:1px SOLID #000000;background-color:#e06666;text-align:center;color:#000000;font-size:10pt;vertical-align:middle;white-space:nowrap;direction:ltr;padding:2px 3px 2px 3px;}
    </style>
	<link rel="icon" type="image/x-icon" href="../images/favicon.png">
    <script src="js/vars.js"></script>
</head>
<body style = "height: 2000px; overflow: auto !important;">
	<header>
		<br><br><br>
		<a href = "../index.php"><img src="../images/logo.png" height="67px" width="120px" alt = "FIPL Logo" class = "not-center"></a>
		<a style='color: black; text-decoration: none; font-size: 155%; font-family: "poppins" !important; bottom: 10px;' href="../index.php" class = "under">Home</a>
		<p class = "dis-link">  </p>
		<a style='color: black; text-decoration: none; font-size: 155%; font-family: "poppins" !important; bottom: 10px;' href="teamslist.php" class = "under">Teams</a>
		<p class = "dis-link">  </p>
		<a style='color: black; text-decoration: none; font-size: 155%; font-family: "poppins" !important; bottom: 10px;' href="matches.php" class = "under">Matches</a>
		<p class = "dis-link">  </p>
		<a style='color: black; text-decoration: none; font-size: 155%; font-family: "poppins" !important; bottom: 10px;' href="points.php" class = "under">Points Table</a>
		<p class = "dis-link">  </p>
		<a style='color: black; text-decoration: none; font-size: 155%; font-family: "poppins" !important; bottom: 10px;' href="predictions.php" class = "under">Match Predictor</a>
		<div class="dropdown">
			<button class="dropbtn"><i class="fa fa-user" style="font-size:24px"></i>   <i class="fa fa-caret-down" style="font-size:24px"></i></button>
			<div class="dropdown-content">
				<?php if (isset($user)): ?>
					<a style='color: black; text-decoration: none; font-family: "poppins" !important; font-size: 14px;' href="logout.php">Log Out</a>
				<?php else: ?>
					<a style='color: black; text-decoration: none; font-family: "poppins" !important' href="login.php">Log In</a>
				<?php endif; ?>
				<a style='color: black; text-decoration: none; font-family: "poppins" !important; font-size: 14px;' href="signup.php">Sign Up</a>
			</div>
		</div>
		<br><br><br>
	</header>
    <?php if(isset($user)): ?>
    <div id = "starting-text" style = "padding-bottom: 0px; height: 570px; overflow: auto; margin-left: 20px; margin-right: 20px; margin-bottom: 20px;">
    <div class="ritz grid-container" dir="ltr">
 <table cellpadding="0" cellspacing="0" class="waffle">
  <thead style = "position: sticky; top: 0;">
   <tr>
    <th class="row-header freezebar-origin-ltr" style = "position: sticky; left: 0;">
    </th>
    <th class="column-headers-background" id="925581585C0" style="width:100px;">
     A
    </th>
    <th class="column-headers-background" id="925581585C1" style="width:175px;">
     B
    </th>
    <th class="column-headers-background" id="925581585C2" style="width:50px;">
     C
    </th>
    <th class="column-headers-background" id="925581585C3" style="width:175px;">
     D
    </th>
    <th class="column-headers-background" id="925581585C4" style="width:50px;">
     E
    </th>
    <th class="column-headers-background" id="925581585C5" style="width:100px;">
     F
    </th>
    <th class="column-headers-background" id="925581585C6" style="width:175px;">
     G
    </th>
    <th class="column-headers-background" id="925581585C7" style="width:150px;">
     H
    </th>
    <th class="column-headers-background" id="925581585C8" style="width:100px;">
     I
    </th>
    <th class="column-headers-background" id="925581585C9" style="width:100px;">
     J
    </th>
    <th class="column-headers-background" id="925581585C10" style="width:100px;">
     K
    </th>
    <th class="column-headers-background" id="925581585C11" style="width:113px;">
     L
    </th>
    <th class="column-headers-background" id="925581585C12" style="width:100px;">
     M
    </th>
    <th class="column-headers-background" id="925581585C13" style="width:125px;">
     N
    </th>
    <th class="column-headers-background" id="925581585C14" style="width:100px;">
     O
    </th>
    <th class="column-headers-background" id="925581585C6" style="width:175px;">
     P
    </th>
    <th class="column-headers-background" id="925581585C7" style="width:150px;">
     Q
    </th>
    <th class="column-headers-background" id="925581585C8" style="width:100px;">
     R
    </th>
    <th class="column-headers-background" id="925581585C9" style="width:100px;">
     S
    </th>
    <th class="column-headers-background" id="925581585C10" style="width:100px;">
     T
    </th>
    <th class="column-headers-background" id="925581585C11" style="width:113px;">
     U
    </th>
    <th class="column-headers-background" id="925581585C12" style="width:100px;">
     V
    </th>
    <th class="column-headers-background" id="925581585C13" style="width:125px;">
     W
    </th>
    <th class="column-headers-background" id="925581585C23" style="width:100px;">
     X
    </th>
   </tr>
  </thead>
  <tbody>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R0" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      1
     </div>
    </th>
    <td class="s0">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s0">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s2">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R1" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      2
     </div>
    </th>
    <td class="s3">
    </td>
    <td id = "match-number-box" style="font-size: 160%;" class="s4" colspan="2" dir="ltr" rowspan="2">
    Match 
        <?php
            if(isset($_SESSION['MATCH'])) {
                echo $_SESSION['MATCH'];
            }
            else {
                echo 'Format';
            }
        ?>
    </td>
    <td class="s5" id = "team-name-1" style = "font-size: 112%; <?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3];
            }
        ?>
    </td>
    <td class="s6" dir="ltr" rowspan="2">
     V
    </td>
    <td class="s7">
    </td>
    <td class="s8" colspan="3" dir="ltr" rowspan="2" style = "font-size: 260%; <?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3];
            }
        ?>
    </td>
    <td style="font-size: 115%;" class="s9" dir="ltr" id = "result-box-1">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s7">
    </td>
    <td class="s8" colspan="3" dir="ltr" rowspan="2" style = "font-size: 260%; <?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1];
            }
        ?>
    </td>
    <td style="font-size: 115%;" class="s9" dir="ltr" id = "result-box-2">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R2" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      3
     </div>
    </th>
    <td class="s3">
    </td>
    <td class="s5" id = "team-name-2" style = "font-size: 112%; <?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1];
            }
        ?>
    </td>
    <td class="s7">
    </td>
    <td style="font-size: 115%;" class="s9" dir="ltr" id = "result-box-3">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s10" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s7">
    </td>
    <td style="font-size: 115%;" class="s9" dir="ltr" id = "result-box-4">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R3" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      4
     </div>
    </th>
    <td class="s0">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1" dir="ltr">
    </td>
    <td class="s1" dir="ltr">
    </td>
    <td class="s1" dir="ltr">
    </td>
    <td class="s1" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1">
    </td>
    <td class="s1" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R4" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      5
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="font-size: 140%;" class="s4" colspan="4" dir="ltr">
     XIs
    </td>
    <td class="s3" dir="ltr">
    </td>
    <td class="s11">
     Player
    </td>
    <td class="s11" dir="ltr">
     Role
    </td>
    <td class="s11" dir="ltr">
     Runs
    </td>
    <td class="s11" dir="ltr">
     Balls
    </td>
    <td class="s11" dir="ltr">
     Wickets
    </td>
    <td class="s11" dir="ltr">
     Runs Conceded
    </td>
    <td class="s11" dir="ltr">
     Overs Bowled
    </td>
    <td class="s11" dir="ltr">
     Catches/Runouts
    </td>
    <td class="s3">
    </td>
    <td class="s11">
     Player
    </td>
    <td class="s11" dir="ltr">
     Role
    </td>
    <td class="s11" dir="ltr">
     Runs
    </td>
    <td class="s11" dir="ltr">
     Balls
    </td>
    <td class="s11" dir="ltr">
     Wickets
    </td>
    <td class="s11" dir="ltr">
     Runs Conceded
    </td>
    <td class="s11" dir="ltr">
     Overs Bowled
    </td>
    <td class="s11" dir="ltr">
     Catches/Runouts
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R5" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      6
     </div>
    </th>
    <td class="s3">
    </td>
    <td class="s5" style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][0][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s5" style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][0][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3" dir="ltr">
    </td>
    <td class="s5" style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0];
            }
        ?>
    </td>
    <td class="s5" style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][0][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][0][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][0][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][0][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R6" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      7
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][1][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][1][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][1][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][1][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][1][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][1][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R7" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      8
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][2][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][2][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][2][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][2][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][2][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][2][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R8" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      9
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][3][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][3][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][3][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][3][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][3][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][3][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R9" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      10
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][4][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][4][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][4][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][4][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][4][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][4][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R10" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      11
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][5][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][5][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][5][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][5][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][5][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][5][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R11" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      12
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][6][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][6][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][6][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][6][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][6][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][6][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R12" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      13
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][7][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][7][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][7][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][7][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][7][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][7][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R13" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      14
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][8][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][8][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][8][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][8][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][8][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][8][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R14" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      15
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][9][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][9][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][9][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][9][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][9][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][9][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R15" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      16
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][10][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][10][0];
            }
        ?>
    </td>
    <td class="s12" dir="ltr">
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 2][10][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 2][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 2][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0];
            }
        ?>
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 1][10][1];
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Runs'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Balls'];
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][1] != 'Batsman') {
                            echo $_stats['Wickets'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][1] != 'Batsman') {
                            echo $_stats['RunsC'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][1] != 'Batsman') {
                            echo $_stats['Overs'];
                        }
                        else {
                            echo 0;
                        }
                    }
                }
            }
        ?>
    </td>
    <td class="s13 formula" dir="ltr">
        <?php
            if (isset($_SESSION['MATCH'])) {
                if ($_XIs[$_SESSION['MATCH'] * 4 - 1][10][0] != ' ') {
                    $mysqli = require __DIR__ . "/database.php";        
                    $sql = sprintf("SELECT * FROM week_" . (intdiv($_SESSION['MATCH'] - 1, 3) + 1) . " WHERE Player = '%s'", $_XIs[$_SESSION['MATCH'] * 4 - 1][10][0]);        
                    $result = $mysqli->query($sql);        
                    $_stats = $result->fetch_assoc();
                    if (!is_null($_stats)) {
                        echo $_stats['Catches'];
                    }
                }
            }
        ?>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R17" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      17
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][11][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     12th
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][11][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     12th
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R18" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      18
     </div>
    </th>
    <td class="s3">
    </td>
    <td class="s4" dir="ltr">
        Subs
    </td>
    <td class="s4" dir="ltr">
    </td>
    <td class="s4" dir="ltr">
        Subs
    </td>
    <td class="s4" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s15">
    </td>
    <td class="s16" id = "result-box-5" colspan="8" dir="ltr" style = "overflow: auto">
    <script>
        const bonus20Mult = 6;
        const bonus35Mult = 9;
        const bonus50Mult = 14;
        const bonus75Mult = 21;
        const bonus100Mult = 35;
        const bonus150Mult = 54;
        const bonus200Mult = 75;
        const wicketSub = 7.5;
        const catchSub = 3;

        var arr = [...document.getElementsByClassName('formula')].map((x) => {if (x.innerHTML.trim() == '') {return 0;} else {return x.innerHTML}});
        var runs = [];
        var balls = [];
        var wickets = [];
        var runsC = [];
        var overs = [];
        var catches = [];
        var runs1 = [];
        var balls1 = [];
        var wickets1 = [];
        var runsC1 = [];
        var overs1 = [];
        var catches1 = [];

        for (let i = 0; i < 132; i += 12) {
            runs.push(parseInt(arr[i]));
            balls.push(parseInt(arr[i + 1]));
            wickets.push(parseInt(arr[i + 2]));
            runsC.push(parseInt(arr[i + 3]));
            if (Number.isInteger(Number(arr[i + 4])))
                overs.push(parseInt(arr[i + 4]));
            else {
                var mat = Number(arr[i + 4]) - Math.floor(Number(arr[i + 4]));
                mat *= 5/3;
                overs.push(Math.floor(Number(arr[i + 4])) + mat);
            }
            catches.push(parseInt(arr[i + 5]));
            runs1.push(parseInt(arr[i + 6]));
            balls1.push(parseInt(arr[i + 7]));
            wickets1.push(parseInt(arr[i + 8]));
            runsC1.push(parseInt(arr[i + 9]));
            if (Number.isInteger(Number(arr[i + 10])))
                overs1.push(parseInt(arr[i + 10]));
            else {
                var mat1 = Number(arr[i + 10]) - Math.floor(Number(arr[i + 10]));
                mat1 *= 5/3;
                overs1.push(Math.floor(Number(arr[i + 10])) + mat1);
            }
            catches1.push(parseInt(arr[i + 11]));
        }

        var score1;
        var score2;
        var runtotal = 0;
        var balltotal = 0;
        var runtotal1 = 0;
        var balltotal1 = 0;
        var wicketstotal = 0;
        var wicketstotal1 = 0;
        var runsCtotal = 0;
        var runsCtotal1 = 0;
        var overstotal = 0;
        var overstotal1 = 0;
        var catchestotal = 0;
        var catchestotal1 = 0;
        var bonus20_1 = 0;
        var bonus35_1 = 0;
        var bonus50_1 = 0;
        var bonus75_1 = 0;
        var bonus100_1 = 0;
        var bonus150_1 = 0;
        var bonus200_1 = 0;
        var bonus20_2 = 0;
        var bonus35_2 = 0;
        var bonus50_2 = 0;
        var bonus75_2 = 0;
        var bonus100_2 = 0;
        var bonus150_2 = 0;
        var bonus200_2 = 0;

        for (let i = 0; i < runs.length; i++) {
            runtotal += runs[i];
            if (runs[i] >= 20 && runs[i] < 35) {
                ++bonus20_1;
            }
            if (runs[i] >= 35 && runs[i] < 50) {
                ++bonus35_1;
            }
            if (runs[i] >= 50 && runs[i] < 75) {
                ++bonus50_1;
            }
            if (runs[i] >= 75 && runs[i] < 100) {
                ++bonus75_1;
            }
            if (runs[i] >= 100 && runs[i] < 150) {
                ++bonus100_1;
            }
            if (runs[i] >= 150 && runs[i] < 200) {
                ++bonus150_1;
            }
            if (runs[i] >= 200) {
                ++bonus200_1;
            }
        }
        for (let i = 0; i < runs1.length; i++) {
            runtotal1 += runs1[i];
            if (runs1[i] >= 20 && runs1[i] < 35) {
                ++bonus20_2;
            }
            if (runs1[i] >= 35 && runs1[i] < 50) {
                ++bonus35_2;
            }
            if (runs1[i] >= 50 && runs1[i] < 75) {
                ++bonus50_2;
            }
            if (runs1[i] >= 75 && runs1[i] < 100) {
                ++bonus75_2;
            }
            if (runs1[i] >= 100 && runs1[i] < 150) {
                ++bonus100_2;
            }
            if (runs1[i] >= 150 && runs1[i] < 200) {
                ++bonus150_2;
            }
            if (runs1[i] >= 200) {
                ++bonus200_2;
            }
        }
        for (let i = 0; i < balls.length; i++) {
            balltotal += balls[i];
        }
        for (let i = 0; i < balls1.length; i++) {
            balltotal1 += balls1[i];
        }
        for (let i = 0; i < wickets.length; i++) {
            wicketstotal += wickets[i];
        }
        for (let i = 0; i < wickets1.length; i++) {
            wicketstotal1 += wickets1[i];
        }
        for (let i = 0; i < runsC.length; i++) {
            runsCtotal += runsC[i];
        }
        for (let i = 0; i < runsC1.length; i++) {
            runsCtotal1 += runsC1[i];
        }
        for (let i = 0; i < overs.length; i++) {
            overstotal += overs[i];
        }
        for (let i = 0; i < overs1.length; i++) {
            overstotal1 += overs1[i];
        }
        for (let i = 0; i < catches.length; i++) {
            catchestotal += catches[i];
        }
        for (let i = 0; i < catches1.length; i++) {
            catchestotal1 += catches1[i];
        }

        score1 = runtotal / balltotal * 120;
        score2 = runtotal1 / balltotal1 * 120;
        var sub1 = score1;
        var sub2 = score2;
        var bowl1 = runsCtotal / overstotal * 20;
        var bowl2 = runsCtotal1 / overstotal1 * 20;

        score1 = (score1 + bowl2) / 2;
        score2 = (score2 + bowl1) / 2;

        score1 -= (wicketstotal1 * wicketSub + catchestotal1 * catchSub);
        score2 -= (wicketstotal * wicketSub + catchestotal * catchSub);

        score1 += (bonus20_1 * bonus20Mult + bonus35_1 * bonus35Mult + bonus50_1 * bonus50Mult + bonus75_1 * bonus75Mult + bonus100_1 * bonus100Mult + 
        bonus150_1 * bonus150Mult + bonus200_1 * bonus200Mult);
        score2 += (bonus20_2 * bonus20Mult + bonus35_2 * bonus35Mult + bonus50_2 * bonus50Mult + bonus75_2 * bonus75Mult + bonus100_2 * bonus100Mult + 
        bonus150_2 * bonus150Mult + bonus200_2 * bonus200Mult);

        var oversCalc1 = score1 / sub1 * 20;
        var oversCalc2 = score2 / sub2 * 20;
        var oversBase1 = Math.floor(oversCalc1);
        var oversBase2 = Math.floor(oversCalc2);

        var oversAdd1 = Math.round((oversCalc1 - oversBase1) * 6) / 10;
        var oversAdd2 = Math.round((oversCalc2 - oversBase2) * 6) / 10;

        var res = [Math.floor(score1), `/${(wicketstotal1 > 10) ? 10 : wicketstotal1}`, Math.floor(score2), `/${(wicketstotal > 10) ? 10 : wicketstotal}`, `(${(wicketstotal1 <= 10) ? '20' : (oversBase1 + oversAdd1)})`, `(${(wicketstotal <= 10) ? '20' : (oversBase2 + oversAdd2)})`];
        var unbev = true;

        for (let i = 0; i < res.length; ++i) {
            if (res[i] !== res[i]) {
                document.write('Match Result');
                unbev = false;
                break;
            }
        }

        if (unbev) {
            document.getElementById('result-box-1').innerHTML = res[0] + res[1];
            document.getElementById('result-box-2').innerHTML = res[2] + res[3];
            document.getElementById('result-box-3').innerHTML = res[4];
            document.getElementById('result-box-4').innerHTML = res[5];

            if ((res[0] - res[2]) === 1 || (res[0] - res[2]) === -1) {
                var runString = ' run!!!';
            }
            else {
                var runString = ' runs!!!';
            }

            if (done) {
                var leadString = ' win by ';
            }
            else {
                var leadString = ' lead by ';
            }

            let finalresult = 'Match Tied';

            if (score1 > score2) {
                finalresult = String(document.getElementById('team-name-1').innerHTML) + leadString + (res[0] - res[2]) + runString;
            }
            else if (score2 > score1) {
                finalresult = String(document.getElementById('team-name-2').innerHTML) + leadString + (res[2] - res[0]) + runString;
            }

            document.write(finalresult);

            function createCookie(name, value, days) {
                var expires;
                
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toGMTString();
                }
                else {
                    expires = "";
                }
                
                document.cookie = escape(name) + "=" + 
                    escape(value) + expires + "; path=/";
            }

            createCookie('result', finalresult.replace(/\s+/g, ' ').trim(), 10);
            createCookie('score1', res[0] + res[1], 10);
            createCookie('overs1', res[4].replace('%28', '(').replace('%29', ')'), 10);
            createCookie('score2', res[2] + res[3], 10);
            createCookie('overs2', res[5].replace('%28', '(').replace('%29', ')'), 10);

            <?php 
                $mysqli = require __DIR__ . "/database.php";
                $sql = "SELECT * FROM results WHERE MatchNum = " . $_SESSION['MATCH'];        
                $result = $mysqli->query($sql);
                $result1 = $result->fetch_assoc();
                if ($_COOKIE['result'] != $result1['Result'] || $_COOKIE['score1'] != $result1['score_1'] || $_COOKIE['score2'] != $result1['score_2'] || $_COOKIE['overs1'] != $result1['overs_1'] || $_COOKIE['overs2'] != $result1['overs_2']) {
                    echo "window.location.replace('add-result.php?match=" . $_SESSION['MATCH'] . "=result=' + finalresult.replace(/\s+/g, ' ').trim() + '=score1=' + (res[0] + res[1]) + '=overs1=' + res[4].replace('%28', '(').replace('%29', ')') + '=score2=' + (res[2] + res[3]) + '=overs2=' + res[5].replace('%28', '(').replace('%29', ')'));";
                }
            ?>
        }
    </script>
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R19" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      19
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][12][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     13
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][12][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     13
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s15">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R20" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      20
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][13][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     14
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][13][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     14
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R21" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      21
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][14][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     15
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][14][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     15
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s17" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R22" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      22
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][15][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     16
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][15][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     16
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s18" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R23" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      23
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][16][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     17
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][16][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     17
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R24" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      24
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][17][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     18
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][17][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     18
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R25" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      25
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][18][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     19
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][18][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     19
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R26" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      26
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][19][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     20
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][19][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     20
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s19" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R27" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      27
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][20][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     21
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][20][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     21
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s20" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R28" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      28
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][21][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     22
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][21][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     22
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R29" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      29
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][22][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     23
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][22][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     23
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R30" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      30
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][23][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     24
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][23][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     24
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R31" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      31
     </div>
    </th>
    <td class="s3">
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 4] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 3]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 4][24][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     25
    </td>
    <td style="<?php if (isset($_SESSION['MATCH'])) {echo $_SESSION['preferences'][$_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 2] . ' ' . $_SESSION['schedule'][$_SESSION['MATCH'] * 4 - 1]];}?>" class="s5">
        <?php
            if (isset($_SESSION['MATCH'])) {
                echo $_XIs[$_SESSION['MATCH'] * 4 - 3][24][0];
            }
        ?>
    </td>
    <td class="s14" dir="ltr">
     25
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
   <tr style="height: 20px">
    <th class="row-headers-background" id="925581585R32" style="height: 20px;">
     <div class="row-header-wrapper" style="line-height: 20px">
      32
     </div>
    </th>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s20" dir="ltr">
    </td>
    <td class="s20" dir="ltr">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
    <td class="s0">
    </td>
   </tr>
  </tbody>
 </table>
</div>
    </div>
    <?php else: ?>
	<div id="starting-text" style = "overflow: auto;">
		<h2 style = "font-size: 185%;"><a style='color: blue; text-decoration: underline;' href="login.php">Log In</a> or <a style='color: blue; text-decoration: underline;' href="signup.php">Sign Up</a> to view this page.</h2>
	</div>
	<?php endif; ?>
</body>
</html>