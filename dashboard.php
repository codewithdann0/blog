<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data from the database
try {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Error fetching user data.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    <p>First Name: <?php echo htmlspecialchars($user['first_name']); ?></p>
    <p>Last Name: <?php echo htmlspecialchars($user['last_name']); ?></p>
    <p>Phone Number: <?php echo htmlspecialchars($user['phone_number']); ?></p>
    <p>Address: <?php echo htmlspecialchars($user['address']); ?></p>
    <p>City: <?php echo htmlspecialchars($user['city']); ?></p>
    <p>State: <?php echo htmlspecialchars($user['state']); ?></p>
    <p>Country: <?php echo htmlspecialchars($user['country']); ?></p>
    <p>Postal Code: <?php echo htmlspecialchars($user['postal_code']); ?></p>
    <p>Gender: <?php echo htmlspecialchars($user['gender']); ?></p>
    <p>Marital Status: <?php echo htmlspecialchars($user['marital_status']); ?></p>
    <p>Age: <?php echo htmlspecialchars($user['age']); ?></p>
    <p>Employment: <?php echo htmlspecialchars($user['employment']); ?></p>
    <a href="view_posts.php">View All Posts</a><br>
    <a href="create_post.php">Create New Post</a><br>
    <a href="edit_profile.php">Edit Profile</a><br>
    <a href="logout.php">Logout</a>
</body>
</html>
