<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId  = (int)currentUser()['id'];
$error   = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $content    = trim($_POST['content']??'');
    $visibility = $_POST['visibility']??'public';
    if ($content !== '') { createPost($userId,$content,'event',$visibility); $success='Évènement publié !'; }
    else $error = 'Le contenu est requis.';
}
$events = getEvents();
$partners = [
    ['name'=>'Capgemini','url'=>'https://www.capgemini.com/fr-fr/carrieres/','icon'=>'💼'],
    ['name'=>'Thales','url'=>'https://www.thalesgroup.com/fr/global/candidats','icon'=>'🚀'],
    ['name'=>'Safran','url'=>'https://jobs.safran.com/','icon'=>'✈️'],
    ['name'=>'Airbus','url'=>'https://www.airbus.com/fr/carrieres','icon'=>'🛩️'],
    ['name'=>'EDF','url'=>'https://recrutement.edf.com/','icon'=>'⚡'],
    ['name'=>'Soc. Gén.','url'=>'https://careers.societegenerale.com/','icon'=>'🏦'],
    ['name'=>'Orange','url'=>'https://orange.jobs/','icon'=>'📡'],
    ['name'=>'Accenture','url'=>'https://www.accenture.com/fr-fr/careers','icon'=>'📊'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Évènements — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">📅 Créer un évènement</div>
        <div class="card-body">
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3"><label class="form-label">Description</label>
            <textarea name="content" class="form-control" rows="4" placeholder="Ex: Conférence IA — 15 mars 2026, amphi A, 14h…" required></textarea></div>
            <div class="mb-3"><label class="form-label">Visibilité</label>
            <select name="visibility" class="form-select"><option value="public">Public</option><option value="friends">Amis</option></select></div>
            <button type="submit" name="create_event" class="btn btn-primary w-100">Publier</button>
          </form>
        </div>
      </div>
      <div class="card shadow-sm mt-4">
        <div class="card-header bg-dark text-white fw-bold">🤝 Partenaires ECE Paris</div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <?php foreach ($partners as $p): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                <span><?= $p['icon'] ?> <?= $p['name'] ?></span>
                <a href="<?= $p['url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Offres</a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <h5 class="fw-bold mb-3">📅 Évènements</h5>
      <?php if (empty($events)): ?>
        <div class="alert alert-info">Aucun évènement. Soyez le premier à en créer un !</div>
      <?php else: ?>
        <?php foreach ($events as $ev): ?>
          <div class="card shadow-sm mb-3 border-start border-primary border-3">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px;height:38px;"><?= strtoupper(substr($ev['name'],0,1)) ?></div>
                <div><strong><?= e($ev['name']) ?></strong><br><small class="text-muted"><?= date('d/m/Y H:i',strtotime($ev['created_at'])) ?></small></div>
              </div>
              <p class="mb-0"><?= nl2br(e($ev['content'])) ?></p>
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
