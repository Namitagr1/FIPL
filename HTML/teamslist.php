<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

$_SESSION['preferences'] = [
    'Chennai Chipmunks' => "background-color: #f1c232;",
    'Mumbai Mayankies' => "background-color: #ff9900;",
    'Calcutta Communists' => "background-color: #000000; color: red;",
    'Bangalore Betas' => "background-color: #073763; color: white;",
    'London Legends' => "background-color: #4a86e8;",
    'Magical Magicians' => "background-color: #ff00ff;"
];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>FIPL T20</title>
	<link rel="stylesheet" type="text/css" href="../css/styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="icon" type="image/x-icon" href="../images/favicon.png">
</head>
<body>
	<header>
		<br><br><br>
		<a href = "../index.php"><img src="../images/logo.png" height="67px" width="120px" alt = "FIPL Logo" class = "not-center"></a>
		<a href="../index.php" class = "under">Home</a>
		<p id = "no-link3">  <b>Teams</b></p>
		<p class = "dis-link">  </p>
		<a href="matches.php" class = "under">Matches</a>
		<p class = "dis-link">  </p>
		<a href="points.php" class = "under">Points Table</a>
		<p class = "dis-link">  </p>
		<a href="predictions.php" class = "under">Match Predictor</a>
		<div class="dropdown">
			<button class="dropbtn"><i class="fa fa-user" style="font-size:24px"></i>   <i class="fa fa-caret-down" style="font-size:24px"></i></button>
			<div class="dropdown-content">
				<?php if (isset($user)): ?>
					<a href="logout.php">Log Out</a>
				<?php else: ?>
					<a href="login.php">Log In</a>
				<?php endif; ?>
				<a href="signup.php">Sign Up</a>
			</div>
		</div>
		<br><br>
	</header>
	<?php if (isset($user)): ?>
	<div id="starting-text">
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 6.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_1 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_1']
				?>
			</p>
		</div>
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 0.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_2 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_2']
				?>
			</p>
		</div>
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 0.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_3 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_3']
				?>
			</p>
		</div>
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 0.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_4 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_4']
				?>
			</p>
		</div>
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 0.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_5 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_5']
				?>
			</p>
		</div>
		<div class="team" style="display: block; float: left; padding: 0.5% 2% 1.4% 2%; width: 10%; margin-left: 0.5%; margin-top: 1%; font-size: 24px; height: 100px;">
			<p class="team_name">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT team_6 FROM teams
							WHERE id = '0'";
					$result = $mysqli->query($sql);
					$result = $result->fetch_assoc();
					echo $result['team_6']
				?>
			</p>
		</div>
	</div>
	<?php else: ?>
	<div id="starting-text">
		<h2><a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a> to view this page.</h2>
	</div>
	<?php endif; ?>
</body>
</html>