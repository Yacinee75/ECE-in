<?php
session_start();
require_once 'includes/functions.php';
requireAdmin();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $uid = (int)$_POST['user_id'];
        if ($uid !== (int)currentUser()['id']) { deleteUser($uid); $success = 'Utilisateur supprimé.'; }
        else $error = 'Vous ne pouvez pas vous supprimer vous-même.';
    }
    if (isset($_POST['add_user'])) {
        $name  = trim($_POST['name']??'');
        $email = trim($_POST['email']??'');
        $pass  = $_POST['password']??'';
        $role  = $_POST['role']??'author';
        if (!str_ends_with(strtolower($email),'@ece.fr')) $error = 'Email @ece.fr uniquement.';
        elseif (adminAddUser($name,$email,$pass,$role)) $success = 'Utilisateur créé.';
        else $error = 'Erreur : email déjà utilisé ?';
    }
}
$users = getAllUsers();
$pdo   = getPDO();
$stats = [
    'users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'posts' => $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'msg'   => $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
    'jobs'  => $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <h4 class="fw-bold mb-4">⚙ Panel Administrateur</h4>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <?php foreach (['👥 Utilisateurs'=>$stats['users'],'📝 Publications'=>$stats['posts'],'✉ Messages'=>$stats['msg'],'💼 Offres'=>$stats['jobs']] as $label => $val): ?>
      <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center p-3">
          <h3 class="fw-bold text-primary"><?= $val ?></h3>
          <small class="text-muted"><?= $label ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">Liste des utilisateurs</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscrit le</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= e($u['name']) ?></td>
                  <td><?= e($u['email']) ?></td>
                  <td><span class="badge <?= $u['role']==='admin'?'bg-warning text-dark':'bg-secondary' ?>"><?= $u['role'] ?></span></td>
                  <td><small><?= date('d/m/Y',strtotime($u['created_at'])) ?></small></td>
                  <td>
                    <?php if ((int)$u['id'] !== (int)currentUser()['id']): ?>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger">🗑</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">Ajouter un utilisateur</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nom complet" required></div>
            <div class="mb-2"><input type="email" name="email" class="form-control form-control-sm" placeholder="email@ece.fr" required></div>
            <div class="mb-2"><input type="password" name="password" class="form-control form-control-sm" placeholder="Mot de passe" required minlength="6"></div>
            <div class="mb-3">
              <select name="role" class="form-select form-select-sm">
                <option value="author">Auteur</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary w-100 btn-sm">Créer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
