<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId  = (int)currentUser()['id'];
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job'])) {
    $title   = trim($_POST['title']??'');
    $company = trim($_POST['company']??'');
    $loc     = trim($_POST['location']??'');
    $desc    = trim($_POST['description']??'');
    if ($title && $company && $desc) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('INSERT INTO jobs (user_id,title,company,location,description) VALUES (:u,:t,:c,:l,:d)');
        $stmt->execute([':u'=>$userId,':t'=>$title,':c'=>$company,':l'=>$loc,':d'=>$desc]);
        $success = 'Offre publiée !';
    } else $error = 'Remplissez tous les champs requis.';
}
$jobs = getJobs();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Emplois — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white fw-bold">💼 Publier une offre</div>
        <div class="card-body">
          <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-2"><input type="text" name="title" class="form-control form-control-sm" placeholder="Titre du poste *" required></div>
            <div class="mb-2"><input type="text" name="company" class="form-control form-control-sm" placeholder="Entreprise *" required></div>
            <div class="mb-2"><input type="text" name="location" class="form-control form-control-sm" placeholder="Lieu"></div>
            <div class="mb-3"><textarea name="description" class="form-control form-control-sm" rows="4" placeholder="Description du poste *" required></textarea></div>
            <button type="submit" name="add_job" class="btn btn-success w-100">Publier</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <h5 class="fw-bold mb-3">Offres disponibles</h5>
      <?php if (empty($jobs)): ?>
        <div class="alert alert-info">Aucune offre pour le moment.</div>
      <?php else: ?>
        <?php foreach ($jobs as $job): ?>
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h5 class="mb-1 fw-bold"><?= e($job['title']) ?></h5>
                  <p class="mb-1 text-primary fw-semibold"><?= e($job['company']) ?></p>
                  <?php if ($job['location']): ?><small class="text-muted">📍 <?= e($job['location']) ?></small><?php endif; ?>
                </div>
                <small class="text-muted"><?= date('d/m/Y',strtotime($job['created_at'])) ?></small>
              </div>
              <p class="mt-2 mb-0"><?= nl2br(e($job['description'])) ?></p>
              <small class="text-muted">Publié par <?= e($job['publisher_name']) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
