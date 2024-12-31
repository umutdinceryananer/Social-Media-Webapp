<?php

require "./db.php";

session_start();

// Start output buffering to prevent early output
ob_start();

if (isset($_POST['senderId'])) {
	extract($_POST);
	try {
		$sql2 = "INSERT INTO notification (to_user_id,from_user_id,message) values (?,?,?)";
		$stmt = $db->prepare($sql2);
		$stmt->execute([$userId, $senderId, "wants to follow you"]);
		echo "<p> Request send to {$userId} !</p>";
		echo "<p> You are redirected to home page</p>";
	} catch (PDOException $e) {}
	exit; // Exit for AJAX requests
}

if (isset($_POST['follow'])) {
	extract($_POST);
	$result = addNotification($userId, $_POST['followId']);
	echo json_encode($result);
	exit;
}

// Process search
$searchResults = [];
$noResults = false;

if (isset($_POST["searchUser"])) {
	$search = $_POST["searchUser"];
	$inputs = explode(" ", $search);
	$user = $_POST["user"];

	$userData = $_SESSION["user"];
	$userId = $userData["user_id"];
	
	// Search logic remains the same
	$sql3 = "SELECT * from `user` WHERE user_name = ? AND user_id != ?";
	$stmt = $db->prepare($sql3);
	$stmt->execute([$inputs[0], $userId]);
	$rs = $stmt;
	
	if (isset($inputs[1])) {
		$sql = "SELECT * from `user` WHERE user_name = ? AND surname = ? AND user_id != ?";
		$stmt = $db->prepare($sql);
		$stmt->execute([$inputs[0], $inputs[1], $userId]);
		$rs = $stmt;
	}

	if ($rs->rowCount() == 0) {
		$sql2 = "SELECT * from `user` WHERE email = ? AND user_id != ?";
		$stmt = $db->prepare($sql2);
		$stmt->execute([$search, $userId]);
		$rs = $stmt;
	}

	$searchResults = $rs->fetchAll(PDO::FETCH_ASSOC);
	$noResults = ($rs->rowCount() == 0);
}

// Clear any buffered output
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Search Users - PicConnect</title>
	
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<!-- Bootstrap JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light min-vh-100">
	<!-- Navigation Bar -->
	<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
		<div class="container">
			<a class="navbar-brand" href="homePage.php">
				<img src="images/picconnect-logo.png" alt="PicConnect Logo" height="40">
			</a>
			
			<!-- Search Form -->
			<div class="d-flex flex-grow-1 justify-content-center">
				<form class="w-50" action="search.php" method="POST">
					<div class="input-group">
						<input type="search" class="form-control" name="searchUser" 
							   placeholder="Search for users..." aria-label="Search users">
						<input type="hidden" name="user" value="<?= $_SESSION['user']['user_id'] ?>">
						<button class="btn btn-outline-primary" type="submit">
							<i class="fas fa-search"></i>
						</button>
					</div>
				</form>
			</div>
			
			<!-- Home Button -->
			<a href="homePage.php" class="btn btn-outline-primary">
				<i class="fas fa-home me-2"></i>Home
			</a>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="container py-4">
		<?php if (isset($_POST["searchUser"])): ?>
			<!-- Search Results -->
			<div class="row justify-content-center">
				<div class="col-lg-8">
					<?php if (!empty($searchResults)): ?>
						<?php foreach ($searchResults as $row): ?>
							<div class="card mb-3 shadow-sm">
								<div class="card-body d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center">
										<img src="images/<?= htmlspecialchars($row["profilepic"]) ?>" 
											 class="rounded-circle me-3" 
											 width="50" height="50" 
											 style="object-fit: cover;"
											 alt="Profile picture">
										<div>
											<h6 class="mb-0">
												<?= htmlspecialchars($row["user_name"]) ?> 
												<?= htmlspecialchars($row["surname"]) ?>
											</h6>
										</div>
									</div>
									<button class="btn btn-primary follow" 
											data-id="<?= $row["user_id"] ?>" 
											data-sender="<?= $_SESSION['user']['user_id'] ?>">
										<i class="fas fa-user-plus me-2"></i>Follow
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="alert alert-info text-center">
							<i class="fas fa-search me-2"></i>No users found matching your search.
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php else: ?>
			<!-- Initial Search State -->
			<div class="row justify-content-center mt-5">
				<div class="col-lg-6 text-center">
					<i class="fas fa-search fa-3x text-muted mb-3"></i>
					<h4 class="text-muted">Search for users to connect with</h4>
					<p class="text-muted">Enter a name or email in the search bar above</p>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<script>
		$(document).ready(function() {
			$('.follow').on('click', function() {
				var $button = $(this);
				var followId = $button.data('id');
				var userId = $button.data('sender');
				
				$.ajax({
					url: 'search.php',
					type: 'post',
					data: {
						'follow': 1,
						'followId': followId,
						'userId': userId
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							$button.prop('disabled', true)
								   .removeClass('btn-primary')
								   .addClass('btn-secondary')
								   .html('<i class="fas fa-check me-2"></i>Request Sent');
							
							showAlert('success', response.message);
						} else {
							showAlert('warning', response.message);
						}
					},
					error: function() {
						showAlert('danger', 'An error occurred while sending the request.');
					}
				});
			});

			function showAlert(type, message) {
				const alert = `
					<div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;" role="alert">
						${message}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				`;
				
				$('body').append(alert);
				setTimeout(() => {
					$('.alert').alert('close');
				}, 3000);
			}
		});
	</script>
</body>

</html>