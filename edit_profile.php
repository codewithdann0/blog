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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $state = filter_var($_POST['state'], FILTER_SANITIZE_STRING);
    $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
    $postal_code = filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING);
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $marital_status = filter_var($_POST['marital_status'], FILTER_SANITIZE_STRING);
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $employment = filter_var($_POST['employment'], FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($age === false) {
        $error = "Invalid age.";
    } else {
        try {
            $sql = "UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, phone_number = :phone_number, address = :address, city = :city, state = :state, country = :country, postal_code = :postal_code, gender = :gender, marital_status = :marital_status, age = :age, employment = :employment WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone_number' => $phone_number,
                ':address' => $address,
                ':city' => $city,
                ':state' => $state,
                ':country' => $country,
                ':postal_code' => $postal_code,
                ':gender' => $gender,
                ':marital_status' => $marital_status,
                ':age' => $age,
                ':employment' => $employment,
                ':id' => $_SESSION['user_id']
            ]);
            $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // Duplicate entry
                $error = "Username or email already exists.";
            } else {
                error_log($e->getMessage());
                $error = "Error occurred. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Edit Profile</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p style='color:green;'>$success</p>"; } ?>
    <form action="edit_profile.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
        <label for="address">Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
        <label for="city">City:</label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
        <label for="state">State:</label>
        <input type="text" name="state" value="<?php echo htmlspecialchars($user['state']); ?>">
        <label for="country">Country:</label>
        <input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>">
        <label for="postal_code">Postal Code:</label>
        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code']); ?>">
        <label for="gender">Gender:</label>
        <select name="gender">
            <option value="male" <?php if ($user['gender'] == 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($user['gender'] == 'female') echo 'selected'; ?>>Female</option>
            <option value="other" <?php if ($user['gender'] == 'other') echo 'selected'; ?>>Other</option>
        </select>
        <label for="marital_status">Marital Status:</label>
        <select name="marital_status">
            <option value="single" <?php if ($user['marital_status'] == 'single') echo 'selected'; ?>>Single</option>
            <option value="married" <?php if ($user['marital_status'] == 'married') echo 'selected'; ?>>Married</option>
            <option value="divorced" <?php if ($user['marital_status'] == 'divorced') echo 'selected'; ?>>Divorced</option>
            <option value="widowed" <?php if ($user['marital_status'] == 'widowed') echo 'selected'; ?>>Widowed</option>
        </select>
        <label for="age">Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>">
        <label for="employment">Employment:</label>
        <input type="text" name="employment" value="<?php echo htmlspecialchars($user['employment']); ?>">
        <button type="submit">Update Profile</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
