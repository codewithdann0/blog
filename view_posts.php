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
    $sql = "SELECT posts.id, posts.title, posts.content, posts.image, posts.created_at, users.username, posts.user_id 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY posts.created_at DESC";
    $stmt = $pdo->query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Error occurred. Please try again later.";
}

// Handle like submission
if (isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Check if user already liked the post
        $sql = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
        $like = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($like) {
            // User already liked the post, remove like
            $sql = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
        } else {
            // Add like
            $sql = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
        header('Location: view_posts.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Error occurred. Please try again later.";
    }
}

// Fetch likes count for each post
$likes = [];
try {
    $sql = "SELECT post_id, COUNT(*) as like_count FROM likes GROUP BY post_id";
    $stmt = $pdo->query($sql);
    $likes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Error occurred. Please try again later.";
}

// Fetch comments for each post
$comments = [];
try {
    $sql = "SELECT comments.id, comments.content, comments.created_at, users.username, comments.post_id 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            ORDER BY comments.created_at ASC";
    $stmt = $pdo->query($sql);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <script>
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Link copied to clipboard');
        }
    </script>
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
                    <?php if ($_SESSION['user_id'] == $post['user_id']): ?>
                        <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a>
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    <?php endif; ?>
                    
                    <!-- Display likes -->
                    <p><?php echo isset($likes[$post['id']]) ? $likes[$post['id']] : 0; ?> Likes</p>
                    <form action="view_posts.php" method="POST">
                        <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit">Like</button>
                    </form>

                    <!-- Share button -->
                    <button onclick="copyToClipboard('<?php echo "http://localhost/blog/view_post.php?id=" . $post['id']; ?>')">Share</button>

                    <!-- Display comments -->
                    <h4>Comments:</h4>
                    <?php foreach ($comments as $comment): ?>
                        <?php if ($comment['post_id'] == $post['id']): ?>
                            <div class="comment">
                                <p><?php echo htmlspecialchars($comment['username']); ?>: <?php echo htmlspecialchars($comment['content']); ?> <span>(<?php echo $comment['created_at']; ?>)</span></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Comment form -->
                    <form action="view_posts.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="comment_content" rows="3" placeholder="Write a comment..." required></textarea>
                        <button type="submit">Comment</button>
                    </form>
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
