<?php
declare(strict_types=1);

require __DIR__ . '/../config/db.php';

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'httponly' => true,
  'secure' => $secure,
  'samesite' => 'Lax',
]);
session_start();

if (!empty($_SESSION['cms_user'])) {
  header('Location: dashboard.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['user'] ?? '');
  $pass = (string)($_POST['pass'] ?? '');

  if ($user === '' || $pass === '') {
    $error = 'fill in username and password.';
  } else {
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM cms_users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['cms_user'] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
      ];
      header('Location: dashboard.php');
      exit;
    } else {
      $error = 'wrong username or password';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CMS Login</title>

  <link rel="icon" type="image/png" href="../assets/images/RetroByteLogo.png">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="css/cms.css">
  <link rel="stylesheet" href="css/product-s.css">

  <!-- icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

  <!-- fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">

  <script src="../assets/js/app.js" defer></script>

  <style>
    body  {
      font: 14px/1.2 "Silkscreen", system-ui, sans-serif;
    }
    #final-cta .cms-login-form{
      max-width: 360px;
      margin: 26px auto 0;
      display: grid;
      gap: 10px;
      text-align: left;
      
    }
    #final-cta .cms-login-label{
      font-size: 12px;
      letter-spacing: .06em;
      color: var(--text-muted);
    }
    #final-cta .cms-login-input{
      padding: 10px 12px;
      font: 14px/1.2 "Silkscreen", system-ui, sans-serif;
      color: var(--text-primary);
      background: rgba(0,0,0,.12);
      border: 2px solid var(--stroke);
      outline: none;
    }
    #final-cta .cms-login-input:focus{
      border-color: var(--color-primary);
      box-shadow: 0 0 12px rgba(165, 140, 255, 0.28);
    }
    #final-cta .cms-login-actions{
      margin-top: 14px;
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    #final-cta .pixel-button{ margin: 0; }
  </style>
</head>
<body class="cms-auth">
  <canvas id="grid"></canvas>

  <section id="final-cta" style="margin:0;">
  <div class="container" style="max-width: 560px; margin: 200px auto;">

    <h2 class="title">CMS LOGIN</h2>

    <?php if ($error): ?>
      <p class="reviews-intro" style="border-color: var(--error); margin-top: 22px;">
        <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>

    <form method="post" autocomplete="off" class="cms-login-form">
      <label class="cms-login-label">Username</label>
      <input class="cms-login-input" type="text" name="user"
             value="<?= htmlspecialchars($_POST['user'] ?? '') ?>"/>

      <label class="cms-login-label">Password</label>
      <input class="cms-login-input" type="password" name="pass" />

      <div class="cms-login-actions">
        <button class="pixel-button btn-primary" type="submit">LOGIN</button>
        <a class="pixel-button btn-ghost" href="../index.php">BACK TO SITE</a>
      </div>
    </form>

  </div>
</section>

<script>
  (function () {
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.dataset.theme = 'dark';
    }
  })();
</script>
</body>
</html>
