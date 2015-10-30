<?php
require 'phpIncludes/functions.php';

if (inSession()) {
	header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php?error=existingsession'); //error
	exit;
} 
else {
	$requiredEmpty = 0; //Required fields that are empty
	$passwordMatchFail = False;
	$registerSuccess = False;
	$usernameTaken = False;

	if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
		
		require 'phpIncludes/functions.php';

		//Initialize variables
		$user = '';
		$pass = '';
		$confirmPass = '';

		if (empty($_POST['username'])) {
			$requiredEmpty += 1;
		} else {
			$user = cleanInput($_POST['username']);
		}
		if (empty($_POST['password'])) {
			$requiredEmpty += 1;
		} else {
			$pass = hash('sha256', cleanInput($_POST['password']));
		}
		if (empty($_POST['confirmPassword'])) {
			$requiredEmpty += 1;
		} else {
			$confirmPass = hash('sha256', cleanInput($_POST['confirmPassword']));
		}
		
		if ($pass !== $confirmPass && $pass != '' && $confirmPass != ''){
			$passwordMatchFail = True;
		}
		
		if ($requiredEmpty == 0 && $passwordMatchFail == False) {
			require 'PhpIncludes/dbConn.php';
			
			try {
				$query = 'SELECT * FROM users WHERE username=:username;';
				$stmt = $db->prepare($query);
				$stmt->bindValue(':username', $user, PDO::PARAM_STR);
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($row)) {
					$usernameTaken = True;
				}
				
				//if no probs
				if (!$usernameTaken) {
					//Now we can add the new user to the db
					$query = 'INSERT INTO users (userId, username, password) VALUES (0, :username, :password)';
					$stmt = $db->prepare($query);
					$stmt->bindValue(':username', $user, PDO::PARAM_STR);
					$stmt->bindValue(':password', $pass, PDO::PARAM_STR);
					$stmt->execute();
					
					$registerSuccess = True;
				}
				
			} catch(PDOException $ex) {
				header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDO'); //error... still need to create error page
				exit;
			} 
			if ($db != NULL) {
				$db = NULL; //Close db connection
			}
			
		}

	}
}	
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Register</title>

    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap core CSS -->
    <link href="css/globalStyle.css" rel="stylesheet">
    <link href="css/register.css" rel="stylesheet">

</head>

<body>

	<?php include 'phpIncludes/navbar.php';?>

    <div class="container">
	
		<div id="alertReqEmpty"></div>
		<div id="alertPwMatch"></div>
		<div id="alertSuccess"></div>
		
		<?php
			if ($registerSuccess == True){
				echo '
					<div class="nonOffsetAlert alert alert-success fade in">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong>Success!</strong> You hace successfully registered. Please login.</div>';
			} elseif ($requiredEmpty != 0) {
				echo '
					<div class="nonOffsetAlert alert alert-danger fade in">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong>Error!</strong> Please enter a value for all fields.</div>';			
			} elseif ($usernameTaken == True) {
				echo '
					<div class="nonOffsetAlert alert alert-danger fade in">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong>Error!</strong> The username you selected is not available. Please try again.</div>';			
			} elseif ($passwordMatchFail == True) {
				echo '
					<div class="nonOffsetAlert alert alert-danger fade in">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<strong>Error!</strong> The passwords you entered don\'t match. Please try again.</div>';			
			}
		?>
	
		<form class="form-register" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<h2 class="form-register-heading">Account Registration</h2>
			<label for="inputUsername" class="sr-only">Username</label>
			<input name="username" type="text" id="inputUsername" class="form-control" placeholder="Username" maxlength="128" required autofocus>
			
			<label for="inputPassword" class="sr-only">Password</label>
			<input name="password" type="password" id="inputPassword" class="form-control" placeholder="Password" maxlength="128" required>
			
			<label for="inputConfirmPassword" class="sr-only">Confirm Password</label>
			<input name="confirmPassword" type="password" id="inputConfirmPassword" class="form-control" placeholder="Confirm Password" maxlength="128" required>
			<button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
		</form>

    </div> <!-- /container -->
	
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>
