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
	if(parse_url(curPageURL())['query'] == "deadline=passed") {
		echo '<script>alert("The deadline for selecting your team has passed!")</script>';
	} else {
		$_SESSION['MATCH'] = intval(explode('=', parse_url(curPageURL())['query'])[1]);
		$checker_i = $_SESSION['MATCH'] - 1;
		if (explode(' ', $user['team_name'])[0] != $_SESSION['schedule'][$checker_i * 4] && explode(' ', $user['team_name'])[0] != $_SESSION['schedule'][$checker_i * 4 + 2]) {
			header('Location: matches.php?check=invalid');
		}
		else {
			header('Location: team.php');
		}
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
	<?php if(isset($user)): ?>
	<div id="form">
		<h1>Team Selection</h1>

		<?php if (isset($_SESSION['invalid2'])): ?>
            <em>Make sure to include 5 bowling options!</em>
            <br><br>
        <?php endif; ?>

		<form action = "match-template.php" method = "post" id = "team-select">
			<div>
				<!-- <label for = "match-select">Match: </label>
				<select name = "match-select" id = "match-select" required>
					<php
						for ($i = 1; $i < 16; ++$i) {
							echo '<option value = "' . $i . '">' . $i . '</option>';
						}
					?>
				</select>
				<script>
					var sel = document.getElementById('match-select');
					<php
						for ($i = 14; $i > -1; --$i) {
							if (explode(' ', $user['team_name'])[0] != $_SESSION['schedule'][$i * 4] && explode(' ', $user['team_name'])[0] != $_SESSION['schedule'][$i * 4 + 2]) {
								echo 'sel.remove(' . $i . ');';
							}
						}
					?>
				</script> !-->
				<label for = "match-select">Match: </label>
				<input type = "text" name = "match-select" 
				<?php
					echo 'value = "' . $_SESSION['MATCH'] . '"';
				?>
				readonly = "readonly">
				<label for="team-name">   Team Name: </label>
				<input type = "text" name = "team-name" 
				<?php
					echo 'value = "' . $user['team_name'] . '"';
				?>
				readonly = "readonly">
			</div>
			<div>
				<h3>Playing XI</h3>
				<?php
					for ($i = 1; $i < 12; ++$i) {
						echo '<label for="player-' . $i . '">' . $i . '. Player Name:   </label>';
						echo '<input type="text" name="player-' . $i . '" placeholder="Player Name" required>';
						echo '<label for="player-' . $i . '-role">   Role: </label>';
						echo '<select name="player-' . $i . '-role" id="role" required><br>';
						echo '<option value = "Allrounder">Allrounder</option>';
						echo '<option value = "Batsman">Batsman</option>';
						echo '<option value = "Bowler">Bowler</option></select><br>';
					}
				?>
				<h3>Substitutes</h3>
				<?php
					for ($i = 12; $i < 26; ++$i) {
						echo '<label for="player-' . $i . '">' . $i . '. Player Name:   </label>';
						echo '<input type="text" name="player-' . $i . '" placeholder="Player Name">';
						echo '<label for="player-' . $i . '-role">   Role: </label>';
						echo '<select name="player-' . $i . '-role" id="role"><br>';
						echo '<option value = "Allrounder">Allrounder</option>';
						echo '<option value = "Batsman">Batsman</option>';
						echo '<option value = "Bowler">Bowler</option></select><br>';
					}
				?>
			</div>
			<br>
			<div>
				<button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;" type="submit">Submit</button>
				<p style = "display: inline; white-space: pre;"> </p>
				<input style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;" type = "reset"></input>
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