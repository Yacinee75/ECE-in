<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId    = (int)currentUser()['id'];
$profileId = (int)($_GET['id'] ?? 0);
if ($profileId <= 0 || $profileId === $userId) { header('Location: profile.php'); exit; }
$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=:id');
$stmt->execute([':id'=>$profileId]);
$profile = $stmt->fetch();
if (!$profile) { header('Location: dashboard.php'); exit; }
// Vérifier connexion existante
$chk = $pdo->prepare('SELECT id FROM connections WHERE (user_id=:u1 AND friend_id=:f1) OR (user_id=:f2 AND friend_id=:u2)');
$chk->execute([':u1'=>$userId,':f1'=>$profileId,':f2'=>$profileId,':u2'=>$userId]);
$isConnected = (bool)$chk->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend']) && !$isConnected) {
    addConnection($userId,$profileId);
    header('Location: user_profile.php?id='.$profileId); exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= e($profile['name']) ?> — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4" style="max-width:700px;">
  <div class="card shadow-sm">
    <div class="card-body text-center">
      <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;"><?= strtoupper(substr($profile['name'],0,1)) ?></div>
      <h4 class="fw-bold"><?= e($profile['name']) ?></h4>
      <p class="text-muted"><?= e($profile['email']) ?></p>
      <?php if ($profile['bio']): ?><p><?= nl2br(e($profile['bio'])) ?></p><?php endif; ?>
      <div class="d-flex gap-2 justify-content-center mt-3">
        <?php if ($isConnected): ?>
          <span class="btn btn-success disabled">✅ Connecté</span>
          <a href="messages.php?contact=<?= $profileId ?>" class="btn btn-outline-primary">✉ Message</a>
        <?php else: ?>
          <form method="POST"><button type="submit" name="add_friend" class="btn btn-primary">+ Ajouter</button></form>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($profile['education'] || $profile['experience']): ?>
      <div class="card-footer bg-light">
        <?php if ($profile['education']): ?>
          <h6 class="fw-bold">🎓 Formation</h6><p><?= nl2br(e($profile['education'])) ?></p>
        <?php endif; ?>
        <?php if ($profile['experience']): ?>
          <h6 class="fw-bold">💼 Expériences</h6><p><?= nl2br(e($profile['experience'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
