<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

if (isset($user)) {
    echo '<script>alert("Warning: opening up the signup page will automatically log you out!");</script>';
}

session_destroy();
session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>FIPL T20</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.png">
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
    <script src="js/validation.js" defer></script>
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
				<a href="login.php">Log In</a>
				<a href="signup.php">Sign Up</a>
			</div>
		</div>
		<br><br>
	</header>
    <div id="starting-text">
        <h1>Signup</h1>
        
        <form action="process-signup.php" method="post" id="signup" novalidate>
            <div>
                <label for="name" style="position: relative; right: 20px;">Name </label>
                <input type="text" id="name" name="name" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 20px;">
            </div>
            <br>
            <div>
                <label for="team_name" style="position: relative; right: 41px;">Team Name </label>
                <input type="text" id="team_name" name="team_name" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 41px;">
            </div>
            <br>
            <div>
                <label for="email" style="position: relative; right: 18px;">Email </label>
                <input type="email" id="email" name="email" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 18px;">
            </div>
            <br>
            <div>
                <label for="password" style="position: relative; right: 32px;">Password </label>
                <input type="password" id="password" name="password" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 32px;">
            </div>
            <br>
            <div>
                <label for="password_confirmation" style="position: relative; right: 62.5px;">Confirm Password </label>
                <input type="password" id="password_confirmation" name="password_confirmation" style="width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 62.5px;">
            </div>
            <br>
            <button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;">Sign up</button>
            <p>Already have an account? <a href="login.php" style="text-decoration:none">Log In</a></p>
        </form>
    </div>
</body>
</html>