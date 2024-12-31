<?php

$dsn = "mysql:host=localhost;dbname=picconnect;charset=utf8mb4;port=3306";
$user = "root";
$pass = "";

// $dsn = "mysql:host=sql309.infinityfree.com;dbname=if0_37979210_picconnect;charset=utf8mb4;port=3306";
// $user = "if0_37979210";
// $pass = "RlWakv2Ytnn7";

try {
	$db = new PDO($dsn, $user, $pass);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
	echo $ex->getMessage();
	echo "<p>Error occured try later.</p>";
	exit;
}

function checkUser($email, $pass)
{
	global $db;
	$user = getUser($email);
	if ($user) {
		return password_verify($pass, $user["password"]);
	}
	return false;
}

function validSession()
{
	return isset($_SESSION["user"]);
}

function getUser($email)
{
	global $db;
	$stmt = $db->prepare("SELECT * FROM user WHERE email=?");
	$stmt->execute([$email]);
	return $stmt->fetch();
}

function getPost($userId)
{
	global $db;
	
	$sql = "SELECT posts.*, user.user_name, user.surname, user.profilepic 
			FROM posts 
			JOIN user ON posts.user_id = user.user_id 
			WHERE posts.user_id = ? 
				OR posts.user_id IN (
					SELECT followed_id 
					FROM friends 
					WHERE follower_id = ?
				)
			ORDER BY posts.id DESC";
			
	$stmt = $db->prepare($sql);
	$stmt->execute([$userId, $userId]);
	return $stmt->fetchAll();
}
function getYourPost($id)
{
	global $db;
	$stmt = $db->prepare("SELECT * FROM posts JOIN user ON posts.user_id = user.user_id WHERE posts.user_id=? AND user.user_id = posts.user_id ORDER BY id DESC");
	$stmt->execute([$id]);
	return $stmt->fetchAll();
}

