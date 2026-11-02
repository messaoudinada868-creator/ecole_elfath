<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_m'])) { q('INSERT INTO matieres(nom,coefficient) VALUES(?,?)', [post('nom'), (float)str_replace(',', '.', post('coef')) ?: 1]); flash('Matière ajoutée.'); }
        elseif (isset($_POST['del_m'])) { q('DELETE FROM matieres WHERE id=?', [(int)$_POST['id']]); flash('Matière supprimée.'); }
        elseif (isset($_POST['add_a'])) { q('INSERT INTO affectations_prof(professeur_id,matiere_id,classe_id) VALUES(?,?,?)', [(int)post('prof'), (int)post('matiere'), (int)post('classe')]); flash('Affectation ajoutée.'); }
        elseif (isset($_POST['del_a'])) { q('DELETE FROM affectations_prof WHERE id=?', [(int)$_POST['id']]); flash('Affectation supprimée.'); }
    } catch (Throwable $x) { flash(err_msg($x), 'err'); }
    redirect('matieres.php');
}
$title = 'Matières & affectations'; require 'inc/header.php';
$mat = q('SELECT * FROM matieres ORDER BY nom')->fetchAll();
$profs = q('SELECT p.id,u.nom,u.prenom FROM professeurs p JOIN users u ON u.id=p.user_id ORDER BY u.nom')->fetchAll();
$cls = q('SELECT c.id,c.nom,a.libelle FROM classes c JOIN annees_scolaires a ON a.id=c.annee_id ORDER BY a.libelle DESC,c.nom')->fetchAll();
$aff = q('SELECT a.id,u.nom,u.prenom,m.nom matiere,c.nom classe FROM affectations_prof a JOIN professeurs p ON p.id=a.professeur_id JOIN users u ON u.id=p.user_id JOIN matieres m ON m.id=a.matiere_id JOIN classes c ON c.id=a.classe_id ORDER BY c.nom,m.nom')->fetchAll();
$del = fn($n, $id) => '<form method="post" data-confirm="Supprimer ?">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><button class="btn sm red" name="' . $n . '" value="1">Supprimer</button></form>';
?>
<div class="card"><h2>Matières</h2>
<form method="post" class="f"><?= csrf_field() ?><div><label>Nom</label><input name="nom" required></div><div><label>Coefficient</label><input name="coef" value="1" required></div><div><button class="btn" name="add_m" value="1">Ajouter</button></div></form>
<div class="tw" style="margin-top:14px"><table><thead><tr><th>Matière</th><th>Coef.</th><th></th></tr></thead><tbody>
<?php foreach ($mat as $m): ?><tr><td><?= e($m['nom']) ?></td><td><?= e($m['coefficient']) ?></td><td><?= $del('del_m', $m['id']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>

<div class="card"><h2>Affecter un professeur (matière + classe)</h2>
<?php if (!$profs || !$mat || !$cls): ?><p class="muted">Il faut au moins un professeur, une matière et une classe.</p><?php else: ?>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Professeur</label><select name="prof"><?php foreach ($profs as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['nom'] . ' ' . $p['prenom']) ?></option><?php endforeach; ?></select></div>
  <div><label>Matière</label><select name="matiere"><?php foreach ($mat as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['nom']) ?></option><?php endforeach; ?></select></div>
  <div><label>Classe</label><select name="classe"><?php foreach ($cls as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['nom'] . ' (' . $c['libelle'] . ')') ?></option><?php endforeach; ?></select></div>
  <div><button class="btn" name="add_a" value="1">Affecter</button></div></form><?php endif; ?>
<div class="tw" style="margin-top:14px"><table><thead><tr><th>Classe</th><th>Matière</th><th>Professeur</th><th></th></tr></thead><tbody>
<?php foreach ($aff as $a): ?><tr><td><?= e($a['classe']) ?></td><td><?= e($a['matiere']) ?></td><td><?= e($a['nom'] . ' ' . $a['prenom']) ?></td><td><?= $del('del_a', $a['id']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'inc/footer.php';
