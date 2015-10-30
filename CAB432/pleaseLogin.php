<?php
	require 'phpIncludes/functions.php';
	//If the user is logged in they shouldn't be here!!
	if (inSession()) {
		header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/index.php');
		exit;
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

    <title>Please Login</title>

    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap core CSS -->    
	<link href="css/globalStyle.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/pleaseLogin.css" rel="stylesheet">

	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>

<body>

	<?php include 'phpIncludes/navbar.php';?>
	


    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li><a href="index.php">Overview</a></li>
            <li><a href="manageFilters.php">Manage Tweet Filters</a></li>
            <li><a href="#">Blah</a></li>
          </ul>
          <ul class="nav nav-sidebar">
            <li class="active"><a href="" style="color:red;"><strong>Please Login</strong></a></li>
      </ul>

        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

          <h1 id="message"><em>Please login or create an account from the navbar to make use of this service :-)</em></h1>
		  
        </div>
      </div>
    </div>
	

</body>
</html>