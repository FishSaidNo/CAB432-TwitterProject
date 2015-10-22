<?php

	require 'phpIncludes/functions.php';
	
	//If already logged in - redirect to index
	session_start();
	if (inSession()) {
		header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php?alreadyloggedin'); // ?alreadyloggedin is just for debugging
		exit;
	}
	
	
	if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
		
		//Initialize variables
		$enteredUsername = '';
		$enteredPass = '';
		
		if (empty($_POST['username'])) {
			//error
			echo 'error';
		} else {
			$enteredUsername = cleanInput($_POST['username']);
		}
		if (empty($_POST['password'])) {
			//error
			echo 'error';
		} else {
			$enteredPass = hash('sha256', cleanInput($_POST['password']));
		}		
		
		require 'phpIncludes/dbConn.php';
		
		$row = []; //Initialize
		
		try {
			$query = 'SELECT * FROM users WHERE username=:username;';
			$stmt = $db->prepare($query);
			$stmt->bindValue(':username', $enteredUsername, PDO::PARAM_STR);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);	
			
		} catch(PDOException $ex) {
			header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDO'); //error
			exit;
		} 	
		if ($db != NULL) {
			$db = NULL; //Insure db connection is closed
		}
		
		if (empty($row)) {
			//No such username exists
			header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php?login=invaliduser');
			exit;
		}		
		
		$storedId = $row['userId'];
		$storedUsername = $row['username'];
		$storedPass = $row['password'];		
		
		if ($enteredPass === $storedPass) {
			session_start();
			session_unset();
			session_destroy();
			session_start();
			$_SESSION['userId'] = $storedId;
			$_SESSION['username'] = $storedUsername;
			
			//Success
			header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php?login=success');
			exit;
		} else {
			header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php?login=invalidpw'); //Invalid pw
			exit;
		}		

	}

?>