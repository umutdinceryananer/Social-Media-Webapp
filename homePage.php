<?php
session_start();
require "db.php";
// check if the user authenticated before
if (!validSession()) {
	header("Location: login.php?error"); // redirect to login page
	exit;
}

$userData = $_SESSION["user"];
$userId = $userData["user_id"];
//posting starts here

if (isset($_POST['check'])) {
	extract($_POST);
	
	$result = addFriendship($_POST['fid'], $userId);
	
	if ($result['success']) {
		// Remove the notification
		$notif = getNotifications($userId);
		$stmt2 = $db->prepare("DELETE FROM notification WHERE id = ?");
		$stmt2->execute([$notif[$_POST['row']]['id']]);
		
		echo json_encode([
			'success' => true,
			'message' => $result['message']
		]);
	} else {
		echo json_encode([
			'success' => false,
			'message' => $result['message']
		]);
	}
	exit;
}

if (isset($_POST['uncheck'])) {
	extract($_POST);
	var_dump($_POST);
	$notif = getNotifications($userId);
	$stmt = $db->prepare("DELETE FROM notification WHERE id = ? ");
	$stmt->execute([$notif[$_POST['row']]['id']]);
}


if (isset($_POST["postBtn"])) {
	extract($_POST);
	$targetDir = "images/";
	$fileName = basename($_FILES["file"]["name"]);
	$targetFilePath = $targetDir . $fileName;
	$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
	$allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');

	if (!($_FILES['file']['name'] == "")) {
		if (in_array($fileType, $allowTypes)) {
			// Upload file to server
			if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
				// Check file size
				if ($_FILES["file"]["size"] < 500000) {
					// Insert image file name into database
					$sql = ("INSERT INTO posts (user_id,post_img, post_text) VALUES (?,?,?)");
					$stmt = $db->prepare($sql);

					$stmt->execute([$_SESSION["user"]["user_id"], $fileName, $textPost]);
				} else {
				}
			}
		} else {
		}
	} else {
		if (!empty($textPost)) {


			$sql = ("INSERT INTO posts (user_id, post_text) VALUES (?,?)");
			$stmt = $db->prepare($sql);
			$stmt->execute([$_SESSION["user"]["user_id"], $textPost]);
			$msg = "Post uploaded Successfully";
		} else {
		}
	}
}




if (isset($_POST['comment'])) {
	extract($_POST);
	
	$stmt = $db->prepare("INSERT INTO comment (post_id, user_id, text) VALUES (?, ?, ?)");
	$stmt->execute([$postid, $userId, $comment]);
	
	// Return the new comment HTML
	echo json_encode([
		'success' => true,
		'user_name' => $_SESSION['user']['user_name'],
		'profilepic' => $_SESSION['user']['profilepic'],
		'comment' => htmlspecialchars($comment)
	]);
	exit;
}

if (isset($_POST['liked'])) :
	extract($_POST);
	$postid = $_POST['postid'];

	$stmt = $db->query("SELECT * FROM posts WHERE id=$postid");

	$rs = $stmt->fetch();
	$n = $rs['like_count'];

	$st = $db->prepare("INSERT INTO likes (user_id, post_id) VALUES ($userId,$postid)");
	$st->execute();

	$db->query("UPDATE posts SET like_count= $n+1 WHERE id=$postid");
	echo $n + 1;
	exit;
endif;

if (isset($_POST['unliked'])) :
	extract($_POST);
	$postid = $_POST['postid'];

	$stmt = $db->query("SELECT * FROM posts WHERE id=$postid");

	$rs = $stmt->fetch();
	$n = $rs['like_count'];

	$st = $db->prepare("DELETE FROM likes WHERE user_id=$userId AND post_id=$postid");
	$st->execute();

	$db->query("UPDATE posts SET like_count= $n-1 WHERE id=$postid");
	echo $n - 1;
	exit;
endif;

if (isset($_POST['notification'])) :



	exit;
endif;


