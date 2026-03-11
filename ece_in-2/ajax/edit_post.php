<?php
session_start();
require_once __DIR__.'/../includes/functions.php';
requireLogin();
header('Content-Type: application/json');
$postId  = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$userId  = (int)currentUser()['id'];
if ($postId <= 0 || $content === '') { echo json_encode(['success'=>false]); exit; }
$result = editPost($postId, $userId, $content);
echo json_encode(['success'=>$result, 'content'=>e($content)]);
