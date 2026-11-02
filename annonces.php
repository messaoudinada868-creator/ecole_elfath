<?php
require 'inc/config.php'; require_login(); check_csrf();
$u = user();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $u['role'] === 'admin') {
    try {
        if (isset($_POST['add'])) {
            $c = in_array(post('cible'), ['tous', 'etudiants', 'professeurs'], true) ? post('cible') : 'tous';
            if (post('titre') === '' || post('contenu') === '') throw new Exception('Titre et contenu obligatoires.');
            q('INSERT INTO annonces(titre,contenu,cible,created_by) VALUES(?,?,?,?)', [post('titre'), post('contenu'), $c, $u['id']]); flash('Annonce publiée.');
        } elseif (isset($_POST['del'])) { q('DELETE FROM annonces WHERE id=?', [(int)$_POST['id']]); flash('Annonce supprimée.'); }
    } catch (Throwable $x) { flash(err_msg($x), 'err'); }
    redirect('annonces.php');
}
$title = 'Annonces'; require 'inc/header.php';
$cibles = ['admin' => ['tous','etudiants','professeurs'], 'professeur' => ['tous','professeurs'], 'etudiant' => ['tous','etudiants']][$role];
$in = implode(',', array_fill(0, count($cibles), '?'));
$list = q("SELECT * FROM annonces WHERE cible IN ($in) ORDER BY created_at DESC", $cibles)->fetchAll();
if ($role === 'admin'): ?>
<div class="card"><h2>Nouvelle annonce</h2>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Titre</label><input name="titre" maxlength="150" required></div>
  <div><label>Destinataires</label><select name="cible"><option value="tous">Tous</option><option value="etudiants">Étudiants</option><option value="professeurs">Professeurs</option></select></div>
  <div style="grid-column:1/-1"><label>Contenu</label><textarea name="contenu" rows="4" required></textarea></div>
  <div><button class="btn" name="add" value="1">Publier</button></div></form></div>
<?php endif; ?>
<div class="card"><h2>Toutes les annonces</h2>
<?php if (!$list) echo '<p class="muted">Aucune annonce.</p>'; foreach ($list as $a): ?>
<div class="ann"><h3><?= e($a['titre']) ?></h3><small><?= e($a['created_at']) ?> · pour <?= e($a['cible']) ?></small><p><?= nl2br(e($a['contenu'])) ?></p>
<?php if ($role === 'admin'): ?><form method="post" data-confirm="Supprimer cette annonce ?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn sm red" name="del" value="1">Supprimer</button></form><?php endif; ?></div>
<?php endforeach; ?></div>
<?php require 'inc/footer.php';
