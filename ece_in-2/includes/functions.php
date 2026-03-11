<?php
require_once __DIR__ . '/db.php';

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function isLoggedIn(): bool    { return !empty($_SESSION['user']); }

function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: index.php'); exit; }
}
function requireAdmin(): void {
    requireLogin();
    if (currentUser()['role'] !== 'admin') { header('Location: dashboard.php'); exit; }
}
function currentUser(): ?array { return $_SESSION['user'] ?? null; }

// ── AUTH ──────────────────────────────────────────────────────────────────
function registerUser(string $name, string $email, string $password, string $role = 'author'): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (:n,:e,:p,:r)');
    return $stmt->execute([':n'=>$name,':e'=>$email,':p'=>password_hash($password,PASSWORD_DEFAULT),':r'=>$role]);
}
function loginUser(string $email, string $password): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=:e LIMIT 1');
    $stmt->execute([':e'=>$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

// ── NOTIFICATIONS ─────────────────────────────────────────────────────────
function addNotification(int $userId, string $message): void {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:u,:m)');
    $stmt->execute([':u'=>$userId,':m'=>$message]);
}
function getNotifications(int $userId): array {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id=:u ORDER BY created_at DESC');
    $stmt->execute([':u'=>$userId]);
    return $stmt->fetchAll();
}
function countUnreadNotifications(int $userId): int {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) AS t FROM notifications WHERE user_id=:u AND is_read=0');
    $stmt->execute([':u'=>$userId]);
    return (int)$stmt->fetch()['t'];
}
function markNotificationsRead(int $userId): void {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=:u');
    $stmt->execute([':u'=>$userId]);
}

