<?php if (isLoggedIn()):
    $unread = countUnreadNotifications((int)currentUser()['id']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php"><span class="text-primary">ECE</span> In</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="network.php">Mon Réseau</a></li>
        <li class="nav-item"><a class="nav-link" href="events.php">Évènements</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Vous</a></li>
        <li class="nav-item">
          <a class="nav-link position-relative" href="notifications.php">
            Notifications
            <?php if ($unread > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread > 99 ? '99+' : $unread ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="messages.php">Messagerie</a></li>
        <li class="nav-item"><a class="nav-link" href="jobs.php">Emplois</a></li>
        <?php if (currentUser()['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link text-warning" href="admin.php">⚙ Admin</a></li>
        <?php endif; ?>
      </ul>
      <form class="d-flex me-3" action="search.php" method="GET">
        <div class="input-group input-group-sm">
          <input type="text" name="q" class="form-control" placeholder="Rechercher..." required minlength="2" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>">
          <button class="btn btn-primary" type="submit">🔍</button>
        </div>
      </form>
      <span class="navbar-text me-3 text-light">Bonjour, <strong><?= e(currentUser()['name']) ?></strong></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
  </div>
</nav>
<?php endif; ?>
