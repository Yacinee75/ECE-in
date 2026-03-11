<?php
session_start();
require_once __DIR__.'/../includes/functions.php';
requireLogin();
header('Content-Type: application/json');
$postId  = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$user    = currentUser();
$userId  = (int)$user['id'];
if ($postId <= 0 || $content === '') { echo json_encode(['success'=>false]); exit; }
$result = addComment($postId, $userId, $content);
if ($result) {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT id FROM comments WHERE post_id=:p AND user_id=:u ORDER BY id DESC LIMIT 1');
    $stmt->execute([':p'=>$postId,':u'=>$userId]);
    $row = $stmt->fetch();
    $author = getPostAuthor($postId);
    if ($author && (int)$author['id'] !== $userId) {
        addNotification((int)$author['id'], $user['name'].' a commenté votre publication.');
    }
    echo json_encode(['success'=>true,'comment_id'=>$row?$row['id']:0,'name'=>e($user['name']),'initial'=>strtoupper(substr($user['name'],0,1)),'content'=>e($content)]);
} else {
    echo json_encode(['success'=>false]);
}
