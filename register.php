<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
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

    try {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone_number, address, city, state, country, postal_code, gender, marital_status, age, employment) VALUES (:username, :email, :password, :first_name, :last_name, :phone_number, :address, :city, :state, :country, :postal_code, :gender, :marital_status, :age, :employment)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
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
            ':employment' => $employment
        ]);
        echo "Registration successful!";
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry
            echo "Username or email already exists.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <form action="register.php" method="POST">
        <h2>Register</h2>
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name">
        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name">
        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number">
        <label for="address">Address:</label>
        <input type="text" name="address">
        <label for="city">City:</label>
        <input type="text" name="city">
        <label for="state">State:</label>
        <input type="text" name="state">
        <label for="country">Country:</label>
        <input type="text" name="country">
        <label for="postal_code">Postal Code:</label>
        <input type="text" name="postal_code">
        <label for="gender">Gender:</label>
        <select name="gender">
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <label for="marital_status">Marital Status:</label>
        <select name="marital_status">
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="divorced">Divorced</option>
            <option value="widowed">Widowed</option>
        </select>
        <label for="age">Age:</label>
        <input type="number" name="age">
        <label for="employment">Employment:</label>
        <input type="text" name="employment">
        <button type="submit">Register</button>
    </form>
</body>
</html>
