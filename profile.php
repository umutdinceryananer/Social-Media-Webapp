<?php
	session_start();
	require_once "db.php" ;
	$userData=$_SESSION["user"];

	$userId=$userData["user_id"];

	// Add this function here as a backup in case db.php doesn't load properly
	if (!function_exists('time_elapsed_string')) {
		function time_elapsed_string($datetime) {
			$now = new DateTime;
			$ago = new DateTime($datetime);
			$diff = $now->diff($ago);

			if ($diff->y > 0) {
				return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
			}
			if ($diff->m > 0) {
				return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
			}
			if ($diff->d > 0) {
				return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
			}
			if ($diff->h > 0) {
				return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
			}
			if ($diff->i > 0) {
				return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
			}
			return 'just now';
		}
	}

	if (isset($_GET['error'])) {
		$error_message = "An error occurred while processing your request.";
	}
	if (isset($_GET['success'])) {
		$success_message = "Request processed successfully!";
	}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
		integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<title><?php echo $_SESSION["user"]["user_name"]." ".$_SESSION["user"]["surname"]?></title>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="container-fluid">
			<!-- Logo  Section -->
			<a class="navbar-brand" href="homePage.php">
				<img src="images/picconnect-logo.png" alt="PicConnect Logo" height="40">
			</a>
			<!-- Search Form Section -->
			<form id="searchForm" class="d-flex mx-auto" style="width: 40%;" action="search.php" method="post">
				<div class="input-group">
					<input type="search" class="form-control" name="searchUser" placeholder="Search for a friend">
					<button class="btn btn-outline-secondary border-start-0" type="submit">
						<img id='chatsvg' src="images/search.svg" alt="Search" width="20">
					</button>
				</div>
				<input type='hidden' name='user' value="<?php echo $userData["user_id"]; ?>">
			</form>
				<div class="dropdown">
					<a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<img src="images/<?php echo ($_SESSION["user"]["profilepic"]) ?>" alt="Profile Picture" class="rounded-circle me-2" width="40" height="40">
						<span class="text-success"><?php echo $_SESSION["user"]["user_name"] . ' ' . $_SESSION["user"]["surname"] ?></span>
					</a>
					<ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profileDropdown">
						<li><a class="dropdown-item" href="homePage.php">
							<img src="images/house.svg" alt="" class="me-2" width="20">Home
						</a></li>
						<li><a class="dropdown-item" href="profile.php">
							<img src="images/person.svg" alt="" class="me-2" width="20">Profile
						</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="logout.php">
							<img src="images/box-arrow-right.svg" alt="" class="me-2" width="20">Logout
						</a></li>
					</ul>
				</div>
		</div>
	</nav>
	<div class="container-fluid py-4">
		<div class="row mb-4">
			<div class="col-12">
				<div class="card shadow-sm">
					<div class="card-header bg-light d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">Profile Settings</h5>
						<button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#profileEdit">
							Edit Profile
						</button>
					</div>
					<div class="collapse" id="profileEdit">
						<div class="card-body">
							<form action="update_profile.php" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-md-2 text-center mb-3">
										<img src="images/<?php echo $_SESSION["user"]["profilepic"] ?>" 
											 class="rounded-circle mb-2" 
											 style="width: 100px; height: 100px; object-fit: cover;">
										<input type="file" name="new_profile_pic" class="form-control form-control-sm">
									</div>
									<div class="col-md-10">
										<div class="row">
											<div class="col-md-6 mb-3">
												<label class="form-label">First Name</label>
												<input type="text" name="user_name" class="form-control" 
													   value="<?php echo $_SESSION["user"]["user_name"] ?>">
											</div>
											<div class="col-md-6 mb-3">
												<label class="form-label">Last Name</label>
												<input type="text" name="surname" class="form-control" 
													   value="<?php echo $_SESSION["user"]["surname"] ?>">
											</div>
											<div class="col-md-6 mb-3">
												<label class="form-label">Email</label>
												<input type="email" name="email" class="form-control" 
													   value="<?php echo $_SESSION["user"]["email"] ?>">
											</div>
											<div class="col-md-6 mb-3">
												<label class="form-label">New Password (leave empty to keep current)</label>
												<input type="password" name="new_password" class="form-control">
											</div>
										</div>
									</div>
								</div>
								<div class="text-end">
									<button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<!-- Left Sidebar - Followers -->
			<div class="col-md-3">
				<!-- Followers Card -->
				<div class="card shadow-sm">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Your Followers</h5>
					</div>
					<div class="card-body">
						<?php
						$friends = getFriends($_SESSION["user"]["user_id"]);
						if(empty($friends)) {
							echo "<p class='text-muted text-center py-3'>You have no followers yet</p>";
						} else {
							foreach($friends as $f){
						?>
							<form action='delete.php' method='post' class="mb-3">
								<div class="d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center">
										<img src='images/<?= htmlspecialchars($f["profilepic"]) ?>' 
											 class="rounded-circle me-2" 
											 style="width: 40px; height: 40px; object-fit: cover;">
										<span><?= htmlspecialchars($f["user_name"]) . " " . htmlspecialchars($f["surname"]) ?></span>
									</div>
									<button type='submit' class='btn btn-danger btn-sm'>Unfollow</button>
									<input type='hidden' name='friend_id' value="<?= $f["friend_id"] ?>">
									<input type='hidden' name='friend2_id' value='<?= $userId ?>'>
								</div>
							</form>
						<?php 
							}
						}
						?>
					</div>
				</div>
			</div>

			<!-- Right Content - Your Posts -->
			<div class="col-md-9">
				<div class="card shadow-sm">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Your Posts</h5>
					</div>
					<div class="card-body">
						<?php
						$yourPosts = getYourPost($_SESSION["user"]["user_id"]);
						if(empty($yourPosts)){
							echo "<p class='text-muted text-center py-3'>You do not have any post yet!</p>";
						} else {
							foreach($yourPosts as $p){
							?>
								<div class="card mb-4">
									<div class="card-header bg-light">
										<div class="d-flex justify-content-between align-items-center">
											<div class="d-flex align-items-center">
												<img src='images/<?= $p["profilepic"] ?>' class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
												<span class="fw-bold"><?= $p["user_name"] . " " . $p["surname"] ?></span>
											</div>
											<form action='delete.php' method='POST'>
												<input type='hidden' name='delete_id' value='<?= $p["id"] ?>'>
												<input type='hidden' name='post_id' value='<?= $p["profilepic"] ?>'>
												<button type='submit' name='deleteBtn' class='btn btn-danger btn-sm'>Delete</button>
											</form>
										</div>
									</div>
									<div class="card-body">
										<?php
										if(!empty($p['post_text']) && !empty($p['post_img'])){
											echo "<p class='card-text mb-3'>" . $p["post_text"] . "</p>";
											echo "<img src='images/" . $p["post_img"] . "' class='img-fluid rounded profile-post-image'>";
										} else if(!empty($p['post_text']) && empty($p['post_img'])){
											echo "<p class='card-text'>" . $p["post_text"] . "</p>";
										} else if(empty($p['post_text']) && !empty($p['post_img'])){
											echo "<img src='images/" . $p["post_img"] . "' class='img-fluid rounded profile-post-image'>";
										}
										?>
									</div>
								</div>
							<?php
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if (isset($error_message)): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?php echo htmlspecialchars($error_message); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php endif; ?>

	<?php if (isset($success_message)): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<?php echo htmlspecialchars($success_message); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php endif; ?>

</body>

</html>
