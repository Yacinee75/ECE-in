<?php
session_start();
require_once __DIR__.'/../includes/functions.php';
requireLogin();
header('Content-Type: application/json');
$commentId = (int)($_POST['comment_id'] ?? 0);
$userId    = (int)currentUser()['id'];
echo json_encode(['success' => $commentId > 0 ? deleteComment($commentId, $userId) : false]);
