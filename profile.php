<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
try {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Error occurred. Please try again later.";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_STRING);
    $martial_status = filter_var($_POST['martial_status'], FILTER_SANITIZE_STRING);
    $age = filter_var($_POST['age'], FILTER_SANITIZE_NUMBER_INT);
    $employment = filter_var($_POST['employment'], FILTER_SANITIZE_STRING);
    
    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $profile_picture = $upload_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    try {
        $sql = "UPDATE users SET username = :username, email = :email, bio = :bio, martial_status = :martial_status, age = :age, employment = :employment, profile_picture = :profile_picture WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':bio' => $bio,
            ':martial_status' => $martial_status,
            ':age' => $age,
            ':employment' => $employment,
            ':profile_picture' => $profile_picture,
            ':id' => $_SESSION['user_id']
        ]);
        $success = "Profile updated successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Error occurred. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>User Profile</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p style='color:green;'>$success</p>"; } ?>
    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div>
            <label for="bio">Bio:</label>
            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
        </div>
        <div>
            <label for="martial_status">Marital Status:</label>
            <input type="text" id="martial_status" name="martial_status" value="<?php echo htmlspecialchars($user['martial_status']); ?>">
        </div>
        <div>
            <label for="age">Age:</label>
            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>">
        </div>
        <div>
            <label for="employment">Employment:</label>
            <input type="text" id="employment" name="employment" value="<?php echo htmlspecialchars($user['employment']); ?>">
        </div>
        <div>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture">
            <?php if ($user['profile_picture']): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="max-width:100px;">
            <?php endif; ?>
        </div>
        <button type="submit">Update Profile</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