function getFriends($id)
{
	global $db;
	$stmt = $db->prepare("
		SELECT DISTINCT u.user_name, u.surname, u.profilepic, f.friend_id,
			CASE 
				WHEN f.follower_id = ? THEN f.followed_id
				ELSE f.follower_id
			END as friend_user_id
		FROM friends f
		JOIN user u ON (
			CASE 
				WHEN f.follower_id = ? THEN f.followed_id
				ELSE f.follower_id
			END = u.user_id
		)
		WHERE f.follower_id = ? OR f.followed_id = ?
	");
	$stmt->execute([$id, $id, $id, $id]);
	return $stmt->fetchAll();
}

function getNotifications($id)
{
	global $db;
	$stmt = $db->prepare("SELECT * FROM notification JOIN user ON from_user_id = user.user_id WHERE to_user_id =? ");
	$stmt->execute([$id]);
	return $stmt->fetchAll();
}


function deletePost($post_id)
{
	global $db;
	$stmt = $db->prepare("DELETE FROM posts WHERE id=?");
	$stmt->execute([$post_id]);
}
function deleteFriend($fr_id)
{
	global $db;
	$stmt = $db->prepare("DELETE FROM friends WHERE friend_id=?");
	$stmt->execute([$fr_id]);
}


function getComment($post_id)
{
	global $db;
	$stmt = $db->prepare("SELECT * FROM comment join posts on comment.post_id=posts.id join user on comment.user_id=user.user_id WHERE comment.post_id=?");
	$stmt->execute([$post_id]);
	return $stmt->fetchAll();
}

function checkExistingFriendship($userId, $friendId) {
	global $db;
	
	$sql = "SELECT * FROM friends 
			WHERE (follower_id = ? AND followed_id = ?) 
			OR (follower_id = ? AND followed_id = ?)";
			
	$stmt = $db->prepare($sql);
	$stmt->execute([$userId, $friendId, $friendId, $userId]);
	return $stmt->rowCount() > 0;
}

function addFriendship($followerId, $followedId) {
	global $db;
	
	try {
		// Start transaction
		$db->beginTransaction();
		
		// Check if friendship already exists in either direction
		$stmt = $db->prepare("SELECT COUNT(*) FROM friends 
							 WHERE (follower_id = ? AND followed_id = ?)
							 OR (follower_id = ? AND followed_id = ?)");
		$stmt->execute([$followerId, $followedId, $followedId, $followerId]);
		$count = $stmt->fetchColumn();
		
		if ($count > 0) {
			$db->rollBack();
			return [
				'success' => false,
				'message' => 'You are already friends with this user.'
			];
		}
		
		// Add new friendship
		$stmt = $db->prepare("INSERT INTO friends(follower_id, followed_id) VALUES (?, ?)");
		$stmt->execute([$followerId, $followedId]);
		
		// Commit transaction
		$db->commit();
		
		return [
			'success' => true,
			'message' => 'Friend request accepted successfully!'
		];
	} catch (PDOException $e) {
		// Rollback transaction on error
		$db->rollBack();
		
		return [
			'success' => false,
			'message' => 'An error occurred while processing your request.'
		];
	}
}

function removeFriendship($userId, $friendId) {
	global $db;
	
	try {
		$stmt = $db->prepare("DELETE FROM friends 
							 WHERE (follower_id = ? AND followed_id = ?) 
							 OR (follower_id = ? AND followed_id = ?)");
		$stmt->execute([$userId, $friendId, $friendId, $userId]);
		
		return [
			'success' => true,
			'message' => 'Friend removed successfully!'
		];
	} catch (PDOException $e) {
		return [
			'success' => false,
			'message' => 'An error occurred while removing the friend.'
		];
	}
}

function checkExistingNotification($fromUserId, $toUserId) {
	global $db;
	
	$sql = "SELECT * FROM notification 
			WHERE from_user_id = ? AND to_user_id = ?";
			
	$stmt = $db->prepare($sql);
	$stmt->execute([$fromUserId, $toUserId]);
	return $stmt->rowCount() > 0;
}

function addNotification($fromUserId, $toUserId) {
	global $db;
	
	try {
		// Check if there's already a pending notification
		if (checkExistingNotification($fromUserId, $toUserId)) {
			return [
				'success' => false,
				'message' => 'Follow request already sent.'
			];
		}
		
		// Check if they're already friends
		if (checkExistingFriendship($fromUserId, $toUserId)) {
			return [
				'success' => false,
				'message' => 'You are already friends with this user.'
			];
		}
		
		// Add new notification
		$stmt = $db->prepare("INSERT INTO notification(from_user_id, to_user_id) VALUES (?, ?)");
		$stmt->execute([$fromUserId, $toUserId]);
		
		return [
			'success' => true,
			'message' => 'Follow request sent successfully!'
		];
	} catch (PDOException $e) {
		// Handle unique constraint violation
		if ($e->getCode() == '23000') {
			return [
				'success' => false,
				'message' => 'Follow request already sent.'
			];
		}
		
		return [
			'success' => false,
			'message' => 'An error occurred while sending the request.'
		];
	}
}

function updateUserProfile($userId, $data) {
	global $db;
	
	try {
		$updates = [];
		$params = [];
		
		// Handle profile picture upload
		if (isset($_FILES['new_profile_pic']) && $_FILES['new_profile_pic']['error'] === 0) {
			$fileName = basename($_FILES['new_profile_pic']['name']);
			$targetDir = "images/";
			$targetFile = $targetDir . $fileName;
			
			// Get file extension
			$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
			
			// Generate unique filename to prevent overwriting
			$uniqueFileName = uniqid() . '.' . $imageFileType;
			$targetFile = $targetDir . $uniqueFileName;
			
			// Check file type
			if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
				if (move_uploaded_file($_FILES['new_profile_pic']['tmp_name'], $targetFile)) {
					// Delete old profile picture if it exists and isn't default
					$stmt = $db->prepare("SELECT profilepic FROM user WHERE user_id = ?");
					$stmt->execute([$userId]);
					$oldPic = $stmt->fetchColumn();
					
					if ($oldPic && $oldPic !== 'default.jpg' && file_exists($targetDir . $oldPic)) {
						unlink($targetDir . $oldPic);
					}
					
					$updates[] = "profilepic = ?";
					$params[] = $uniqueFileName;
				}
			}
		} else {
			// If no new profile picture is uploaded and user doesn't have a profile picture
			$stmt = $db->prepare("SELECT profilepic FROM user WHERE user_id = ?");
			$stmt->execute([$userId]);
			$currentPic = $stmt->fetchColumn();
			
			if (!$currentPic || empty($currentPic)) {
				$updates[] = "profilepic = ?";
				$params[] = 'default.jpg';
			}
		}
		
		// Add other fields to update
		$fields = ['user_name', 'surname', 'email'];
		foreach ($fields as $field) {
			if (isset($data[$field]) && !empty($data[$field])) {
				$updates[] = "$field = ?";
				$params[] = $data[$field];
			}
		}
		
		// Handle password update with validation
		if (!empty($data['new_password'])) {
			// Password validation
			$password = $data['new_password'];
			$pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
			
			if (!preg_match($pattern, $password)) {
				return [
					'success' => false,
					'message' => 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character'
				];
			}
			
			$updates[] = "password = ?";
			$params[] = password_hash($password, PASSWORD_DEFAULT);
		}
		
		if (empty($updates)) {
			return [
				'success' => false,
				'message' => 'No changes to update'
				];
		}
		
		// Add userId to params
		$params[] = $userId;
		
		$sql = "UPDATE user SET " . implode(", ", $updates) . " WHERE user_id = ?";
		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		
		// Get updated user data
		$stmt = $db->prepare("SELECT * FROM user WHERE user_id = ?");
		$stmt->execute([$userId]);
		$updatedUser = $stmt->fetch();
		
		// Update session
		$_SESSION['user'] = $updatedUser;
		
		return [
			'success' => true,
			'message' => 'Profile updated successfully',
			'user' => $updatedUser
		];
	} catch (PDOException $e) {
		return [
			'success' => false,
			'message' => 'An error occurred while updating profile'
		];
	}
}
