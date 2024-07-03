<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all posts
try {
    $sql = "SELECT posts.id, posts.title, posts.content, posts.image, posts.created_at, users.username 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY posts.created_at DESC";
    $stmt = $pdo->query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Error occurred. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>All Posts</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <div class="posts">
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p>by <?php echo htmlspecialchars($post['username']); ?> on <?php echo $post['created_at']; ?></p>
                    <?php if ($post['content']): ?>
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <?php elseif ($post['image']): ?>
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width:100%;">
                    <?php endif; ?>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts to display.</p>
        <?php endif; ?>
    </div>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
