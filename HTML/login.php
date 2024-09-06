<?php

session_start();

session_destroy();
?>
<?php

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = sprintf("SELECT * FROM user
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
    
    if ($user) {
        
        if (password_verify($_POST["password"], $user["password_hash"])) {
            
            session_start();
            
            session_regenerate_id();
            
            $_SESSION["user_id"] = $user["id"];
            
            header("Location: ../index.php");
            exit;
        }
    }
    
    $is_invalid = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>FIPL T20</title>
    <meta charset="UTF-8">
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
    <div id="starting-text">
        <h1>Login</h1>
        
        <?php if ($is_invalid): ?>
            <em>Invalid login</em>
            <br><br>
        <?php endif; ?>
        
        <form method="post">
            <label for="email" style="position: relative; right: 15px;">Email </label>
            <input type="email" name="email" id="email" style = "width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 15px;"
                value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            <br><br>
            <label for="password" style="position: relative; right: 30px;">Password </label>
            <input type="password" name="password" id="password" style = "width: 200px; height: 40px; border: none; border-radius: 8px; background: #d0d2d6; position: relative; right: 30px;">
            <br><br>
            <button style="font-family: system-ui; font-size: 120%; padding: 5px 20px 8px 20px; position: relative;">Log in</button>
            <p>Don't have an account? <a href="signup.php" style="text-decoration: none">Sign Up</a></p>
        </form>
    </div>
</body>
</html>