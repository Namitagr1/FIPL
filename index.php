<?php
# Trial change
session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/html/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>FIPL T20</title>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.png">
</head>
<body>
	<header>
		<br><br><br>
		<a href = "index.php"><img src="images/logo.png" height="67px" width="120px" alt = "FIPL Logo" class = "not-center"></a>
		<p id = "no-link"><b>Home</b>   </p>
		<a href="html/teamslist.php" class = "under">Teams</a>
		<p class = "dis-link">  </p>
		<a href="html/matches.php" class = "under">Matches</a>
		<p class = "dis-link">  </p>
		<a href="html/points.php" class = "under">Points Table</a>
		<p class = "dis-link">  </p>
		<a href="html/predictions.php" class = "under">Match Predictor</a>
		<div class="dropdown">
			<button class="dropbtn"><i class="fa fa-user" style="font-size:24px"></i>   <i class="fa fa-caret-down" style="font-size:24px"></i></button>
			<div class="dropdown-content">
				<?php if (isset($user)): ?>
					<a href="html/logout.php">Log Out</a>
				<?php else: ?>
					<a href="html/login.php">Log In</a>
				<?php endif; ?>
				<a href="html/signup.php">Sign Up</a>
			</div>
		</div>
		<br><br>
	</header>
	<div id = "starting-text">
		<h1>Home</h1>

		<?php if (isset($user)): ?>
        
			<h2>Welcome to FIPL T20, <?= htmlspecialchars(explode(" ", $user["name"])[0]) ?>!</h2>
			
		<?php else: ?>
			
			<h2>Welcome to FIPL T20, Guest!</h2>
			
		<?php endif; ?>
		<img src="images/FIPL.png" height="335px" width="600px" alt = "FIPL Logo">
		</br></br>
		<a href="https://www.iplt20.com/" target="_blank" rel="noopener noreferrer">Take a look at <b>IPL T20</b>!</a>
	</div>
</body>
</html>