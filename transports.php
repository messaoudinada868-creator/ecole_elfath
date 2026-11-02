<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            q('INSERT INTO transports(nom_bus,matricule,chauffeur,tel_chauffeur,trajet,tarif_mensuel) VALUES(?,?,?,?,?,?)',
              [post('nom'), post('mat') ?: null, post('chauffeur') ?: null, post('tel') ?: null, post('trajet') ?: null, (float)str_replace(',', '.', post('tarif'))]);
            flash('Bus ajouté.');
        } elseif (isset($_POST['del'])) { q('DELETE FROM transports WHERE id=?', [(int)$_POST['id']]); flash('Bus supprimé.'); }
    } catch (Throwable $x) { flash(err_msg($x), 'err'); }
    redirect('transports.php');
}
$title = 'Transport'; require 'inc/header.php';
$list = q('SELECT t.*,(SELECT COUNT(*) FROM etudiants e WHERE e.transport_id=t.id) nb FROM transports t ORDER BY nom_bus')->fetchAll();
?>
<div class="card"><h2>Nouveau bus</h2>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Nom du bus</label><input name="nom" required></div><div><label>Matricule</label><input name="mat"></div>
  <div><label>Chauffeur</label><input name="chauffeur"></div><div><label>Tél. chauffeur</label><input name="tel"></div>
  <div><label>Trajet</label><input name="trajet"></div><div><label>Tarif mensuel (DH)</label><input name="tarif" required></div>
  <div><button class="btn" name="add" value="1">Ajouter</button></div></form></div>
<div class="card"><h2>Bus (<?= count($list) ?>)</h2><div class="tw"><table><thead><tr><th>Bus</th><th>Matricule</th><th>Chauffeur</th><th>Trajet</th><th>Tarif</th><th>Élèves</th><th></th></tr></thead><tbody>
<?php foreach ($list as $t): ?><tr><td><?= e($t['nom_bus']) ?></td><td><?= e($t['matricule']) ?></td><td><?= e($t['chauffeur'] . ' ' . $t['tel_chauffeur']) ?></td><td><?= e($t['trajet']) ?></td><td><?= money($t['tarif_mensuel']) ?></td><td><?= (int)$t['nb'] ?></td>
<td><form method="post" data-confirm="Supprimer ce bus ?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="btn sm red" name="del" value="1">Supprimer</button></form></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<?php require 'inc/footer.php';
