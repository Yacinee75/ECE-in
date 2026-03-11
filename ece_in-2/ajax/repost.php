<?php
session_start();
require_once __DIR__.'/../includes/functions.php';
requireLogin();
header('Content-Type: application/json');
$postId  = (int)($_POST['post_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId  = (int)currentUser()['id'];
echo json_encode(['success' => $postId > 0 ? repostPost($userId, $postId, $comment) : false]);