?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PicConnect</title>
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
	<!-- jQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
	<!-- Bootstrap JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
	<?php
	if (isset($msg)) {
		echo "<p class='msg'>", $msg, "</p>";
	}
	?>

	<!-- Navbar -->
	<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
		<div class="container">
			<!-- Logo Section -->
			<a class="navbar-brand" href="homePage.php">
				<img src="images/picconnect-logo.png" alt="PicConnect Logo" height="40">
			</a>
			
			<!-- Search Form Section -->
			<form id="searchForm" class="d-flex mx-auto col-lg-4 col-md-6" action="search.php" method="post">
				<div class="input-group">
					<input type="search" class="form-control border-end-0" name="searchUser" placeholder="Search for a friend">
					<button class="btn btn-outline-secondary border-start-0" type="submit">
						<i class="bi bi-search"></i>
					</button>
				</div>
				<input type='hidden' name='user' value="<?php echo $userData["user_id"]; ?>">
			</form>
			<!-- Profile Dropdown -->
			<div class="dropdown ms-3">
				<a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<img src="images/<?php echo ($_SESSION["user"]["profilepic"]) ?>" alt="Profile Picture" 
						 class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
					<span class="d-none d-sm-inline text-dark"><?php echo $_SESSION["user"]["user_name"] . ' ' . $_SESSION["user"]["surname"] ?></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-end shadow-sm">
					<li><a class="dropdown-item" href="homePage.php">
						<i class="bi bi-house-door me-2"></i>Home
					</a></li>
					<li><a class="dropdown-item" href="profile.php">
						<i class="bi bi-person me-2"></i>Profile
					</a></li>
					<li><hr class="dropdown-divider"></li>
					<li><a class="dropdown-item" href="logout.php">
						<i class="bi bi-box-arrow-right me-2"></i>Logout
					</a></li>
				</ul>
			</div>
			<!-- Add this right before the Profile Dropdown in the navbar -->
			<div class="dropdown me-3">
				<a class="nav-link position-relative text-dark" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<i class="fa fa-bell fs-5"></i>
					<?php 
					$sql = "SELECT * FROM notification WHERE to_user_id = ?";
					$stmt = $db->prepare($sql);
					$stmt->execute([$userId]);
					$notificationCount = $stmt->rowCount();
					if ($notificationCount > 0): 
					?>
						<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
							<?= $notificationCount ?>
						</span>
					<?php endif; ?>
				</a>

				<div class="dropdown-menu dropdown-menu-end shadow-sm py-0" style="width: 320px; max-height: 400px; overflow-y: auto;">
					<?php 
					$notifications = getNotifications($userId);
					if (empty($notifications)): 
					?>
						<div class="p-4 text-center text-muted">
							<i class="bi bi-bell-slash fs-3 mb-3 d-block"></i>
							<p class="mb-0">No new notifications</p>
						</div>
					<?php else: ?>
						<div class="border-bottom p-3 bg-light">
							<h6 class="mb-0 fw-semibold">Notifications</h6>
						</div>
						<div class="notification-list">
							<?php foreach ($notifications as $index => $notif): ?>
								<div class="dropdown-item notification-item py-3 border-bottom">
									<div class="d-flex align-items-center">
										<img src="images/<?= htmlspecialchars($notif['profilepic']) ?>" 
											 class="rounded-circle me-3" 
											 width="45" height="45" 
											 style="object-fit: cover;">
										<div class="flex-grow-1">
											<p class="mb-1 fw-semibold">
												<?= htmlspecialchars($notif['user_name']) ?> <?= htmlspecialchars($notif['surname']) ?>
												<span class="fw-normal text-muted">wants to follow you</span>
											</p>
											<div class="d-flex gap-2">
												<button class="btn btn-sm btn-success accept-request px-3" 
														data-fid="<?= $notif['from_user_id'] ?>" 
														data-row="<?= $index ?>">
													<i class="fa-solid fa-check me-2"></i>Accept
												</button>
												<button class="btn btn-sm btn-outline-secondary decline-request px-3" 
														data-fid="<?= $notif['from_user_id'] ?>" 
														data-row="<?= $index ?>">
													<i class="fa-solid fa-xmark me-2"></i>Decline
												</button>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>

	<!-- Main Content Area -->
	<div class="container py-4">
		<div class="row justify-content-center">
			<div class="col-lg-6 col-md-8">
				<!-- Create Post Card -->
				<div class="card shadow-sm mb-4 position-sticky" style="top: 70px; z-index: 100;">
					<div class="card-body p-3">
						<form action="" method="post" enctype="multipart/form-data">
							<textarea name="textPost" class="form-control border-0 mb-3" rows="3"
									placeholder="What's on your mind, <?php echo $_SESSION["user"]["user_name"]; ?>?"></textarea>
							<div class="d-flex gap-2">
								<input type="file" name="file" class="form-control form-control-sm">
								<button name="postBtn" class="btn btn-primary px-4">Post</button>
							</div>
						</form>
					</div>
				</div>

				<!-- Posts Feed -->
				<?php
				$posts = getPost($_SESSION["user"]["user_id"]);
				$perPage = 10; //Number of photos to display per page
				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$start = ($page - 1) * $perPage;
				$displayPosts = array_slice($posts, $start, $perPage);
				?>
				<div class='nextPost'>
					<?php foreach ($displayPosts as $p) { ?>
						<div class="card shadow-sm mb-4">
							<!-- Post Header -->
							<div class="card-header bg-white border-0 py-3">
								<div class="d-flex align-items-center">
									<img src="images/<?= $p["profilepic"]; ?>" 
										 class="rounded-circle me-2" 
										 width="40" 
										 height="40" 
										 style="object-fit: cover;">
									<div>
										<h6 class="mb-0"><?= htmlspecialchars($p["user_name"]) ?> <?= htmlspecialchars($p["surname"]); ?></h6>
										<small class="text-muted">
											<?= date('F j, Y, g:i a', strtotime($p["created_at"])) ?>
										</small>
									</div>
								</div>
							</div>

							<!-- Post Content -->
							<div class="card-body">
								<?php if (!empty($p['post_text'])) { ?>
									<p class="card-text mb-3"><?= htmlspecialchars($p["post_text"]) ?></p>
								<?php } ?>
								
								<?php if (!empty($p['post_img'])) { ?>
									<img src="images/<?= htmlspecialchars($p["post_img"]) ?>" 
										 class="img-fluid rounded mb-3" 
										 alt="Post image">
								<?php } ?>

								<!-- Like and Comment Section -->
								<div class="border-top pt-3">
									<div class="d-flex align-items-center mb-3">
										<?php
										$sql = "SELECT * FROM likes WHERE user_id=? AND post_id=?";
										$stmt = $db->prepare($sql);
										$stmt->execute([$userId, $p["id"]]);
										$isLiked = $stmt->rowCount() == 1;
										?>
										<div class="post me-3">
											<span class="<?= $isLiked ? 'unlike fa-solid' : 'like fa-regular' ?> fa-heart" 
												  data-id="<?= $p['id']; ?>" 
												  style="cursor: pointer;"></span>
											<span class="like_count ms-1"><?= $p['like_count']; ?> likes</span>
										</div>
									</div>

									<!-- Comment Input -->
									<div class="input-group mb-3">
										<textarea class="form-control" 
												id="comment" 
												placeholder="Add a comment..." 
												rows="1"></textarea>
										<button class="btn btn-outline-primary" 
												type="button" 
												data-uid="<?= $p['id']; ?>">
											<i class="bi bi-send"></i>
										</button>
									</div>

									<!-- Comments Display -->
									<div class="disp-com">
										<?php 
										$comments = getComment($p['id']);
										foreach ($comments as $c) {
											if (!empty($c['text']) && $c['post_id'] == $p['id']) { 
										?>
											<div class="d-flex align-items-start mb-2">
												<img src="images/<?= htmlspecialchars($c['profilepic']) ?>" 
													 class="rounded-circle me-2" 
													 width="24" 
													 height="24" 
													 style="object-fit: cover;">
												<div>
													<span class="fw-bold"><?= htmlspecialchars($c['user_name']) ?></span>
													<span class="ms-1"><?= htmlspecialchars($c['text']) ?></span>
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
					<?php } ?>

					<!-- Pagination -->
					<div class="d-flex justify-content-between mb-4">
						<?php if ($page > 1) { ?>
							<a href="?page=<?= $page - 1 ?>" class="btn btn-outline-primary">
								<i class="bi bi-chevron-left"></i> Previous Page
							</a>
						<?php } else { ?>
							<div></div>
						<?php } ?>
						
						<?php if (count($posts) > ($start + $perPage)) { ?>
							<a href="?page=<?= $page + 1 ?>" class="btn btn-outline-primary">
								Next Page <i class="bi bi-chevron-right"></i>
							</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		$(document).ready(function() {
			// Like/Unlike functionality
			$('.like, .unlike').on('click', function() {
				var postid = $(this).data('id');
				var $icon = $(this);
				var $likeCount = $icon.closest('.post').find('.like_count');
				
				if ($icon.hasClass('like')) {
					// Like action
					$.ajax({
						url: 'homePage.php',
						type: 'post',
						data: {
							'liked': 1,
							'postid': postid
						},
						success: function(response) {
							$likeCount.text(response + " likes");
							$icon.removeClass('like fa-regular').addClass('unlike fa-solid');
						}
					});
				} else {
					// Unlike action
					$.ajax({
						url: 'homePage.php',
						type: 'post',
						data: {
							'unliked': 1,
							'postid': postid
						},
						success: function(response) {
							$likeCount.text(response + " likes");
							$icon.removeClass('unlike fa-solid').addClass('like fa-regular');
						}
					});
				}
			});

			// Comment functionality
			$('.input-group button').on('click', function() {
				var $button = $(this);
				var $commentInput = $button.closest('.input-group').find('textarea');
				var $commentsContainer = $button.closest('.card-body').find('.disp-com');
				var postid = $button.data('uid');
				var comment = $commentInput.val();

				if (comment.trim() === "") {
					$commentInput.attr('placeholder', 'Cannot be empty!');
					return;
				}

				$.ajax({
					url: 'homePage.php',
					type: 'post',
					data: {
						'comment': comment,
						'postid': postid
					},
					success: function(response) {
						// Clear input and reset placeholder
						$commentInput.val('');
						$commentInput.attr('placeholder', 'Add a comment...');

						// Add new comment to the comments container
						var newComment = `
							<div class="d-flex align-items-start mb-2">
								<img src="images/<?= $_SESSION['user']['profilepic'] ?>" 
									 class="rounded-circle me-2" 
									 width="24" 
									 height="24" 
									 style="object-fit: cover;">
								<div>
									<span class="fw-bold"><?= $_SESSION['user']['user_name'] ?></span>
									<span class="ms-1">${comment}</span>
								</div>
							</div>
						`;
						$commentsContainer.append(newComment);
					}
				});
			});

			// Accept request
			$('.accept-request').on('click', function() {
				var $button = $(this);
				var fid = $button.data('fid');
				var row = $button.data('row');
				
				$.ajax({
					url: 'homePage.php',
					type: 'post',
					data: {
						'fid': fid,
						'check': 1,
						'row': row
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							$button.closest('.notification-item').fadeOut(300, function() {
								$(this).remove();
								updateNotificationCount();
							});
							
							// Show success message
							showAlert('success', response.message);
						} else {
							// Show error message
							showAlert('danger', response.message);
						}
					},
					error: function() {
						showAlert('danger', 'An error occurred while processing your request.');
					}
				});
			});

			// Decline request
			$('.decline-request').on('click', function() {
				var $button = $(this);
				var fid = $button.data('fid');
				var row = $button.data('row');
				
				$.ajax({
					url: 'homePage.php',
					type: 'post',
					data: {
						'fid': fid,
						'uncheck': 1,
						'row': row
					},
					success: function() {
						$button.closest('.notification-item').fadeOut(300, function() {
							$(this).remove();
							updateNotificationCount();
						});
					}
				});
			});

			// Function to update notification count
			function updateNotificationCount() {
				var notificationCount = $('.notification-item').length;
				if (notificationCount === 0) {
					// Make the selector more specific to target only the notification dropdown menu
					$('#notificationDropdown + .dropdown-menu').html(`
						<div class="p-4 text-center text-muted">
							<i class="bi bi-bell-slash fs-3 mb-3 d-block"></i>
							<p class="mb-0">No new notifications</p>
						</div>
					`);
					$('.notification-count').fadeOut();
				} else {
					$('.notification-count').text(notificationCount);
				}
			}

			// Add this function for showing alerts
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