// ── POSTS ─────────────────────────────────────────────────────────────────
function getFeed(): array {
    $pdo    = getPDO();
    $userId = (int)currentUser()['id'];
    $sql = "SELECT p.*, u.name, u.profile_photo,
                   (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id=p.id) AS like_count,
                   op.content AS original_content,
                   ou.name    AS original_author_name
            FROM posts p
            JOIN users u  ON u.id  = p.user_id
            LEFT JOIN posts op ON op.id = p.original_post_id
            LEFT JOIN users ou ON ou.id = op.user_id
            WHERE p.visibility='public'
               OR p.user_id=:own
               OR (p.visibility='friends' AND EXISTS (
                   SELECT 1 FROM connections c
                   WHERE (c.user_id=p.user_id AND c.friend_id=:chk)
                      OR (c.friend_id=p.user_id AND c.user_id=:chk2)))
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':own'=>$userId,':chk'=>$userId,':chk2'=>$userId]);
    return $stmt->fetchAll();
}
function getPostAuthor(int $postId): ?array {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT u.* FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=:id');
    $stmt->execute([':id'=>$postId]);
    return $stmt->fetch() ?: null;
}
function createPost(int $userId, string $content, string $type='status', string $visibility='public', ?string $mediaPath=null, ?int $originalPostId=null): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('INSERT INTO posts (user_id,content,type,visibility,media_path,original_post_id) VALUES (:u,:c,:t,:v,:m,:o)');
    return $stmt->execute([':u'=>$userId,':c'=>$content,':t'=>$type,':v'=>$visibility,':m'=>$mediaPath,':o'=>$originalPostId]);
}
function repostPost(int $userId, int $originalPostId, string $comment=''): bool {
    $pdo  = getPDO();
    $orig = $pdo->prepare('SELECT * FROM posts WHERE id=:id');
    $orig->execute([':id'=>$originalPostId]);
    $original = $orig->fetch();
    if (!$original) return false;
    $content = $comment !== '' ? $comment : '🔁 Repost';
    $stmt = $pdo->prepare('INSERT INTO posts (user_id,content,type,visibility,media_path,original_post_id) VALUES (:u,:c,:t,:v,:m,:o)');
    $result = $stmt->execute([':u'=>$userId,':c'=>$content,':t'=>$original['type'],':v'=>'public',':m'=>$original['media_path'],':o'=>$originalPostId]);
    if ($result && (int)$original['user_id'] !== $userId) {
        $me = currentUser();
        addNotification((int)$original['user_id'], $me['name'].' a reposté votre publication.');
    }
    return $result;
}
function editPost(int $postId, int $userId, string $content): bool {
    $pdo  = getPDO();
    $user = currentUser();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare('UPDATE posts SET content=:c WHERE id=:id');
        return $stmt->execute([':c'=>$content,':id'=>$postId]);
    }
    $stmt = $pdo->prepare('UPDATE posts SET content=:c WHERE id=:id AND user_id=:u');
    return $stmt->execute([':c'=>$content,':id'=>$postId,':u'=>$userId]);
}
function deletePost(int $postId, int $userId): bool {
    $pdo  = getPDO();
    $user = currentUser();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id=:id');
        return $stmt->execute([':id'=>$postId]);
    }
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id=:id AND user_id=:u');
    return $stmt->execute([':id'=>$postId,':u'=>$userId]);
}
function getCommentsByPost(int $postId): array {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT c.*,u.name FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=:pid ORDER BY c.created_at ASC');
    $stmt->execute([':pid'=>$postId]);
    return $stmt->fetchAll();
}
function addComment(int $postId, int $userId, string $content): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('INSERT INTO comments (post_id,user_id,content) VALUES (:p,:u,:c)');
    return $stmt->execute([':p'=>$postId,':u'=>$userId,':c'=>$content]);
}
function deleteComment(int $commentId, int $userId): bool {
    $pdo  = getPDO();
    $user = currentUser();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id=:id');
        return $stmt->execute([':id'=>$commentId]);
    }
    $stmt = $pdo->prepare('DELETE FROM comments WHERE id=:id AND user_id=:u');
    return $stmt->execute([':id'=>$commentId,':u'=>$userId]);
}
function toggleLike(int $postId, int $userId): int {
    $pdo   = getPDO();
    $check = $pdo->prepare('SELECT id FROM post_likes WHERE post_id=:p AND user_id=:u');
    $check->execute([':p'=>$postId,':u'=>$userId]);
    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM post_likes WHERE post_id=:p AND user_id=:u')->execute([':p'=>$postId,':u'=>$userId]);
    } else {
        $pdo->prepare('INSERT INTO post_likes (post_id,user_id) VALUES (:p,:u)')->execute([':p'=>$postId,':u'=>$userId]);
    }
    $count = $pdo->prepare('SELECT COUNT(*) AS t FROM post_likes WHERE post_id=:p');
    $count->execute([':p'=>$postId]);
    return (int)$count->fetch()['t'];
}
function getEvents(): array {
    $pdo = getPDO();
    return $pdo->query("SELECT p.*,u.name FROM posts p JOIN users u ON u.id=p.user_id WHERE p.type='event' AND p.visibility='public' ORDER BY p.created_at DESC")->fetchAll();
}
function handleFileUpload(string $inputName, string $type): ?string {
    if (empty($_FILES[$inputName]['name'])) return null;
    $file      = $_FILES[$inputName];
    $uploadDir = __DIR__.'/../uploads/';
    $allowed   = ['photo'=>['image/jpeg','image/png','image/gif','image/webp'],'video'=>['video/mp4','video/webm','video/ogg']];
    $finfo     = new finfo(FILEINFO_MIME_TYPE);
    $mime      = $finfo->file($file['tmp_name']);
    if (!in_array($mime,$allowed[$type]??[],true)) return null;
    if ($file['size'] > 20*1024*1024) return null;
    $ext      = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    $filename = uniqid('media_',true).'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'],$uploadDir.$filename)) return null;
    return 'uploads/'.$filename;
}

