<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
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
		<p id = "no-link4">  <b>Points Table</b></p>
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
	<div id = "points-table">
		<h1>Points Table</h1>
		<table>
			<thead>
				<th>Ranking</th>
				<th>Team</th>
				<th>MP</th>
				<th>W</th>
				<th>L</th>
				<th>Margins</th>
				<th>Points</th>
			</thead>
			<tr>
				<td>1</td>
				<td>Magical Magicians</td>
				<td>6</td>
				<td>4</td>
				<td>2</td>
				<td>207</td>
				<td>8</td>
			</tr>
			<tr>
				<td>2</td>
				<td>London Legends</td>
				<td>6</td>
				<td>4</td>
				<td>2</td>
				<td>14</td>
				<td>8</td>
			</tr>
			<tr>
				<td>3</td>
				<td>Bangalore Betas</td>
				<td>6</td>
				<td>3</td>
				<td>3</td>
				<td>55</td>
				<td>6</td>
			</tr>
			<tr>
				<td>4</td>
				<td>Mumbai Mayankies</td>
				<td>6</td>
				<td>2</td>
				<td>4</td>
				<td>-27</td>
				<td>4</td>
			</tr>
			<tr>
				<td>5</td>
				<td>Calcutta Communists</td>
				<td>6</td>
				<td>2</td>
				<td>4</td>
				<td>-88</td>
				<td>4</td>
			</tr>
			<tr>
				<td>6</td>
				<td>Chennai Chipmunks</td>
				<td>6</td>
				<td>2</td>
				<td>4</td>
				<td>-109</td>
				<td>4</td>
			</tr>
		</table>
	</div>
	<?php else: ?>
	<div id="starting-text">
		<h2><a href="login.php">Log In</a> or <a href="signup.php">Sign Up</a> to view this page.</h2>
	</div>
	<?php endif; ?>
</body>
</html>