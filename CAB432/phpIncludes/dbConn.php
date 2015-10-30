<?php
	//Establishes our connection to the database

	$dbHost = 'cab432-database.cloudapp.net';
	$dbName = 'cab432_db';
	$dbCharset = 'utf8';
	$dbUser = 'cab432user';
	$dbPass = 'CoreyMorgan432';

	// $dbHost = 'localhost';
	// $dbName = 'cab432-db';
	// $dbCharset = 'utf8';
	// $dbUser = 'root';
	// $dbPass = '';
	
	
	$db = new PDO('mysql:host='.$dbHost.';dbname='.$dbName.';charset='.$dbCharset, $dbUser, $dbPass);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Provides meaningful SQL error data that we can handle appropriately
	$dbHost = $dbName = $dbCharset = $dbUser = $dbPass = NULL; //Remove used variables from memory
?>