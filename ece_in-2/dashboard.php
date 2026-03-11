<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId = (int)currentUser()['id'];
$error  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish'])) {
    $content    = trim($_POST['content']??'');
    $type       = $_POST['type']??'status';
    $visibility = $_POST['visibility']??'public';
    $mediaPath  = null;
    if (in_array($type,['photo','video'])) $mediaPath = handleFileUpload('media_file',$type);
    if ($content !== '') { createPost($userId,$content,$type,$visibility,$mediaPath); header('Location: dashboard.php'); exit; }
    else $error = 'Le contenu est requis.';
}
$posts = getFeed();
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
  <title>Accueil — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <div class="row g-4">

    <!-- Profil résumé -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="card shadow-sm text-center p-3">
        <div class="rounded-circle bg-primary text-white mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:1.6rem;">
          <?= strtoupper(substr(currentUser()['name'],0,1)) ?>
        </div>
        <h6 class="mb-0 fw-bold"><?= e(currentUser()['name']) ?></h6>
        <small class="text-muted"><?= e(currentUser()['email']) ?></small>
        <hr>
        <a href="profile.php" class="btn btn-outline-primary btn-sm mb-1">Mon profil</a>
        <a href="cv.php" class="btn btn-outline-secondary btn-sm">Mon CV</a>
      </div>
    </div>

    <!-- Feed -->
    <div class="col-lg-6">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <form method="POST" enctype="multipart/form-data">
            <textarea name="content" class="form-control mb-2" rows="3" placeholder="Quoi de neuf ?" required></textarea>
            <div class="row g-2 align-items-center">
              <div class="col-auto">
                <select name="type" class="form-select form-select-sm" id="postType">
                  <option value="status">💬 Statut</option>
                  <option value="event">📅 Évènement</option>
                  <option value="photo">📷 Photo</option>
                  <option value="video">🎥 Vidéo</option>
                </select>
              </div>
              <div class="col-auto">
                <select name="visibility" class="form-select form-select-sm">
                  <option value="public">🌍 Public</option>
                  <option value="friends">👥 Amis</option>
                </select>
              </div>
              <div class="col-auto" id="mediaUploadField" style="display:none;">
                <input type="file" name="media_file" class="form-control form-control-sm" accept="image/*,video/*">
              </div>
              <div class="col-auto ms-auto">
                <button type="submit" name="publish" class="btn btn-primary btn-sm">Publier</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <?php if (empty($posts)): ?>
        <div class="alert alert-info">Aucune publication pour le moment.</div>
      <?php endif; ?>

      <?php foreach ($posts as $post): ?>
        <div class="card shadow-sm mb-3" id="post-<?= $post['id'] ?>">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                <?= strtoupper(substr($post['name'],0,1)) ?>
              </div>
              <div class="flex-grow-1">
                <strong><?= e($post['name']) ?></strong>
                <?php if ($post['original_post_id']): ?><span class="badge bg-info text-dark ms-1">🔁 Repost</span><?php endif; ?>
                <br><small class="text-muted"><?= date('d/m/Y H:i',strtotime($post['created_at'])) ?></small>
              </div>
              <?php if ((int)$post['user_id'] === $userId || currentUser()['role'] === 'admin'): ?>
                <button class="btn btn-sm btn-outline-secondary btn-edit-post" data-id="<?= $post['id'] ?>" title="Modifier">✏️</button>
              <?php endif; ?>
            </div>

            <?php if ($post['original_post_id'] && $post['original_author_name']): ?>
              <small class="text-muted fst-italic d-block mb-1">Reposté depuis <strong><?= e($post['original_author_name']) ?></strong></small>
              <?php if (!empty($post['original_content'])): ?>
                <div class="border-start border-3 border-info ps-3 mb-2 text-muted small"><?= nl2br(e($post['original_content'])) ?></div>
              <?php endif; ?>
              <?php if (trim($post['content']) !== '🔁 Repost'): ?>
                <p class="mb-0"><?= nl2br(e($post['content'])) ?></p>
              <?php endif; ?>
            <?php else: ?>
              <div class="post-content-text" id="content-text-<?= $post['id'] ?>"><?= nl2br(e($post['content'])) ?></div>
            <?php endif; ?>

            <div class="post-edit-zone d-none mt-2" id="edit-zone-<?= $post['id'] ?>">
              <textarea class="form-control mb-2 edit-textarea" rows="3"><?= e($post['content']) ?></textarea>
              <button class="btn btn-sm btn-success btn-save-edit" data-id="<?= $post['id'] ?>">Enregistrer</button>
              <button class="btn btn-sm btn-secondary btn-cancel-edit" data-id="<?= $post['id'] ?>">Annuler</button>
            </div>

            <?php if ($post['media_path']): ?>
              <?php $ext = strtolower(pathinfo($post['media_path'],PATHINFO_EXTENSION)); ?>
              <?php if (in_array($ext,['jpg','jpeg','png','gif','webp'])): ?>
                <img src="<?= e($post['media_path']) ?>" class="img-fluid rounded mt-2" alt="media">
              <?php else: ?>
                <video controls class="w-100 rounded mt-2"><source src="<?= e($post['media_path']) ?>"></video>
              <?php endif; ?>
            <?php endif; ?>

            <div class="d-flex gap-3 mt-3 align-items-center flex-wrap">
              <button class="btn btn-sm btn-outline-danger btn-like" data-id="<?= $post['id'] ?>">
                ❤️ <span class="like-count"><?= (int)$post['like_count'] ?></span>
              </button>
              <button class="btn btn-sm btn-outline-secondary btn-toggle-comments" data-id="<?= $post['id'] ?>">💬 Commentaires</button>
              <?php if ((int)$post['user_id'] !== $userId): ?>
                <button class="btn btn-sm btn-outline-info btn-repost" data-id="<?= $post['id'] ?>">🔁 Reposter</button>
              <?php endif; ?>
            </div>

            <div class="repost-zone d-none mt-2 p-2 bg-light rounded" id="repost-zone-<?= $post['id'] ?>">
              <textarea class="form-control mb-2 repost-comment" rows="2" placeholder="Ajouter un commentaire (optionnel)…"></textarea>
              <button class="btn btn-sm btn-info btn-confirm-repost text-white" data-id="<?= $post['id'] ?>">✅ Confirmer</button>
              <button class="btn btn-sm btn-secondary btn-cancel-repost" data-id="<?= $post['id'] ?>">Annuler</button>
            </div>

            <div class="comments-section mt-3 d-none" id="comments-<?= $post['id'] ?>">
              <div class="comments-list mb-2">
                <?php foreach (getCommentsByPost($post['id']) as $c): ?>
                  <div class="d-flex gap-2 mb-1 align-items-start" id="comment-<?= $c['id'] ?>">
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:.8rem;flex-shrink:0;"><?= strtoupper(substr($c['name'],0,1)) ?></div>
                    <div class="bg-light rounded p-2 flex-grow-1 small"><strong><?= e($c['name']) ?></strong> <?= e($c['content']) ?></div>
                    <?php if ((int)$c['user_id'] === $userId || currentUser()['role'] === 'admin'): ?>
                      <button class="btn btn-sm text-danger p-0 btn-delete-comment" data-id="<?= $c['id'] ?>">✕</button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control comment-input" placeholder="Votre commentaire…" data-id="<?= $post['id'] ?>">
                <button class="btn btn-primary btn-send-comment" data-id="<?= $post['id'] ?>">Envoyer</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Partenaires -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">🤝 Partenaires ECE</div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <?php foreach ($partners as $p): ?>
              <li class="list-group-item py-2 px-3">
                <a href="<?= $p['url'] ?>" target="_blank" class="text-decoration-none text-dark d-flex justify-content-between">
                  <span><?= $p['icon'] ?> <?= $p['name'] ?></span><span class="text-primary">→</span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="p-2"><a href="events.php" class="btn btn-sm btn-outline-primary w-100">Voir les évènements</a></div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
