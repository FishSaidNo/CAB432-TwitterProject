<?php
	require 'phpIncludes/functions.php';
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

    <title>TwitterSomething CAB432</title>

    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap core CSS -->    
	<link href="css/globalStyle.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">

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
            <li class="active"><a href="#">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="#">Manage Tweet Filters</a></li>
            <li><a href="#">Blah</a></li>
          </ul>
          <ul class="nav nav-sidebar">
            <li><a href="">Something</a></li>
            <li><a href="">Else</a></li>
          </ul>

        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php
			//Display notification if invalid user/pass or logout successful
			if (!empty($_GET['login'])) {
				if ($_GET['login'] == 'invaliduser') {
					echo '
						<div class="offsetAlert alert alert-danger fade in">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<strong>Error!</strong> The entered username does not exist. Please <a href="register.php">register</a> or try again.</div>';							
				} elseif ($_GET['login'] == 'invalidpw') {
					echo '
						<div class="offsetAlert alert alert-danger fade in">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<strong>Error!</strong> That password was invalid. Please try again.</div>';							
				}
			}
			if (!empty($_GET['logout'])) {
				if ($_GET['logout'] == 'true') {
					echo '
						<div class="offsetAlert alert alert-info fade in">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<strong>Alert!</strong> You have successfully logged out.</div>';							
				}
			}
			if (!empty($_GET['error'])) {
				if ($_GET['error'] == 'existingsession') {
					echo '
						<div class="offsetAlert alert alert-info fade in">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<strong>Alert!</strong> You are already logged in, please <a href="logout.php"><strong><u>logout</u></strong></a> before trying again.</div>';							
				}
			}
		?>
          <h1 class="page-header">Dashboard</h1>

          <div class="row placeholders">
            <div class="col-xs-6 col-sm-3 placeholder">
              <img data-src="holder.js/200x200/auto/sky" class="img-responsive" alt="Generic placeholder thumbnail">
              <h4>Label</h4>
              <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
              <img data-src="holder.js/200x200/auto/vine" class="img-responsive" alt="Generic placeholder thumbnail">
              <h4>Label</h4>
              <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
              <img data-src="holder.js/200x200/auto/sky" class="img-responsive" alt="Generic placeholder thumbnail">
              <h4>Label</h4>
              <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
              <img data-src="holder.js/200x200/auto/vine" class="img-responsive" alt="Generic placeholder thumbnail">
              <h4>Label</h4>
              <span class="text-muted">Something else</span>
            </div>
          </div>
		  
          <h2 class="sub-header">Tweets</h2>
          <div class="table-responsive">
            <table class="table table-striped">
			  <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Tweet</th>
                </tr>
              </thead>
              <tbody id="load_from_queue">
				<!-- Fills from external file -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
	
	<script type="text/javascript">                                    
		$('#load_from_queue').load('queue-consume.php');
	</script>
	
    <!-- Just to make our placeholder images work. Don't actually copy the next line! -->
    <script src="../../assets/js/vendor/holder.min.js"></script>
	
</body>
</html>