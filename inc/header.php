<?php
require_once __DIR__ . '/config.php';
require_login(); check_csrf();
$u = user(); $role = $u['role']; $page = basename($_SERVER['SCRIPT_NAME']);
$ann = ['Annonces', 'M4 10v4h3l7 4V6L7 10zM18 9a4 4 0 010 6'];
$menu = ['dashboard.php' => ['Tableau de bord', 'M3 12l9-9 9 9M5 10v10h14V10']];
if ($role === 'admin') $menu += [
  'etudiants.php' => ['Étudiants', 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0'],
  'professeurs.php' => ['Professeurs', 'M2 8l10-5 10 5-10 5zM6 10v6c3 2 9 2 12 0v-6'],
  'classes.php' => ['Classes & salles', 'M4 4h16v16H4zM4 10h16M10 10v10'],
  'matieres.php' => ['Matières & affectations', 'M5 4h12a2 2 0 012 2v14H7a2 2 0 01-2-2zM9 8h6'],
  'transports.php' => ['Transport', 'M4 16V6a2 2 0 012-2h12a2 2 0 012 2v10M4 16h16M7 20v-4M17 20v-4'],
  'paiements.php' => ['Paiements', 'M3 6h18v12H3zM3 10h18'],
  'annonces.php' => $ann,
];
if ($role === 'professeur') $menu += ['notes.php' => ['Saisie des notes', 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4'], 'annonces.php' => $ann];
if ($role === 'etudiant') $menu += [
  'mes_notes.php' => ['Mes notes', 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4'],
  'mes_paiements.php' => ['Mes paiements', 'M3 6h18v12H3zM3 10h18'],
  'annonces.php' => $ann,
];
?><!DOCTYPE html>
<html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Espace') ?> · <?= ECOLE ?></title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<aside class="side" id="side">
  <div class="brand"><img src="assets/img/logo.png" alt="Logo Elfath"><div><b>ELFATH</b><small><?= ECOLE_AR ?></small></div></div>
  <nav>
  <?php foreach ($menu as $f => [$label, $d]): ?>
    <a href="<?= $f ?>" class="<?= $page === $f ? 'on' : '' ?>"><svg viewBox="0 0 24 24"><path d="<?= $d ?>"/></svg><?= $label ?></a>
  <?php endforeach; ?>
  </nav>
  <a class="out" href="logout.php">Se déconnecter</a>
</aside>
<div class="main">
  <header class="top">
    <button class="burger" onclick="document.getElementById('side').classList.toggle('open')" aria-label="Menu">☰</button>
    <h1><?= e($title ?? '') ?></h1>
    <div class="who"><?= e($u['prenom'] . ' ' . $u['nom']) ?> <span class="tag"><?= e($role) ?></span></div>
  </header>
  <main>
  <?php if (!empty($_SESSION['flash'])): [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert <?= $t ?>"><?= e($m) ?></div>
  <?php endif; ?>
