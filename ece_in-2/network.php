<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId      = (int)currentUser()['id'];
$connections = getConnections($userId);
$connIds     = array_column($connections,'id');
$newMembers  = getNewMembers($userId, $connIds);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend'])) {
    addConnection($userId,(int)$_POST['friend_id']);
    header('Location: network.php'); exit;
}
$partners = [
    ['name'=>'Capgemini','url'=>'https://www.capgemini.com/fr-fr/carrieres/','icon'=>'💼','desc'=>'Conseil & IT'],
    ['name'=>'Thales','url'=>'https://www.thalesgroup.com/fr/global/candidats','icon'=>'🚀','desc'=>'Défense & Tech'],
    ['name'=>'Safran','url'=>'https://jobs.safran.com/','icon'=>'✈️','desc'=>'Aéronautique'],
    ['name'=>'Airbus','url'=>'https://www.airbus.com/fr/carrieres','icon'=>'🛩️','desc'=>'Aviation'],
    ['name'=>'EDF','url'=>'https://recrutement.edf.com/','icon'=>'⚡','desc'=>'Énergie'],
    ['name'=>'Société Générale','url'=>'https://careers.societegenerale.com/','icon'=>'🏦','desc'=>'Finance'],
    ['name'=>'Orange','url'=>'https://orange.jobs/','icon'=>'📡','desc'=>'Télécoms'],
    ['name'=>'Accenture','url'=>'https://www.accenture.com/fr-fr/careers','icon'=>'📊','desc'=>'Conseil'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Réseau — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">

  <h5 class="fw-bold mb-3">👥 Mes connexions (<?= count($connections) ?>)</h5>
  <?php if (empty($connections)): ?>
    <div class="alert alert-info mb-4">Vous n'avez pas encore de connexions.</div>
  <?php else: ?>
    <div class="row g-3 mb-4">
      <?php foreach ($connections as $c): ?>
        <div class="col-md-4 col-lg-3">
          <div class="card shadow-sm text-center h-100">
            <div class="card-body">
              <div class="rounded-circle bg-primary text-white mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;"><?= strtoupper(substr($c['name'],0,1)) ?></div>
              <h6 class="mb-0"><?= e($c['name']) ?></h6>
              <small class="text-muted"><?= e($c['email']) ?></small>
            </div>
            <div class="card-footer bg-transparent"><a href="user_profile.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Voir profil</a></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($newMembers)): ?>
    <h5 class="fw-bold mb-3">🔍 Découvrir des membres</h5>
    <div class="row g-3 mb-5">
      <?php foreach ($newMembers as $m): ?>
        <div class="col-md-4 col-lg-3">
          <div class="card shadow-sm text-center h-100">
            <div class="card-body">
              <div class="rounded-circle bg-secondary text-white mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;"><?= strtoupper(substr($m['name'],0,1)) ?></div>
              <h6 class="mb-0"><?= e($m['name']) ?></h6>
              <small class="text-muted"><?= e($m['email']) ?></small>
              <?php if ($m['bio']): ?><p class="small text-muted mt-1 mb-0"><?= e(mb_substr($m['bio'],0,50)) ?>…</p><?php endif; ?>
            </div>
            <div class="card-footer bg-transparent d-flex gap-1 justify-content-center">
              <a href="user_profile.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary">Profil</a>
              <form method="POST"><input type="hidden" name="friend_id" value="<?= $m['id'] ?>">
              <button type="submit" name="add_friend" class="btn btn-sm btn-primary">+ Ajouter</button></form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h5 class="fw-bold mb-3">🤝 Partenaires ECE Paris</h5>
  <p class="text-muted mb-3">Ces entreprises recrutent régulièrement des diplômés ECE Paris.</p>
  <div class="row g-3">
    <?php foreach ($partners as $p): ?>
      <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm h-100 border-top border-3 border-primary">
          <div class="card-body text-center">
            <div style="font-size:2rem;"><?= $p['icon'] ?></div>
            <h6 class="fw-bold mt-2 mb-0"><?= $p['name'] ?></h6>
            <small class="text-muted"><?= $p['desc'] ?></small>
          </div>
          <div class="card-footer bg-transparent text-center">
            <a href="<?= $p['url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">Voir les offres →</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
