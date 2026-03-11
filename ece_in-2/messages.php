<?php
session_start();
require_once 'includes/functions.php';
requireLogin();
$userId    = (int)currentUser()['id'];
$contacts  = getConnections($userId);
$contactId = isset($_GET['contact']) ? (int)$_GET['contact'] : ($contacts[0]['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $contactId > 0) {
    $content   = trim($_POST['content']??'');
    $mediaPath = null;
    $mediaType = 'text';

    // Upload image/gif
    if (!empty($_FILES['media_file']['name'])) {
        $file      = $_FILES['media_file'];
        $uploadDir = __DIR__.'/uploads/messages/';
        $finfo     = new finfo(FILEINFO_MIME_TYPE);
        $mime      = $finfo->file($file['tmp_name']);
        $allowed   = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
        if (isset($allowed[$mime]) && $file['size'] <= 10*1024*1024) {
            $filename = uniqid('msg_',true).'.'.$allowed[$mime];
            if (move_uploaded_file($file['tmp_name'],$uploadDir.$filename)) {
                $mediaPath = 'uploads/messages/'.$filename;
                $mediaType = ($mime === 'image/gif') ? 'gif' : 'image';
            }
        }
    }

    // GIF via URL Tenor
    if (!empty($_POST['gif_url']) && $mediaPath === null) {
        $mediaPath = $_POST['gif_url'];
        $mediaType = 'gif';
        if ($content === '') $content = ' ';
    }

    // Message vocal (base64)
    if (!empty($_POST['audio_data']) && $mediaPath === null) {
        $audioData = $_POST['audio_data'];
        if (preg_match('/^data:audio\/(\w+);base64,(.+)$/', $audioData, $m)) {
            $ext      = $m[1] === 'mpeg' ? 'mp3' : 'webm';
            $filename = uniqid('voice_',true).'.'.$ext;
            $uploadDir = __DIR__.'/uploads/messages/';
            file_put_contents($uploadDir.$filename, base64_decode($m[2]));
            $mediaPath = 'uploads/messages/'.$filename;
            $mediaType = 'audio';
            $content   = '🎤 Message vocal';
        }
    }

    if ($content !== '' || $mediaPath !== null) {
        sendMessage($userId, $contactId, $content !== '' ? $content : ' ', $mediaPath, $mediaType);
        header('Location: messages.php?contact='.$contactId);
        exit;
    }
}

$conversation = $contactId > 0 ? getConversation($userId,$contactId) : [];
$contactName  = '';
foreach ($contacts as $c) { if ((int)$c['id']===$contactId) { $contactName=$c['name']; break; } }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Messagerie — ECE In</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
<div class="container py-4">
  <div class="row g-3">

    <!-- Contacts -->
    <div class="col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">💬 Conversations</div>
        <div class="card-body p-2">
          <?php if (empty($contacts)): ?>
            <p class="text-muted small p-2">Ajoutez des connexions dans <a href="network.php">Mon Réseau</a>.</p>
          <?php endif; ?>
          <?php foreach ($contacts as $contact): ?>
            <a href="messages.php?contact=<?= (int)$contact['id'] ?>"
               class="contact-item d-flex align-items-center gap-2 text-decoration-none text-dark border rounded p-2 mb-1 <?= $contactId===(int)$contact['id']?'active':'' ?>">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                <?= strtoupper(substr($contact['name'],0,1)) ?>
              </div>
              <div><div class="fw-semibold small"><?= e($contact['name']) ?></div>
              <small class="text-muted"><?= e(substr($contact['email'],0,20)) ?>…</small></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Conversation -->
    <div class="col-lg-9">
      <div class="card shadow-sm d-flex flex-column" style="height:600px;">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
          <?php if ($contactName): ?>
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><?= strtoupper(substr($contactName,0,1)) ?></div>
            <strong><?= e($contactName) ?></strong>
          <?php else: ?>
            <span class="text-muted">Sélectionnez une conversation</span>
          <?php endif; ?>
        </div>

        <div class="chat-box flex-grow-1" id="chatBox" style="height:460px;">
          <?php if (empty($conversation) && $contactId > 0): ?>
            <div class="text-center text-muted mt-5">Démarrez la conversation !</div>
          <?php endif; ?>
          <?php foreach ($conversation as $msg): ?>
            <?php $isMe = (int)$msg['sender_id'] === $userId; ?>
            <div class="d-flex mb-3 <?= $isMe?'justify-content-end':'justify-content-start' ?>">
              <?php if (!$isMe): ?>
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:30px;height:30px;font-size:.75rem;">
                  <?= strtoupper(substr($msg['sender_name'],0,1)) ?>
                </div>
              <?php endif; ?>
              <div class="bubble <?= $isMe?'bubble-me':'bubble-other' ?>">
                <?php if ($msg['media_type']==='image' || $msg['media_type']==='gif'): ?>
                  <img src="<?= e($msg['media_path']) ?>" alt="media">
                  <?php if (trim($msg['content'])): ?><div class="mt-1 small"><?= e($msg['content']) ?></div><?php endif; ?>
                <?php elseif ($msg['media_type']==='audio'): ?>
                  <audio controls src="<?= e($msg['media_path']) ?>" style="max-width:200px;"></audio>
                <?php else: ?>
                  <?= nl2br(e($msg['content'])) ?>
                <?php endif; ?>
                <div class="<?= $isMe?'text-white-50':'text-muted' ?>" style="font-size:.7rem;margin-top:4px;"><?= date('H:i',strtotime($msg['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($contactId > 0): ?>
        <div class="card-footer bg-white p-2 border-top">
          <!-- Barre prévisualisation -->
          <div id="preview-bar" class="d-none d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded">
            <img id="preview-img" src="" alt="" style="max-height:50px;border-radius:6px;display:none;">
            <span id="preview-name" class="small text-muted flex-grow-1"></span>
            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-preview">✕</button>
          </div>
          <form method="POST" enctype="multipart/form-data" id="msgForm">
            <input type="hidden" name="audio_data" id="audioData">
            <input type="hidden" name="gif_url" id="gifUrlInput">
            <div class="d-flex gap-2 align-items-end">

              <!-- Bouton image/gif -->
              <div class="dropdown dropup">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="mediaDropBtn" title="Image / GIF">🖼️</button>
                <div class="dropdown-menu p-2" id="mediaDropdown" style="width:300px;display:none;position:absolute;bottom:40px;left:0;z-index:9999;">
                  <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-sm btn-primary" id="tabUploadBtn">📁 Fichier</button>
                    <button class="btn btn-sm btn-outline-secondary" id="tabGifBtn">🎞️ GIF</button>
                  </div>
                  <div id="tab-upload">
                    <input type="file" name="media_file" id="mediaFileInput" accept="image/*,.gif" class="form-control form-control-sm">
                  </div>
                  <div id="tab-gif" style="display:none;">
                    <input type="text" id="gifSearch" class="form-control form-control-sm mb-2" placeholder="Chercher un GIF…">
                    <div class="gif-grid" id="gifGrid"><div class="text-muted small py-2 text-center" style="grid-column:1/-1;">Tapez pour chercher</div></div>
                  </div>
                </div>
              </div>

              <!-- Message vocal -->
              <button type="button" class="btn btn-outline-secondary btn-sm" id="voiceBtn" title="Message vocal">🎤</button>
              <span id="recTimer" class="small text-danger d-none">⏺ <span id="recSeconds">0</span>s</span>

              <!-- Texte -->
              <textarea name="content" id="msgContent" class="form-control form-control-sm" rows="1" placeholder="Écrire un message… (Entrée pour envoyer)" style="resize:none;"></textarea>

              <button type="submit" class="btn btn-primary btn-sm px-3">Envoyer</button>
            </div>
          </form>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Scroll bas
const chatBox = document.getElementById('chatBox');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

// Toggle dropdown image/gif
const mediaDropBtn = document.getElementById('mediaDropBtn');
const mediaDropdown = document.getElementById('mediaDropdown');
if (mediaDropBtn) {
  mediaDropBtn.addEventListener('click', e => {
    e.stopPropagation();
    mediaDropdown.style.display = mediaDropdown.style.display === 'none' ? 'block' : 'none';
  });
  document.addEventListener('click', () => { if (mediaDropdown) mediaDropdown.style.display = 'none'; });
  mediaDropdown.addEventListener('click', e => e.stopPropagation());
}

// Tabs
document.getElementById('tabUploadBtn')?.addEventListener('click', () => {
  document.getElementById('tab-upload').style.display = '';
  document.getElementById('tab-gif').style.display = 'none';
  document.getElementById('tabUploadBtn').className = 'btn btn-sm btn-primary';
  document.getElementById('tabGifBtn').className = 'btn btn-sm btn-outline-secondary';
});
document.getElementById('tabGifBtn')?.addEventListener('click', () => {
  document.getElementById('tab-upload').style.display = 'none';
  document.getElementById('tab-gif').style.display = '';
  document.getElementById('tabGifBtn').className = 'btn btn-sm btn-primary';
  document.getElementById('tabUploadBtn').className = 'btn btn-sm btn-outline-secondary';
});

// Prévisualisation fichier
document.getElementById('mediaFileInput')?.addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const bar = document.getElementById('preview-bar');
  const img = document.getElementById('preview-img');
  bar.classList.remove('d-none');
  document.getElementById('preview-name').textContent = file.name;
  if (file.type.startsWith('image/')) {
    const r = new FileReader();
    r.onload = e => { img.src = e.target.result; img.style.display = ''; };
    r.readAsDataURL(file);
  }
  if (mediaDropdown) mediaDropdown.style.display = 'none';
});
document.getElementById('clear-preview')?.addEventListener('click', () => {
  document.getElementById('mediaFileInput').value = '';
  document.getElementById('preview-bar').classList.add('d-none');
  document.getElementById('preview-img').style.display = 'none';
  document.getElementById('preview-img').src = '';
  document.getElementById('preview-name').textContent = '';
  document.getElementById('gifUrlInput').value = '';
});

// Recherche GIF
let gifTimeout;
document.getElementById('gifSearch')?.addEventListener('input', function () {
  clearTimeout(gifTimeout);
  const q = this.value.trim();
  const grid = document.getElementById('gifGrid');
  if (!q) { grid.innerHTML = '<div class="text-muted small py-2 text-center" style="grid-column:1/-1;">Tapez pour chercher</div>'; return; }
  gifTimeout = setTimeout(() => {
    fetch(`https://tenor.googleapis.com/v2/search?q=${encodeURIComponent(q)}&key=AIzaSyAyimkuYQYF_FXVALexPzHeC0kjRcGSXwM&limit=9&media_filter=gif`)
      .then(r => r.json())
      .then(data => {
        grid.innerHTML = '';
        if (!data.results?.length) { grid.innerHTML = '<div class="text-muted small" style="grid-column:1/-1;">Aucun résultat</div>'; return; }
        data.results.forEach(gif => {
          const url  = gif.media_formats?.gif?.url;
          const tiny = gif.media_formats?.tinygif?.url || url;
          if (!url) return;
          const img = document.createElement('img');
          img.src = tiny;
          img.addEventListener('click', () => {
            document.getElementById('gifUrlInput').value = url;
            const bar = document.getElementById('preview-bar');
            const pi  = document.getElementById('preview-img');
            bar.classList.remove('d-none');
            pi.src = tiny; pi.style.display = '';
            document.getElementById('preview-name').textContent = 'GIF sélectionné';
            mediaDropdown.style.display = 'none';
          });
          grid.appendChild(img);
        });
      }).catch(() => { grid.innerHTML = '<div class="text-muted small">Erreur de chargement</div>'; });
  }, 400);
});

// Message vocal
let mediaRecorder, audioChunks = [], recInterval;
const voiceBtn   = document.getElementById('voiceBtn');
const recTimer   = document.getElementById('recTimer');
const recSeconds = document.getElementById('recSeconds');
if (voiceBtn) {
  voiceBtn.addEventListener('click', async () => {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
      clearInterval(recInterval);
      voiceBtn.classList.remove('recording');
      recTimer.classList.add('d-none');
      return;
    }
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      audioChunks  = [];
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
      mediaRecorder.onstop = () => {
        const blob = new Blob(audioChunks, { type: 'audio/webm' });
        const r    = new FileReader();
        r.onloadend = () => {
          document.getElementById('audioData').value = r.result;
          const bar = document.getElementById('preview-bar');
          bar.classList.remove('d-none');
          document.getElementById('preview-name').textContent = '🎤 Message vocal prêt — cliquez Envoyer';
          document.getElementById('preview-img').style.display = 'none';
        };
        r.readAsDataURL(blob);
        stream.getTracks().forEach(t => t.stop());
      };
      mediaRecorder.start();
      voiceBtn.classList.add('recording');
      recTimer.classList.remove('d-none');
      let s = 0; recSeconds.textContent = s;
      recInterval = setInterval(() => { recSeconds.textContent = ++s; }, 1000);
    } catch (err) { alert('Micro non disponible : ' + err.message); }
  });
}

// Auto-resize + Entrée pour envoyer
const msgContent = document.getElementById('msgContent');
if (msgContent) {
  msgContent.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
  });
  msgContent.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); document.getElementById('msgForm').submit(); }
  });
}
</script>
</body>
</html>
