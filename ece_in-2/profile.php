<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId  = (int)currentUser()['id'];
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo  = getPDO();
    $name = trim($_POST['name']??'');
    $bio  = trim($_POST['bio']??'');
    $edu  = trim($_POST['education']??'');
    $exp  = trim($_POST['experience']??'');
    $stmt = $pdo->prepare('UPDATE users SET name=:n,bio=:b,education=:e,experience=:ex WHERE id=:id');
    if ($stmt->execute([':n'=>$name,':b'=>$bio,':e'=>$edu,':ex'=>$exp,':id'=>$userId])) {
        $stmt2 = $pdo->prepare('SELECT * FROM users WHERE id=:id');
        $stmt2->execute([':id'=>$userId]);
        $_SESSION['user'] = $stmt2->fetch();
        $success = 'Profil mis à jour !';
    }
}
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4" style="max-width:700px;">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white fw-bold">👤 Mon Profil</div>
    <div class="card-body">
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
      <div class="text-center mb-4">
        <div class="rounded-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      </div>
      <form method="POST">
        <div class="mb-3"><label class="form-label fw-bold">Nom</label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
        <div class="mb-3"><label class="form-label fw-bold">Email</label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled></div>
        <div class="mb-3"><label class="form-label fw-bold">Bio</label><textarea name="bio" class="form-control" rows="3"><?= e($user['bio']??'') ?></textarea></div>
        <div class="mb-3"><label class="form-label fw-bold">Formation</label><textarea name="education" class="form-control" rows="3" placeholder="Ex: ECE Paris — ING2, option IA"><?= e($user['education']??'') ?></textarea></div>
        <div class="mb-3"><label class="form-label fw-bold">Expériences</label><textarea name="experience" class="form-control" rows="3" placeholder="Ex: Stage Capgemini — Développeur Backend (2025)"><?= e($user['experience']??'') ?></textarea></div>
        <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
      </form>
      <div class="mt-3 text-center"><a href="cv.php" class="btn btn-outline-secondary">Générer mon CV</a></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
