<?php
require 'inc/config.php';
if (user()) redirect('dashboard.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        $u = q('SELECT * FROM users WHERE email=? AND actif=1', [post('email')])->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['mot_de_passe'])) {
            session_regenerate_id(true);
            unset($u['mot_de_passe']);
            $_SESSION['user'] = $u;
            redirect('dashboard.php');
        }
        usleep(400000); $err = 'Email ou mot de passe incorrect.';
    } catch (Throwable $x) { $err = 'Base de données inaccessible. Lancez setup.php après avoir importé database.sql.'; }
}
?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion · <?= ECOLE ?></title><link rel="icon" href="assets/img/logo.png"><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login"><div class="lbox">
  <img src="assets/img/logo.png" alt="Logo Elfath"><h1>ELFATH</h1><p><?= ECOLE_AR ?></p>
  <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <div><label for="email">Email</label><input id="email" type="email" name="email" required autofocus></div>
    <div><label for="pw">Mot de passe</label><input id="pw" type="password" name="password" required></div>
    <button class="btn">Se connecter</button>
  </form>
</div></body></html>
