<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $post_type = filter_var($_POST['post_type'], FILTER_SANITIZE_STRING);
    $content = $post_type === 'text' ? filter_var($_POST['content'], FILTER_SANITIZE_STRING) : null;
    $image = $post_type === 'image' ? $_FILES['image']['name'] : null;

    // Validate inputs
    if (empty($title)) {
        $error = "Title cannot be empty.";
    } elseif ($post_type === 'text' && empty($content)) {
        $error = "Content cannot be empty for text posts.";
    } elseif ($post_type === 'image' && empty($image)) {
        $error = "Please upload an image.";
    } else {
        // Handle file upload for image posts
        if ($post_type === 'image' && !empty($image)) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($image);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check file size
            if ($_FILES['image']['size'] > 5000000) {
                $error = "Sorry, your file is too large.";
            }

            // Allow certain file formats
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }

            // Check if $error is not set
            if (!isset($error)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // File is uploaded, now insert post into database
                    $image = $target_file;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        // If no errors, insert post into database
        if (!isset($error)) {
            try {
                $sql = "INSERT INTO posts (user_id, title, content, image) VALUES (:user_id, :title, :content, :image)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':title' => $title,
                    ':content' => $content,
                    ':image' => $image
                ]);
                $success = "Post created successfully!";
            } catch (PDOException $e) {
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
    <title>Create New Post</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Create New Post</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p style='color:green;'>$success</p>"; } ?>
    <form action="create_post.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" required>
        <label for="post_type">Post Type:</label>
        <select name="post_type" id="post_type" onchange="togglePostType()" required>
            <option value="text">Text</option>
            <option value="image">Image</option>
        </select>
        <div id="text_post">
            <label for="content">Content:</label>
            <textarea name="content" rows="10"></textarea>
        </div>
        <div id="image_post" style="display: none;">
            <label for="image">Upload Image:</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit">Create Post</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>

    <script>
        function togglePostType() {
            var postType = document.getElementById('post_type').value;
            if (postType === 'text') {
                document.getElementById('text_post').style.display = 'block';
                document.getElementById('image_post').style.display = 'none';
            } else if (postType === 'image') {
                document.getElementById('text_post').style.display = 'none';
                document.getElementById('image_post').style.display = 'block';
            }
        }
    </script>
</body>
</html>
