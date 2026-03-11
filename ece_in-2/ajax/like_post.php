<?php
session_start();
require_once __DIR__.'/../includes/functions.php';
requireLogin();
header('Content-Type: application/json');
$postId = (int)($_POST['post_id'] ?? 0);
$user   = currentUser();
$userId = (int)$user['id'];
if ($postId <= 0) { echo json_encode(['success'=>false]); exit; }
$totalLikes = toggleLike($postId, $userId);
$author = getPostAuthor($postId);
if ($author && (int)$author['id'] !== $userId) {
    addNotification((int)$author['id'], $user['name'].' a aimé votre publication.');
}
echo json_encode(['success'=>true, 'totalLikes'=>$totalLikes]);
