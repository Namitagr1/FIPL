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
    if (explode('=', parse_url(curPageURL())['query'])[0] == "check" && explode('=', parse_url(curPageURL())['query'])[1] == "invalid") {
        echo '<script>alert("You are not scheduled to play this match!");</script>';
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
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script>
		var count = 0;
	</script>
	<?php
		$count = -1;
		$schedule_string = "Team 1 Team 2 Team 3 Team 4 Team 5 Team 6 Team 5 Team 6 Team 3 Team 1 Team 4 Team 2 Team 6 Team 3 Team 2 Team 4 Team 5 Team 6 Team 2 Team 3 Team 1 Team 4 Team 2 Team 1 Team 5 Team 6 Team 3 Team 5 Team 4 Team 1";
		$schedule_arr = explode(' ', $schedule_string);
		$schedule = array();
		for ($i = 0; $i < 30; $i += 2) {
			array_push($schedule, $schedule_arr[$i], $schedule_arr[$i + 1], $schedule_arr[$i + 30], $schedule_arr[$i + 31]);
		}
		$_SESSION['schedule'] = $schedule;
	?>
	<script src="js/vars.js"></script>
	<script>
		function setVar(num) {
			matches[num - 1] = true;
		}
	</script>
</head>
<body>
	<header>
		<br><br><br>
		<a href = "../index.php"><img src="../images/logo.png" height="67px" width="120px" alt = "FIPL Logo" class = "not-center"></a>
		<a href="../index.php" class = "under">Home</a>
		<p class = "dis-link">  </p>
		<a href="teamslist.php" class = "under">Teams</a>
		<p id = "no-link2">  <b>Matches</b></p>
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
	<div id="starting-text" style = "height: 5000px;">
		<div class="match" style = "margin: 25px 200px 25px 200px;">
			<div class="match-box" style="display: block; width: 9.5%; padding: 25px 0px 46.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white; max-height: 120px;">
				<?php
					echo "<p style='font-size:32px;'>Match <strong>" . (++$count + 1) . "</strong></p>";
				?>
				<script>
					++count;
				</script>
			</div>
			<div class="match-box" style="display: block; width: 12%; padding: 5px 15px 12.5px 35px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: left; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4] . '</p>';
					echo '<p style = "text-align: left; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 1] . '</p>';
				?>
				<p style = "text-align: left; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">
				<?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT * FROM Results WHERE MatchNum = " . ($count + 1);		
					$result = $mysqli->query($sql);
					$bev = $result->fetch_assoc();
					echo $bev['score_1'];
				?>
				</p>
				<p style = "text-align: left; font-size: 15px; margin-top: 0px; color: #e3dddc;">
				<?php
					echo $bev['overs_1'];
				?>
				</p>
			</div>
			<div class="match-box" style="display: block; width: 6%; padding: 62.75px 0px 62.75px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<p style = "text-align: center; font-size: 20px; color: #e3dddc;">vs</p>
			</div>
			<div class="match-box" style="display: block; width: 12%; padding: 5px 35px 12.5px 15px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: right; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4 + 2] . '</p>';
					echo '<p style = "text-align: right; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 3] . '</p>';
				?>
				<p style = "text-align: right; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">
				<?php
					echo $bev['score_2'];
				?>
				</p>
				<p style = "text-align: right; font-size: 15px; margin-top: 0px; color: #e3dddc;">
				<?php
					echo $bev['overs_2'];
				?>
				</p>
			</div>
			<div id = "bevism" class="match-box" style="display: block; width: 50%; padding: 0px 0px 20px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white;">
				<p id = "result-1" style = "text-align: center; font-size: 30px; margin-bottom: 0px;">
				<?php
					echo $bev['Result'];
				?>
				</p>
				<p style = "text-align: left; font-size: 20px; padding-left: 40px;">Venue</p>
				<a href = "match-template.php?match=<?php echo ($count + 1);?>" onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById('bevism').style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById('bevism').style.paddingBottom='20px'"
				onclick = 'setVar(1)'
				id = "match-btn1" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-right: 10px;">Match Center</a>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById('bevism').style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById('bevism').style.paddingBottom='20px'"
				id = "match-btn2" href="team.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-left: 10px;">Select Your Team</a>
			</div>
		</div>
		<br><br><br><br><br><br><br><br><br><br>
		<div class="match" style = "margin: 25px 200px 25px 200px;">
			<div class="match-box" style="display: block; width: 9.5%; padding: 25px 0px 46.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white; max-height: 120px;">
				<?php
					echo "<p style='font-size:32px;'>Match <strong>" . (++$count + 1) . "</strong></p>";
				?>
				<script>
					++count;
				</script>
			</div>
			<div class="match-box" style="display: block; width: 12%; padding: 5px 15px 12px 35px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: left; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4] . '</p>';
					echo '<p style = "text-align: left; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 1] . '</p>';
				?>
				<p style = "text-align: left; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: left; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<div class="match-box" style="display: block; width: 6%; padding: 62.5px 0px 62.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<p style = "text-align: center; font-size: 20px; color: #e3dddc;">vs</p>
			</div>
			<div class="match-box" style="display: block; width: 12%; padding: 5px 35px 12px 15px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: right; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4 + 2] . '</p>';
					echo '<p style = "text-align: right; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 3] . '</p>';
				?>
				<p style = "text-align: right; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: right; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<div id = "bevism2" class="match-box" style="display: block; width: 50%; padding: 0px 0px 20px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white;">
				<p id = "result-2" style = "text-align: center; font-size: 30px; margin-bottom: 0px;"><?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT * FROM Results WHERE MatchNum = " . ($count + 1);		
					$result = $mysqli->query($sql);
					$bev = $result->fetch_assoc();
					echo $bev['Result'];
				?></p>
				<p style = "text-align: left; font-size: 20px; padding-left: 40px;">Venue</p>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById('bevism2').style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById('bevism2').style.paddingBottom='20px'"
				id = "match-btn1" href="match-template.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-right: 10px;">Match Center</a>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById('bevism2').style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById('bevism2').style.paddingBottom='20px'"
				id = "match-btn2" href="team.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-left: 10px;">Select Your Team</a>
			</div>
		</div>
		<br><br><br><br><br><br><br><br><br><br>
		<div class="match" style = "margin: 25px 200px 25px 200px;">
			<div class="match-box" style="display: block; width: 9.5%; padding: 25px 0px 46.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white; max-height: 120px;">
				<?php
					echo "<p style='font-size:32px;'>Match <strong>" . (++$count + 1) . "</strong></p>";
				?>
				<script>
					++count;
				</script>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 15px 12px 35px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: left; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4] . '</p>';
					echo '<p style = "text-align: left; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 1] . '</p>';
				?>
				<p style = "text-align: left; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: left; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<div class="match-box" style="display: block; width: 2%; padding: 62.5px 0px 62.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<p style = "text-align: center; font-size: 20px; color: #e3dddc;">vs</p>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 35px 12px 15px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: right; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4 + 2] . '</p>';
					echo '<p style = "text-align: right; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 3] . '</p>';
				?>
				<p style = "text-align: right; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: right; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<?php
				echo '<div id = "bevism' . ($count + 1) . '" class="match-box" style="display: block; width: 50%; padding: 0px 0px 20px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white;">';
			?>
				<p id = "result-3" style = "text-align: center; font-size: 30px; margin-bottom: 0px;"><?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT * FROM Results WHERE MatchNum = " . ($count + 1);		
					$result = $mysqli->query($sql);
					$bev = $result->fetch_assoc();
					echo $bev['Result'];
				?></p>
				<p style = "text-align: left; font-size: 20px; padding-left: 40px;">Venue</p>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism3`).style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism3`).style.paddingBottom='20px'"
				id = "match-btn1" href="match-template.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-right: 10px;">Match Center</a>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism3`).style.paddingBottom='16px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism3`).style.paddingBottom='20px'"
				id = "match-btn2" href="team.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-left: 10px;">Select Your Team</a>
			</div>
		</div>
		<br><br><br><br><br><br><br><br><br><br>
		<div class="match" style = "margin: 25px 200px 25px 200px;">
			<div class="match-box" style="display: block; width: 9.5%; padding: 25px 0px 46.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white; max-height: 120px;">
				<?php
					echo "<p style='font-size:32px;'>Match <strong>" . (++$count + 1) . "</strong></p>";
				?>
				<script>
					++count;
				</script>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 15px 12px 35px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: left; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4] . '</p>';
					echo '<p style = "text-align: left; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 1] . '</p>';
				?>
				<p style = "text-align: left; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: left; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<div class="match-box" style="display: block; width: 2%; padding: 62.5px 0px 62.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<p style = "text-align: center; font-size: 20px; color: #e3dddc;">vs</p>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 35px 12px 15px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: right; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4 + 2] . '</p>';
					echo '<p style = "text-align: right; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 3] . '</p>';
				?>
				<p style = "text-align: right; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: right; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<?php
				echo '<div id = "bevism' . ($count + 1) . '" class="match-box" style="display: block; width: 50%; padding: 0px 0px 20.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white;">';
			?>
				<p id = "result-4" style = "text-align: center; font-size: 30px; margin-bottom: 0px;"><?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT * FROM Results WHERE MatchNum = " . ($count + 1);		
					$result = $mysqli->query($sql);
					$bev = $result->fetch_assoc();
					echo $bev['Result'];
				?></p>
				<p style = "text-align: left; font-size: 20px; padding-left: 40px;">Venue</p>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism${count - 1}`).style.paddingBottom='16.5px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism${count - 1}`).style.paddingBottom='20.5px'"
				id = "match-btn1" href="match-template.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-right: 10px;">Match Center</a>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism${count - 1}`).style.paddingBottom='16.5px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism${count - 1}`).style.paddingBottom='20.5px'"
				id = "match-btn2" href="team.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-left: 10px;">Select Your Team</a>
			</div>
		</div>
		<br><br><br><br><br><br><br><br><br><br>
		<div class="match" style = "margin: 25px 200px 25px 200px;">
			<div class="match-box" style="display: block; width: 9.5%; padding: 25px 0px 46.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white; max-height: 120px;">
				<?php
					echo "<p style='font-size:32px;'>Match <strong>" . (++$count + 1) . "</strong></p>";
				?>
				<script>
					++count;
				</script>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 15px 12px 35px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: left; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4] . '</p>';
					echo '<p style = "text-align: left; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 1] . '</p>';
				?>
				<p style = "text-align: left; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: left; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<div class="match-box" style="display: block; width: 2%; padding: 62.5px 0px 62.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<p style = "text-align: center; font-size: 20px; color: #e3dddc;">vs</p>
			</div>
			<div class="match-box" style="display: block; width: 14%; padding: 5px 35px 12px 15px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: red; color: white;">
				<?php
					echo '<p style = "text-align: right; font-size: 24px; margin-bottom: 2px; font-style: italic;">' . $schedule[$count * 4 + 2] . '</p>';
					echo '<p style = "text-align: right; font-size: 24px; margin-top: 3px; margin-bottom: 3px; font-style: italic;">' . $schedule[$count * 4 + 3] . '</p>';
				?>
				<p style = "text-align: right; font-size: 32px; margin-top: 3px; margin-bottom: 3px;">Score</p>
				<p style = "text-align: right; font-size: 15px; margin-top: 0px; color: #e3dddc;">(Overs)</p>
			</div>
			<?php
				echo '<div id = "bevism' . ($count + 1) . '" class="match-box" style="display: block; width: 50%; padding: 0px 0px 20.5px 0px; float: left; margin: 0px 0px 0px 0px; border: none; border-radius: 0px; background-color: black; color: white;">';
			?>
				<p id = "result-5" style = "text-align: center; font-size: 30px; margin-bottom: 0px;"><?php
					$mysqli = require __DIR__ . "/database.php";
					$sql = "SELECT * FROM Results WHERE MatchNum = " . ($count + 1);		
					$result = $mysqli->query($sql);
					$bev = $result->fetch_assoc();
					echo $bev['Result'];
				?></p>
				<p style = "text-align: left; font-size: 20px; padding-left: 40px;">Venue</p>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism${count}`).style.paddingBottom='16.5px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism${count}`).style.paddingBottom='20.5px'"
				id = "match-btn1" href="match-template.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-right: 10px;">Match Center</a>
				<a onmouseover = "
				this.style.backgroundColor='white'
				this.style.color='orange'
				this.style.border='3px solid orange'
				document.getElementById(`bevism${count}`).style.paddingBottom='16.5px'"
				onmouseleave = "
				this.style.backgroundColor='orange'
				this.style.color='white'
				this.style.border='1px solid transparent'
				document.getElementById(`bevism${count}`).style.paddingBottom='20.5px'"
				id = "match-btn2" href="team.php?match=<?php echo ($count + 1);?>" style = "border: 1px solid transparent; border-radius: 3px; display: inline-block; box-shadow: 0 3px 5px #00000040; background-color: orange; color: white; text-decoration: none; padding: 6px 20px; margin-left: 10px;">Select Your Team</a>
			</div>
		</div>
	</div>
	<?php else: ?>
	<div id="starting-text">
		<h2><a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a> to view this page.</h2>
	</div>
	<?php endif; ?>
</body>
</html>