# PicConnect - Social Media Web Application

## Overview
PicConnect is a social media web application built with PHP and MySQL that allows users to connect, share posts, and interact with friends. The platform provides features similar to popular social networks while maintaining a clean and user-friendly interface.

🌐 **Live Demo:** [picconnect.unaux.com](http://picconnect.unaux.com)

## Features

### User Authentication
- **Registration System**
  - Secure password requirements (8+ characters, uppercase, lowercase, number, special character)
  - Age verification (13+ requirement)
  - Profile picture upload
  - Email validation

- **Login System**
  - Session-based authentication
  - Secure password handling

### Core Functionality

#### Profile Management
- Custom profile pictures
- Profile information editing
- Password update with security requirements
- Bio and personal information

#### Social Features
- **Posts**
  - Text posts
  - Image uploads
  - Post deletion
  - Timeline view

- **Friend System**
  - Friend requests
  - Accept/Decline functionality
  - Friend list management
  - Remove friends

- **Notifications**
  - Friend request notifications
  - Real-time notification counter
  - Interactive notification center

#### Search System
- User search by name or email
- Interactive follow buttons

## Technical Stack

### Frontend
- HTML5
- Bootstrap 5
- JavaScript/jQuery
- AJAX for asynchronous operations
- Font Awesome icons

### Backend
- PHP
- MySQL
- PDO for database operations
- Session management

### Security Features
- Password hashing
- SQL injection prevention
- XSS protection
- Input validation and sanitization

## Installation

1. **Prerequisites**
   - PHP 7.4 or higher
   - MySQL/MariaDB
   - Web server (Apache/Nginx)

2. **Database Setup**
   ```sql
   CREATE DATABASE picconnect;
   USE picconnect;
   ```

3. **Configuration**
   - Clone the repository
   - Configure database connection in `db.php`

4. **File Structure**
   ```
   PicConnect/
   ├── FriendHub/
   │   ├── images/
   │   ├── db.php
   │   ├── index.php
   │   ├── login.php
   │   ├── register.php
   │   ├── homePage.php
   │   ├── profile.php
   │   ├── search.php
   │   └── logout.php
   ```

## Usage

1. **Registration**
   - Navigate to register.php
   - Fill in required information
   - Upload profile picture (optional)
   - Submit registration form

2. **Login**
   - Use registered email and password
   - Access the platform features

3. **Social Interaction**
   - Create posts
   - Follow users
   - Like and comment
   - Manage friend requests

## Security Considerations

- Passwords are hashed using PHP's password_hash()
- Prepared statements for all database queries
- Input sanitization for user-submitted content
- Session security measures
- File upload restrictions

## Live Website

The project is currently live and can be accessed at [picconnect.unaux.com](http://picconnect.unaux.com). Feel free to create an account and explore the features!

## Support

For support, create an issue in the repository.

---

**Note:** This project is for educational purposes and demonstrates basic social media functionality.