// ── RÉSEAU ────────────────────────────────────────────────────────────────
function getConnections(int $userId): array {
    $pdo  = getPDO();
    $sql  = "SELECT u.id,u.name,u.email,u.profile_photo,u.bio FROM connections c JOIN users u ON u.id=c.friend_id WHERE c.user_id=:u1
             UNION
             SELECT u.id,u.name,u.email,u.profile_photo,u.bio FROM connections c JOIN users u ON u.id=c.user_id WHERE c.friend_id=:u2
             ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':u1'=>$userId,':u2'=>$userId]);
    return $stmt->fetchAll();
}
function getNewMembers(int $userId, array $connectionIds): array {
    $pdo      = getPDO();
    $excluded = array_merge($connectionIds,[$userId]);
    $in       = implode(',',array_fill(0,count($excluded),'?'));
    $stmt     = $pdo->prepare("SELECT id,name,email,profile_photo,bio FROM users WHERE id NOT IN ($in) ORDER BY name");
    $stmt->execute($excluded);
    return $stmt->fetchAll();
}
function addConnection(int $userId, int $friendId): bool {
    $pdo   = getPDO();
    $check = $pdo->prepare('SELECT id FROM connections WHERE (user_id=:u1 AND friend_id=:f1) OR (user_id=:f2 AND friend_id=:u2)');
    $check->execute([':u1'=>$userId,':f1'=>$friendId,':f2'=>$friendId,':u2'=>$userId]);
    if ($check->fetch()) return false;
    $stmt   = $pdo->prepare('INSERT INTO connections (user_id,friend_id) VALUES (:u,:f)');
    $result = $stmt->execute([':u'=>$userId,':f'=>$friendId]);
    if ($result) { $me = currentUser(); addNotification($friendId, $me['name'].' vous a ajouté à son réseau.'); }
    return $result;
}
function searchUsers(string $query): array {
    $pdo  = getPDO();
    $like = '%'.$query.'%';
    $stmt = $pdo->prepare("SELECT id,name,email,bio FROM users WHERE name LIKE :q OR email LIKE :q2 ORDER BY name LIMIT 20");
    $stmt->execute([':q'=>$like,':q2'=>$like]);
    return $stmt->fetchAll();
}

// ── MESSAGERIE ────────────────────────────────────────────────────────────
function getConversation(int $userId, int $contactId): array {
    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT m.*,u.name AS sender_name FROM messages m JOIN users u ON u.id=m.sender_id
                           WHERE (sender_id=:u1 AND receiver_id=:c1) OR (sender_id=:c2 AND receiver_id=:u2)
                           ORDER BY m.created_at ASC");
    $stmt->execute([':u1'=>$userId,':c1'=>$contactId,':c2'=>$contactId,':u2'=>$userId]);
    return $stmt->fetchAll();
}
function sendMessage(int $senderId, int $receiverId, string $content, ?string $mediaPath=null, string $mediaType='text'): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id,receiver_id,content,media_path,media_type) VALUES (:s,:r,:c,:mp,:mt)');
    return $stmt->execute([':s'=>$senderId,':r'=>$receiverId,':c'=>$content,':mp'=>$mediaPath,':mt'=>$mediaType]);
}

// ── EMPLOIS ───────────────────────────────────────────────────────────────
function getJobs(): array {
    $pdo = getPDO();
    return $pdo->query('SELECT j.*,u.name AS publisher_name FROM jobs j JOIN users u ON u.id=j.user_id ORDER BY j.created_at DESC')->fetchAll();
}

// ── ADMIN ─────────────────────────────────────────────────────────────────
function getAllUsers(): array {
    $pdo = getPDO();
    return $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll();
}
function deleteUser(int $userId): bool {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('DELETE FROM users WHERE id=:id');
    return $stmt->execute([':id'=>$userId]);
}
function adminAddUser(string $name, string $email, string $password, string $role): bool {
    return registerUser($name,$email,$password,$role);
}
