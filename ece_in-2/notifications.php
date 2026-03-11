<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId        = (int)currentUser()['id'];
$notifications = getNotifications($userId);
markNotificationsRead($userId);
// Mettre à jour la session
$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=:id');
$stmt->execute([':id'=>$userId]);
$_SESSION['user'] = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Notifications — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4" style="max-width:700px;">
  <h4 class="fw-bold mb-4">🔔 Notifications</h4>
  <?php if (empty($notifications)): ?>
    <div class="card shadow-sm p-4 text-center text-muted">Aucune notification pour le moment.</div>
  <?php else: ?>
    <?php foreach ($notifications as $n): ?>
      <div class="card shadow-sm mb-2 <?= $n['is_read'] ? '' : 'border-primary border-2' ?>">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
          <span><?= e($n['message']) ?></span>
          <small class="text-muted ms-3 text-nowrap"><?= date('d/m H:i',strtotime($n['created_at'])) ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
