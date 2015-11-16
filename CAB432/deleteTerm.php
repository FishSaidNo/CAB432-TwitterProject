<?php
	$userId;
	$watchedTerms = array(); //Will store a simple array of the user's watched terms
	
	require 'phpIncludes/functions.php';
	
	//If not logged in, redirect the user
	if (!inSession()) {
		header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/pleaseLogin.php');
		exit;
	}
	else {
		$userId = $_SESSION['userId'];
		
		require 'phpIncludes/dbConn.php';
		
		$row = []; //Initialize
		
		try {
			$query = 'SELECT watchedTerms FROM users WHERE userId=:userId;';
			$stmt = $db->prepare($query);
			$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);	
			
		} catch(PDOException $ex) {
			header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDO'); //error
			exit;
		} 	

		
		if (empty($row) || $row['watchedTerms'] == '') {
			//user not watching any terms
			//do nothing currently
		} else {
			//Store terms in array
			$watchedTerms = explode(',', $row['watchedTerms']);
			
		}
		
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {
			if (empty($_GET['term'])) {
				//user disabled html5/js validation :: hard redirect for simplicity
				header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=NoTermToDelete'); //error
				exit;
			} else {
				$termToDel = $_GET['term'];

				//Check term actually exists
				
				$found = False;
				
				foreach ($watchedTerms as $term) {
					if ($termToDel == $term) {
						$found = True;
					}
				}
				
				if (!$found) {
					header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=CantDeleteTermDoesntExist'); //error
					exit;
				}
				
				//create enerw array without the deleted term
				$newWatchedTerms = array();
				foreach($watchedTerms as $value)
				{
					if($value == $termToDel)
					{
						continue;
					}
					else
					{
						$newWatchedTerms[] = $value;
					}     
				}			
				
				$watchedTermsString = ''; //Will contain the list of watched terms delimited by a comma (CSV), for storage into the database
				
				//Update the watched terms
				$counter = 0;
				$len = count($newWatchedTerms);
				foreach ($newWatchedTerms as $term) {
					$watchedTermsString .= $term; //Append term to string
					if ($counter < ($len - 1))  {
						//If not the last entry, append a comma
						$watchedTermsString .= ',';
					}
					$counter = $counter + 1; //Advance counter
				}
				
				//Update the db
				try {
					$query = 'UPDATE users SET watchedTerms=:terms WHERE userId=:userId';
					$stmt = $db->prepare($query);
					$stmt->bindValue(':terms', $watchedTermsString, PDO::PARAM_STR);
					$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
					$stmt->execute();
					//If sucessfull, redirect to self with success popup
					header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/manageFilters.php?success=termdeleted'); //Success!
					exit;
				} catch(PDOException $ex) {
					header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=PDO'); //error
					exit;
				} 
			}
		}
		
		
		if ($db != NULL) {
			$db = NULL; //Close db connection
		}		
	}
	
	
?>