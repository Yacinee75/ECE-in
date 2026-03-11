<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$query   = trim($_GET['q']??'');
$results = $query !== '' ? searchUsers($query) : [];
$userId  = (int)currentUser()['id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Recherche — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <h5 class="fw-bold mb-3">🔍 Résultats pour « <?= e($query) ?> »</h5>
  <?php if (empty($results)): ?>
    <div class="alert alert-info">Aucun membre trouvé.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($results as $u): ?>
        <div class="col-md-4">
          <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;font-size:1.2rem;"><?= strtoupper(substr($u['name'],0,1)) ?></div>
              <div><h6 class="mb-0"><?= e($u['name']) ?></h6><small class="text-muted"><?= e($u['email']) ?></small></div>
            </div>
            <div class="card-footer bg-transparent text-end">
              <?php if ((int)$u['id'] !== $userId): ?>
                <a href="user_profile.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">Voir le profil</a>
              <?php else: ?>
                <a href="profile.php" class="btn btn-sm btn-outline-secondary">Mon profil</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
