<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if post ID is provided
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    try {
        // Delete the post
        $sql = "DELETE FROM posts WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $post_id, ':user_id' => $_SESSION['user_id']]);
        header('Location: view_posts.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Error occurred. Please try again later.";
    }
} else {
    header('Location: view_posts.php');
    exit;
}
?>
