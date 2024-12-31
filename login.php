<?php
session_start();
require "db.php";

// auto login
if (validSession()) {
	header("Location: homePage.php");
	exit;
}

if (isset($_POST["loginBtn"])) {

	if (empty($_POST['email'])) {
		$email_error = "Please enter your email";
	}

	if (empty($_POST['pass'])) {
		$pass_error = "Please enter your password";
	}
}
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Form</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
	<?php
	// Authentication
	if (!empty($_POST)) {
		extract($_POST);
		if (checkUser($email, $pass)) {
			// the user is authenticated
			// Store data to use in other php files. 
			$_SESSION["user"] = getUser($email);
			header("Location: homePage.php"); // redirect to main page
			exit;
		}
		if (!empty($_POST['email']) && !empty($_POST['pass'])) {
			$authError = true;
		}
	}
	?>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6 col-lg-5">
				<div class="card shadow">
					<div class="card-body">
						<h1 class="text-center mb-4">Login to <span class="text-primary">PicConnect</span></h1>
						<form action="login.php" method="POST">
							<div class="mb-3">
								<label for="email" class="form-label">
									<i class="bi bi-mailbox2 me-2"></i>Email
								</label>
								<input type="email" class="form-control" placeholder="Enter Your Email" name="email" 
									value="<?= isset($email) ? $email : '' ?>">
								<?= (isset($email_error) ? "<p class='text-danger small'>$email_error</p>" : "") ?>
							</div>
							<div class="mb-3">
								<label for="password" class="form-label">
									<i class="bi bi-key me-2"></i>Password
								</label>
								<input type="password" class="form-control" placeholder="********" name="pass" 
									value="<?= isset($pass) ? $pass : '' ?>">
								<?php
								// Authentication Error Message
								if (isset($authError)) {
									echo "<p class='text-danger small'>Wrong email or password</p>";
								}
								// Direct access to main page error message
								if (isset($_GET["error"])) {
									echo "<p class='text-danger small'>You tried to access home page directly</p>";
								}
								if (isset($pass_error)) {
									echo "<p class='text-danger small'>$pass_error</p>";
								}
								?>
							</div>
							<div class="mb-3">
								<button type="submit" class="btn btn-primary w-100" name="loginBtn">Login</button>
							</div>
							<p class="text-center">Don't have an account? <a href="register.php">Register Now!</a></p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
