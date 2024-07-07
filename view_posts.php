<?php
session_start();
include 'includes/db.php';
include 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch posts
try {
    $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Error occurred. Please try again later.";
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_content = htmlspecialchars(trim($_POST['comment_content']));
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id, ':content' => $comment_content]);

        // Fetch the post owner's user ID
        $sql = "SELECT user_id FROM posts WHERE id = :post_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            $post_owner_id = $post['user_id'];
            $message = "New comment on your post.";
            addNotification($post_owner_id, $message);
        }

        // Redirect to the same page to prevent form resubmission
        header('Location: view_posts.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Error occurred while adding the comment. Please try again later.";
    }
}

// Handle post like
if (isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $sql = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);

        // Fetch the post owner's user ID
        $sql = "SELECT user_id FROM posts WHERE id = :post_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            $post_owner_id = $post['user_id'];
            $message = "Your post has been liked.";
            addNotification($post_owner_id, $message);
        }

        // Redirect to the same page to prevent form resubmission
        header('Location: view_posts.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Error occurred while liking the post. Please try again later.";
    }
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
    <h2>Posts</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <p>Posted by: <?php echo htmlspecialchars($post['username']); ?> on <?php echo $post['created_at']; ?></p>
                <form action="view_posts.php" method="POST">
                    <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit">Like</button>
                </form>
                <form action="view_posts.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <textarea name="comment_content" placeholder="Add a comment" required></textarea>
                    <button type="submit">Comment</button>
                </form>
                <!-- Display comments for this post -->
                <?php
                $sql = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = :post_id ORDER BY comments.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':post_id' => $post['id']]);
                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="comments">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            <p>Comment by: <?php echo htmlspecialchars($comment['username']); ?> on <?php echo $comment['created_at']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
