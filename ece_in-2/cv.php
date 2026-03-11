<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$user    = currentUser();
$userId  = (int)$user['id'];
$cvFile  = __DIR__.'/cv_data/cv_'.$userId.'.xml';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cv'])) {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=:id');
    $stmt->execute([':id'=>$userId]);
    $u = $stmt->fetch();
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><cv/>');
    $xml->addChild('name',     htmlspecialchars($u['name']??''));
    $xml->addChild('email',    htmlspecialchars($u['email']??''));
    $xml->addChild('bio',      htmlspecialchars($u['bio']??''));
    $xml->addChild('education',htmlspecialchars($u['education']??''));
    $xml->addChild('experience',htmlspecialchars($u['experience']??''));
    $xml->addChild('title',    htmlspecialchars($_POST['cv_title']??'CV ECE In'));
    $xml->asXML($cvFile);
    $success = 'CV généré !';
}
$cvData = null;
if (file_exists($cvFile)) {
    $cvData = simplexml_load_file($cvFile);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon CV — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    @media print { .no-print { display:none!important; } body { background:#fff; } }
  </style>
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4" style="max-width:800px;">
  <div class="no-print mb-3 d-flex gap-2">
    <form method="POST">
      <div class="input-group">
        <input type="text" name="cv_title" class="form-control form-control-sm" placeholder="Titre du CV" value="<?= e($user['cv_title']??'CV ECE In') ?>">
        <button type="submit" name="save_cv" class="btn btn-primary btn-sm">Générer mon CV</button>
      </div>
    </form>
    <?php if ($cvData): ?>
      <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨 Imprimer / PDF</button>
    <?php endif; ?>
  </div>
  <?php if ($success): ?><div class="alert alert-success no-print"><?= e($success) ?></div><?php endif; ?>

  <?php if ($cvData): ?>
    <div class="card shadow" id="cv-preview">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <div class="rounded-circle bg-primary text-white mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:70px;height:70px;font-size:1.8rem;"><?= strtoupper(substr((string)$cvData->name,0,1)) ?></div>
          <h3 class="fw-bold"><?= e((string)$cvData->name) ?></h3>
          <p class="text-muted"><?= e((string)$cvData->email) ?></p>
          <?php if ((string)$cvData->bio): ?><p class="fst-italic"><?= e((string)$cvData->bio) ?></p><?php endif; ?>
        </div>
        <?php if ((string)$cvData->education): ?>
          <h5 class="border-bottom pb-1 text-primary">🎓 Formation</h5>
          <p><?= nl2br(e((string)$cvData->education)) ?></p>
        <?php endif; ?>
        <?php if ((string)$cvData->experience): ?>
          <h5 class="border-bottom pb-1 text-primary">💼 Expériences</h5>
          <p><?= nl2br(e((string)$cvData->experience)) ?></p>
        <?php endif; ?>
        <p class="text-muted text-end mt-4 small">Généré via ECE In — <?= date('d/m/Y') ?></p>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info">Complétez votre <a href="profile.php">profil</a> puis cliquez sur "Générer mon CV".</div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
