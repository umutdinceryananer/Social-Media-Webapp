<?php
// Initialize variables at the start of the file
$sanitized_name = '';
$sanitized_surname = '';
$sanitized_email = '';
$sanitized_pass = '';

if (!empty($_POST)) 
{
	require "db.php";
	extract($_POST);

	// Age validation
	if (!empty($bdate)) {  // Add check to prevent DateTime errors
		$birthDate = new DateTime($bdate);
		$today = new DateTime();
		$age = $today->diff($birthDate)->y;
		
		if ($age < 13) {
			$age_error = "You must be at least 13 years old to register.";
		} else {
			//Saniting
			$sanitized_name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$sanitized_surname = filter_var($surname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
			$sanitized_pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			//Default Profile Picture
			try 
			{
				if (empty($_FILES["profilePic"]["name"])) 
				{
					$targetDir = "images/";
					$fileName = basename("default.jpg");
					$targetFilePath = $targetDir . $fileName;
					$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
				} 
				else
				{	
					$targetDir = "images/";
					$fileName = $_FILES["profilePic"]["name"];
					$targetFilePath = $targetDir . $fileName;
					$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
				}

				// File Type Check
				$allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
				$hash_password = password_hash($sanitized_pass, PASSWORD_BCRYPT);

				// Create User
				$sql = ("insert into user (user_name, surname, email,password,bdate,profilepic) values (?,?,?,?,?,?)");
				$stmt = $db->prepare($sql);

				// Check E-mail
				$emailCheck = $db->prepare("SELECT * FROM user WHERE email = ?");
				$emailCheck->execute([$email]);
				if (!(empty($name) || empty($surname) || empty($email) || empty($pass) || empty($bdate))) {
					if ($emailCheck->rowCount() == 0) {
						$stmt->execute([$name, $surname, $email, $hash_password, $bdate, $fileName]);
						header("Location: login.php");
					} 
					else 
					{
						$authError = true;
					}
				}
			} catch (PDOException $ex) {
				header("Location: register.php");
				exit;
			}
		}
	}
}

//Error Messages
if ($_SERVER['REQUEST_METHOD'] == "POST") 
{
	if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $pass)) 
	{
		$error_pass_match = "Password does not meet the requirements";
	}
	if (empty($_POST['name'])) 
	{
		$name_error = "Please Enter Your Name";
	}
	if (empty($_POST['surname'])) 
	{
		$surname_error = "Please Enter Your Surname";
	}
	if (empty($_POST['email'])) 
	{
		$email_error = "Please Enter Your Mail";
	}
	if (empty($_POST['pass'])) 
	{
		$pass_error = "Please Enter Your Password";
	}
	if (empty($_POST['bdate'])) 
	{
		$bdate_error = "Please Enter Your Birth Date";
	} else {
		$birthDate = new DateTime($_POST['bdate']);
		$today = new DateTime();
		$age = $today->diff($birthDate)->y;
		if ($age < 13) {
			$bdate_error = "You must be at least 13 years old to register.";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register Form</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-6 col-md-8">
				<div class="card shadow">
					<div class="card-body">
						<h1 class="register text-center mb-4">Register to <span class="text-primary">PicConnect</span></h1>
						<form action="" method="POST" enctype="multipart/form-data">
							<div class="mb-3">
								<div class="text-center mb-3">
									<div class="circle mx-auto" style="width: 100px; height: 100px; overflow: hidden; border-radius: 50%;">
										<img class="profile-pic img-fluid"
											src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg">
									</div>
									<button type="button" class="btn btn-secondary btn-sm upload-button mt-2">Upload</button>
									<input class="file-upload d-none" name="profilePic" type="file" accept="image/*">
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="name" class="form-label">Name</label>
									<input type="text" class="form-control" id="name" placeholder="Enter Your Name" name="name"
										value="<?= isset($name) ? $sanitized_name : '' ?>">
									<?php if (isset($name_error)) echo "<small class='text-danger'>$name_error</small>" ?>
								</div>
								<div class="col-md-6">
									<label for="surname" class="form-label">Surname</label>
									<input type="text" class="form-control" id="surname" placeholder="Enter Your Surname" name="surname"
										value="<?= isset($surname) ? $sanitized_surname : '' ?>">
									<?php if (isset($surname_error)) echo "<small class='text-danger'>$surname_error</small>" ?>
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="email" class="form-label">Email</label>
									<input type="email" class="form-control" id="email" placeholder="Enter Your Email" name="email"
										value="<?= isset($email) ? $sanitized_email : '' ?>">
									<?php if (isset($email_error)) echo "<small class='text-danger'>$email_error</small>" ?>
								</div>
								<div class="col-md-6">
									<label for="pass" class="form-label">
										Password 
										<i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right"
										   title="Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character"></i>
									</label>
									<input type="password" class="form-control" id="pass" placeholder="Enter Your Password" name="pass"
										value="<?= isset($pass) ? $sanitized_pass : '' ?>">
									<?php if (isset($pass_error)) echo "<small class='text-danger'>$pass_error</small>" ?>
									<small class="text-danger"><?= isset($error_pass_match) ? $error_pass_match : '' ?></small>
								</div>
							</div>

							<div class="mb-3">
								<label for="bdate" class="form-label">Birth Date</label>
								<input type="date" class="form-control" id="bdate" name="bdate" max="<?= date('Y-m-d') ?>">
								<?php if (isset($bdate_error)) echo "<small class='text-danger'>$bdate_error</small>" ?>
							</div>

							<div class="text-center">
								<button type="submit" class="btn btn-primary w-100" name="registerBtn">Register</button>
								<p class="mt-3">Already have an account? <a href="login.php">Log in</a></p>
							</div>
						</form>
						<?php if (isset($authError)) {
							echo "<p class='text-danger text-center mt-3'>This email is already used</p>";
						} ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		$(document).ready(function() {
			var readURL = function(input) {
				if (input.files && input.files[0]) {
					var reader = new FileReader();
					reader.onload = function(e) {
						$('.profile-pic').attr('src', e.target.result);
					}
					reader.readAsDataURL(input.files[0]);
				}
			};
			$(".file-upload").on('change', function() {
				readURL(this);
			});
			$(".upload-button").on('click', function() {
				$(".file-upload").click();
			});
		});

		// Initialize tooltips
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl)
		});
	</script>
</body>
</html>
