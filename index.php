<?php
require 'inc/config.php';
$u = user();
try {
    $niveaux = q('SELECT niveau, COUNT(*) nb FROM classes GROUP BY niveau ORDER BY niveau')->fetchAll();
    $anns = q("SELECT titre,contenu,created_at FROM annonces WHERE cible='tous' ORDER BY created_at DESC LIMIT 3")->fetchAll();
} catch (Throwable $x) { $niveaux = $anns = []; }
?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= ECOLE ?> — Enseignement privé</title><link rel="icon" href="assets/img/logo.png"><link rel="stylesheet" href="assets/css/site.css"></head><body>
<header class="nav">
  <a class="lg" href="#top"><img src="assets/img/logo.png" alt="Logo Elfath">ELFATH</a>
  <nav class="links"><a href="#ecole">L’école</a><a href="#niveaux">Niveaux</a><a href="#espace">Espace en ligne</a><a href="#contact">Contact</a>
    <a class="btn" href="<?= $u ? 'dashboard.php' : 'login.php' ?>"><?= $u ? 'Mon espace' : 'Connexion' ?></a></nav>
</header>

<div class="hero" id="top">
  <div>
    <h1>Un suivi clair pour chaque élève, chaque famille.</h1>
    <p class="ar"><?= ECOLE_AR ?></p>
    <p>Notes, paiements, transport et annonces : l’école et les parents suivent tout au même endroit, depuis un téléphone ou un ordinateur.</p>
    <p><a class="btn w" href="<?= $u ? 'dashboard.php' : 'login.php' ?>"><?= $u ? 'Ouvrir mon espace' : 'Se connecter' ?></a></p>
  </div>
  <img class="big" src="assets/img/logo.png" alt="Logo de la Fondation Elfath">
</div>

<section id="ecole"><h2>L’école</h2>
  <p class="lead"><?= ECOLE ?> est un établissement d’enseignement privé. Cette plateforme relie la direction, les professeurs et les familles.</p>
  <div class="cols">
    <div class="box"><h3>Direction</h3><p>Gère les classes, les professeurs, les élèves, le transport et les paiements.</p></div>
    <div class="box"><h3>Professeurs</h3><p>Saisissent les notes de leurs classes et lisent les annonces de l’école.</p></div>
    <div class="box"><h3>Élèves et parents</h3><p>Consultent les notes, la moyenne, les paiements et les annonces.</p></div>
  </div>
</section>

<section id="niveaux" class="alt"><h2>Niveaux et classes</h2>
  <p class="lead">Les classes de l’année en cours.</p>
  <?php if ($niveaux): ?><div class="niv"><?php foreach ($niveaux as $n): ?><span><?= e($n['niveau']) ?> · <?= (int)$n['nb'] ?> classe<?= $n['nb'] > 1 ? 's' : '' ?></span><?php endforeach; ?></div>
  <?php else: ?><p class="lead">Les niveaux apparaîtront ici dès que l’école aura créé ses classes.</p><?php endif; ?>
</section>

<section id="espace"><h2>Espace en ligne</h2>
  <p class="lead">Ce que chacun trouve après connexion.</p>
  <div class="cols">
    <div class="box"><h3>Notes et moyenne</h3><p>Contrôles, devoirs et examens par matière, avec la moyenne pondérée.</p></div>
    <div class="box"><h3>Paiements</h3><p>Scolarité, transport et inscription : l’historique et le mois en cours.</p></div>
    <div class="box"><h3>Transport</h3><p>Bus, trajet et tarif mensuel pour les élèves concernés.</p></div>
    <div class="box"><h3>Annonces</h3><p>Les informations importantes de l’école, publiées par la direction.</p></div>
  </div>
</section>

<?php if ($anns): ?><section class="alt"><h2>Annonces</h2>
  <?php foreach ($anns as $a): ?><div class="ann"><h3><?= e($a['titre']) ?></h3><small><?= e(substr($a['created_at'], 0, 10)) ?></small><p><?= nl2br(e($a['contenu'])) ?></p></div><?php endforeach; ?>
</section><?php endif; ?>

<section class="cta" id="contact"><h2>Contact</h2>
  <p>Site de l’école : <a style="color:#fff" href="https://ecoleelfath.com" rel="noopener">ecoleelfath.com</a><br>Facebook : <?= ECOLE_AR ?></p>
  <p><a class="btn w" href="<?= $u ? 'dashboard.php' : 'login.php' ?>"><?= $u ? 'Mon espace' : 'Connexion' ?></a></p>
</section>
<footer><span>© <?= date('Y') ?> <?= ECOLE ?></span><span><?= ECOLE_AR ?></span></footer>
</body></html>
