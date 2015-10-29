<?php
	
	// Used to sanitise user input
	function cleanInput($input) {
		$cleanInput = trim($input);
		$cleanInput = stripslashes($cleanInput);
		$cleanInput = htmlspecialchars($cleanInput);
		return $cleanInput;
	}	
	
/* 	
	// Insures value is numeric
	function checkNums($input) {
		if ( strlen($input) && ctype_digit($input) ) {
			return True;
		}
		return False;
	}	
*/
	 
	//Checks if user is logged in
	function inSession() {
		if(!isset($_SESSION)) { 
			session_start(); 
		} 
		if (isset($_SESSION['userId'])) {
			return True;
		}
		return False;
	}			
?>