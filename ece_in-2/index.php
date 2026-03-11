<?php
session_start();
require_once 'includes/functions.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        if (loginUser(trim($_POST['email']??''), $_POST['password']??'')) { header('Location: dashboard.php'); exit; }
        $error = 'Email ou mot de passe incorrect.';
    }
    if (isset($_POST['register'])) {
        $name  = trim($_POST['reg_name']??'');
        $email = trim($_POST['reg_email']??'');
        $pass  = $_POST['reg_password']??'';
        if (!str_ends_with(strtolower($email),'@ece.fr')) { $error = 'Seules les adresses @ece.fr sont autorisées.'; }
        elseif ($name===''||$email===''||$pass==='')       { $error = 'Tous les champs sont requis.'; }
        elseif (registerUser($name,$email,$pass))           { $success = 'Compte créé ! Vous pouvez vous connecter.'; }
        else                                                { $error = 'Cet email est déjà utilisé.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>ECE In — Réseau Professionnel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand fw-bold fs-4"><span class="text-primary">ECE</span> In</span>
    <span class="text-light small">Le réseau professionnel de la communauté ECE Paris</span>
  </div>
</nav>

<!-- Carrousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="d-flex align-items-center justify-content-center text-white" style="height:300px;background:linear-gradient(135deg,#0d6efd,#0a58ca);">
        <div class="text-center px-3"><h2 class="fw-bold">Bienvenue sur ECE In</h2><p class="lead">Le réseau social professionnel des étudiants et alumni ECE Paris</p></div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="d-flex align-items-center justify-content-center text-white" style="height:300px;background:linear-gradient(135deg,#198754,#157347);">
        <div class="text-center px-3"><h2 class="fw-bold">Développez votre réseau</h2><p class="lead">Connectez-vous avec des professionnels issus de la communauté ECE</p></div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="d-flex align-items-center justify-content-center text-white" style="height:300px;background:linear-gradient(135deg,#dc3545,#b02a37);">
        <div class="text-center px-3"><h2 class="fw-bold">Trouvez votre futur emploi</h2><p class="lead">Accédez aux offres de nos partenaires recruteurs</p></div>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</div>

<div class="container py-5">
  <div class="row g-4 justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header bg-primary text-white fw-bold">Connexion</div>
        <div class="card-body">
          <?php if ($error && isset($_POST['login'])): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
            <button type="submit" name="login" class="btn btn-primary w-100">Se connecter</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header bg-success text-white fw-bold">Créer un compte</div>
        <div class="card-body">
          <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div>
          <?php elseif ($error && isset($_POST['register'])): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3"><label class="form-label">Nom complet</label><input type="text" name="reg_name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email <small class="text-muted">(@ece.fr uniquement)</small></label><input type="email" name="reg_email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" name="reg_password" class="form-control" required minlength="6"></div>
            <button type="submit" name="register" class="btn btn-success w-100">S'inscrire</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-5">
    <h5 class="fw-bold text-center mb-3">📍 ECE Paris — 37 Quai de Grenelle, 75015 Paris</h5>
    <div class="rounded overflow-hidden shadow">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.5!2d2.2895!3d48.8504!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6703b6bba5555%3A0x3b08f4e3b7c63e4f!2sECE%20Paris!5e0!3m2!1sfr!2sfr!4v1" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
  </div>
</div>
<footer class="bg-dark text-light text-center py-3"><small>ECE In — Projet APP 2026 | Réseau professionnel ECE Paris</small></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
