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
		
		//If user adding more terms
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
			if (empty($_POST['newterm'])) {
				//user disabled html5/js validation :: hard redirect for simplicity
				header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/errorpage.php?id=postEmptyForNewTerm'); //error
				exit;
			} else {
				$newTerm = cleanInput($_POST['newterm']); //Sanitize user input
				$newTerm = str_replace(',', '', $newTerm); //Strip commas from string
				$newTerm = strtolower($newTerm); //Convert string to lowercase for storage
				
				//Check new term does not already exist
				foreach ($watchedTerms as $term) {
					if ($newTerm == $term) {
						header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/manageFilters.php?error=termexists'); //Notify user with popup
						exit;
					}
				}
				
				$watchedTerms[] = $newTerm; //Add new term to the array
				$watchedTermsString = ''; //Will contain the list of watched terms delimited by a comma (CSV), for storage into the database
				
				//Update the watched terms
				$counter = 0;
				$len = count($watchedTerms);
				foreach ($watchedTerms as $term) {
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
					header('Location: http://'.$_SERVER["HTTP_HOST"].'/CAB432/manageFilters.php?success=termadded'); //Success!
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

    <title>Manage Filters - TweetParser</title>

    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap core CSS -->    
	<link href="css/globalStyle.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/manageFilters.css" rel="stylesheet">

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
            <li class="active"><a href="manageFilters.php">Manage Tweet Filters</a></li>
          </ul>

        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		
			<?php
				//Display notification if term added/removed (un)successfully.
				if (!empty($_GET['success'])) {
					if ($_GET['success'] == 'termadded') {
						echo '
							<div class="offsetAlert alert alert-success fade in">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<strong>Success!</strong> Your new search term / tweet filter was successfully added.</div>';							
					}
					if ($_GET['success'] == 'termdeleted') {
						echo '
							<div class="offsetAlert alert alert-warning fade in">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<strong>Term Deleted!</strong> Your have successfully deleted the term.</div>';							
					}
				} elseif (!empty($_GET['error'])) {
					if ($_GET['error'] == 'termexists') {
						echo '
							<div class="offsetAlert alert alert-danger fade in">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<strong>Error!</strong> You are already watching the term you tried to add...</div>';							
					}
				}
				
			?>
			
			<h1 class="page-header">Manage Tweet Filters</h1>

			<div class="">
				<form class="form-term" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
					<h2 class="my-heading">Add a new search term/filter</h2>

					<input name="newterm" type="text" class="form-control" placeholder="New term (e.g. 'pizza')" maxlength="140" required>
					<p><strong>Note:</strong> Please add only one term at a time of at most 140 characters. Values are not case-sensitive & all commas (",") will be stripped.</p>
					<button class="btn btn-lg btn-primary btn-block" type="submit">Add term</button>
				</form>
			</div>
			
			<div id="termsList">
				<h2 class="my-heading sub-header">Terms you are watching:</h2>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
						<tr>
						  <th class="col-md-8">Term</th>
						  <th class="col-md-2">Remove?</th>
						</tr>
						</thead>
						<tbody>
						<?php	
							foreach ($watchedTerms as $term) {
								echo '<tr>';
								echo '<td>' . $term . '</td>';
								echo '<td><button type="button" class="btn btn-danger termBtn" term="'.$term.'">Delete Term</button></td>';
								echo '</tr>';
							}
						?>	
						</tbody>
					</table>
				</div>
			</div>
			
		</div>	
	</div>	
	
	
	<script type="text/javascript">
		$('.termBtn').on('click', function() {
			window.location.href = 'deleteTerm.php?term=' + $(this).attr('term');
		});
	</script>	
</body>
</html>