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

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

if (isset(parse_url(curPageURL())['query'])) {
    if (explode('=', parse_url(curPageURL())['query'])[1] == "successful") {
        echo '<script>alert("Operation successful!");</script>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
	if($_POST['match'] === "All") {
		$mysqli = require __DIR__ . "/database.php";
		for ($j = 1; $j < 31; ++$j) {
			for ($i = 1; $i < 26; ++$i) {
				$sql = "UPDATE match_" . $j . " SET Player" . $i . "_" . $_POST['inning'] . " = ' '";
				$result = $mysqli->query($sql);
			}
		}
		unset($_SESSION['MATCH']);
		header("Location: match-template.php");
		exit;
	}
	else {
		$mysqli = require __DIR__ . "/database.php";
		for ($i = 1; $i < 26; ++$i) {
			$sql = "UPDATE match_" . $_POST['match'] . " SET Player" . $i . "_" . $_POST['inning'] . " = ' '";
			$result = $mysqli->query($sql);
		}
		$_SESSION['MATCH'] = $_POST['match'];
		header("Location: match-template.php");
		exit;
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="icon" type="image/x-icon" href="../images/favicon.png">
</head>
<body>
	<header>
		<br><br><br>
		<a href = "../index.php"><img src="../images/logo.png" height="67px" width="120px" alt = "FIPL Logo" class = "not-center"></a>
		<a href="../index.php" class = "under">Home</a>
		<p class = "dis-link">  </p>
		<a href="teamslist.php" class = "under">Teams</a>
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
    <?php if(isset($user) && $user['admin'] == 1): ?>
    <div id="form">
        <h1>Admin</h1>
        <form action="process-link.php" method="post" id = "link-entry">
            <h3>File Entry</h3>
			<script>
				function createContent() {
					const content = document.querySelector(".content");
					const [file] = document.querySelector("input[type=file]").files;
					const reader = new FileReader();
					reader.addEventListener(
						"load",
						() => {
							content.value = reader.result.split('\n').slice(2, -1);
						},
						false,
					);
					if (file) {
						reader.readAsText(file);
					}
				}
			</script>
            <label for="file" style = "position: relative; right: 20px;">Stats File: </label>
			<input type="file" id = "file" name="file" accept = ".txt" onchange = "createContent();" required><br><br>
			<input type="text" id = "content" name = "content" class = "content" hidden></input>
            <label for="week" style="position: relative; right: 48px;">Match Week: </label>
			<input type="text" name="week" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 48px;"><br>
            <br>
			<div>
				<button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;" type="submit">Submit</button>
			</div>
        </form>
		<br>
		<form method="post" id = "reset-team">
            <h3>Team Reset</h3>
            <label for="match" style = "position: relative; right: 28px;">Match: </label>
			<select name="match" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 28px;" required>
				<option value="" selected disabled>Match Number</option>
				<?php
					for ($i = 1; $i < 31; ++$i) {
						echo '<option value="' . $i . '">' . $i . '</option>';
					}
				?>
				<option value="All">All</option>
			</select>
			<p style = "display: inline; white-space: pre;">   </p>
			<label for="inning" style = "position: relative; right: 28px;">Inning: </label>
			<select name="inning" style="width: 205px; height: 42px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 28px;" required>
				<option value="1">1</option>
				<option value="2">2</option>
			</select>
			<br><br>
			<div>
				<button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;" type="submit">Submit</button>
			</div>
        </form>
		<br>
		<form action="process-deadline.php" method = "post" id = "deadline-entry">
			<h3>Team Deadline</h3>
			<label for="week" style = "position: relative; right: 23px;">Week: </label>
			<select name="week" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 23px;" required>
				<option value="" selected disabled>Week Number</option>
				<?php
					for ($i = 1; $i < 11; ++$i) {
						echo '<option value="' . $i . '">' . $i . '</option>';
					}
				?>
			</select>
			<br><br>
			<label for="date" style = "position: relative; right: 20px;">Date: </label>
			<input type="date" id = "date" name = "date" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 20px;" required><br>
			<br>
			<label for="time" style = "position: relative; right: 20px;">Time: </label>
			<input type="time" id = "time" name = "time" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 20px;" required><br>
			<br>
			<div>
				<button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;" type="submit">Submit</button>
			</div>
		</form>
    </div>
    <?php else: ?>
	<div id="starting-text">
		<h2><a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a> to view this page.</h2>
	</div>
	<?php endif; ?>
</body>
</html>