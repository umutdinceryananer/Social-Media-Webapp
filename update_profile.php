<?php
session_start();
require "db.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST["update_profile"])) {
    try {
        $user_id = $_SESSION["user"]["user_id"];
        $user_name = trim($_POST["user_name"]);
        $surname = trim($_POST["surname"]);
        $email = trim($_POST["email"]);
        $new_password = $_POST["new_password"] ?? '';

        // Basic validation
        if (empty($user_name) || empty($surname) || empty($email)) {
            throw new Exception("Required fields cannot be empty");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists (excluding current user)
        $emailCheck = $db->prepare("SELECT user_id FROM user WHERE email = ? AND user_id != ?");
        $emailCheck->execute([$email, $user_id]);
        if ($emailCheck->rowCount() > 0) {
            throw new Exception("Email already in use by another account");
        }

        // Start with basic query
        $sql = "UPDATE user SET user_name = :user_name, surname = :surname, email = :email";
        $params = [
            ':user_name' => $user_name,
            ':surname' => $surname,
            ':email' => $email
        ];

        // Add password if provided
        if (!empty($new_password)) {
            // Password validation
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
                throw new Exception("Password must contain at least 8 characters, including uppercase, lowercase, number and special character");
            }
            $sql .= ", password = :password";
            $params[':password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Handle profile picture
        if (isset($_FILES["new_profile_pic"]) && $_FILES["new_profile_pic"]["error"] == 0) {
            $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
            $filename = $_FILES["new_profile_pic"]["name"];
            $filetype = $_FILES["new_profile_pic"]["type"];
            $filesize = $_FILES["new_profile_pic"]["size"];

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!array_key_exists($ext, $allowed)) {
                throw new Exception("Invalid file format. Only JPG, JPEG, PNG & GIF allowed.");
            }

            if ($filesize > 5 * 1024 * 1024) {
                throw new Exception("File size must be less than 5MB");
            }

            $new_filename = uniqid() . "." . $ext;
            
            if (move_uploaded_file($_FILES["new_profile_pic"]["tmp_name"], "images/" . $new_filename)) {
                // Delete old profile picture if it exists and is not default
                if (isset($_SESSION["user"]["profilepic"]) && 
                    $_SESSION["user"]["profilepic"] != "default.jpg" && 
                    file_exists("images/" . $_SESSION["user"]["profilepic"])) {
                    unlink("images/" . $_SESSION["user"]["profilepic"]);
                }
                
                $sql .= ", profilepic = :profilepic";
                $params[':profilepic'] = $new_filename;
            }
        }

        // Add WHERE clause
        $sql .= " WHERE user_id = :user_id";
        $params[':user_id'] = $user_id;

        // Prepare and execute the query
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            // Update session data
            $_SESSION["user"]["user_name"] = $user_name;
            $_SESSION["user"]["surname"] = $surname;
            $_SESSION["user"]["email"] = $email;
            if (isset($new_filename)) {
                $_SESSION["user"]["profilepic"] = $new_filename;
            }

            header("Location: profile.php?success=1");
            exit();
        } else {
            throw new Exception("Update failed. Please try again.");
        }

    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        header("Location: profile.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}