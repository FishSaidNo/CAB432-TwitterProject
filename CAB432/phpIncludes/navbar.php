<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="index.php">TwitterSomething</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">

			<?php
				include 'phpIncludes/functions.php';
				if (!inSession()) {
					//Disply login form
					echo '
						<form id="navbarLoginForm" class="navbar-form navbar-right" action="processLogin.php" method="POST">
							<div class="form-group">
								<input name="username" type="text" placeholder="Username" class="form-control" maxlength="128" required>
							</div>
							<div class="form-group">
								<input name="password" type="password" placeholder="Password" class="form-control" maxlength="128" required>
							</div>
							<button type="submit" class="btn btn-success">Sign In</button>
							<a type="button" class="btn btn-primary" href="register.php">Sign Up</a>
						</form>
					';
				}
				else {
					//Show username & logout button
					echo '
						<form class="navbar-form navbar-right">
							<a href="logout.php" type="button" class="btn btn-primary">Logout</a>
						</form>
						<p class="navbar-text navbar-right"> Logged in as: <a href="#">'.$_SESSION['username'].'</a></p>
					';
				}
			?>
		
		</div>
	</div>
</nav>