<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_annee'])) {
            if (!preg_match('/^\d{4}-\d{4}$/', post('libelle'))) throw new Exception('Format attendu : 2026-2027');
            if (isset($_POST['courante'])) q('UPDATE annees_scolaires SET courante=0');
            q('INSERT INTO annees_scolaires(libelle,courante) VALUES(?,?)', [post('libelle'), isset($_POST['courante']) ? 1 : 0]);
            flash('Année ajoutée.');
        } elseif (isset($_POST['set_courante'])) {
            q('UPDATE annees_scolaires SET courante=0'); q('UPDATE annees_scolaires SET courante=1 WHERE id=?', [(int)$_POST['id']]); flash('Année courante mise à jour.');
        } elseif (isset($_POST['add_salle'])) {
            q('INSERT INTO salles(nom,capacite) VALUES(?,?)', [post('nom'), (int)post('capacite') ?: 25]); flash('Salle ajoutée.');
        } elseif (isset($_POST['del_salle'])) {
            q('DELETE FROM salles WHERE id=?', [(int)$_POST['id']]); flash('Salle supprimée.');
        } elseif (isset($_POST['add_classe'])) {
            q('INSERT INTO classes(nom,niveau,salle_id,annee_id) VALUES(?,?,?,?)', [post('nom'), post('niveau'), post('salle_id') ?: null, (int)post('annee_id')]); flash('Classe ajoutée.');
        } elseif (isset($_POST['del_classe'])) {
            q('DELETE FROM classes WHERE id=?', [(int)$_POST['id']]); flash('Classe supprimée.');
        }
    } catch (Throwable $x) { flash(err_msg($x), 'err'); }
    redirect('classes.php');
}
$title = 'Classes & salles'; require 'inc/header.php';
$annees = q('SELECT * FROM annees_scolaires ORDER BY libelle DESC')->fetchAll();
$salles = q('SELECT * FROM salles ORDER BY nom')->fetchAll();
$classes = q('SELECT c.*,a.libelle,s.nom salle,(SELECT COUNT(*) FROM etudiants e WHERE e.classe_id=c.id) nb FROM classes c JOIN annees_scolaires a ON a.id=c.annee_id LEFT JOIN salles s ON s.id=c.salle_id ORDER BY a.libelle DESC,c.nom')->fetchAll();
$del = fn($name, $id, $msg) => '<form method="post" data-confirm="' . $msg . '">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><button class="btn sm red" name="' . $name . '" value="1">Supprimer</button></form>';
?>
<div class="card"><h2>Années scolaires</h2>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Libellé</label><input name="libelle" placeholder="2026-2027" required pattern="\d{4}-\d{4}"></div>
  <div><label><input type="checkbox" name="courante" style="width:auto"> Année courante</label></div>
  <div><button class="btn" name="add_annee" value="1">Ajouter</button></div></form>
<div class="tw" style="margin-top:14px"><table><thead><tr><th>Année</th><th>Statut</th><th></th></tr></thead><tbody>
<?php foreach ($annees as $a): ?><tr><td><?= e($a['libelle']) ?></td><td><?= $a['courante'] ? '<span class="pill ok">Courante</span>' : '' ?></td>
<td><?php if (!$a['courante']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn sm" name="set_courante" value="1">Définir courante</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div></div>

<div class="card"><h2>Salles</h2>
<form method="post" class="f"><?= csrf_field() ?><div><label>Nom</label><input name="nom" required></div><div><label>Capacité</label><input type="number" name="capacite" value="25" min="1"></div><div><button class="btn" name="add_salle" value="1">Ajouter</button></div></form>
<div class="tw" style="margin-top:14px"><table><thead><tr><th>Salle</th><th>Capacité</th><th></th></tr></thead><tbody>
<?php foreach ($salles as $s): ?><tr><td><?= e($s['nom']) ?></td><td><?= (int)$s['capacite'] ?></td><td><?= $del('del_salle', $s['id'], 'Supprimer cette salle ?') ?></td></tr><?php endforeach; ?></tbody></table></div></div>

<div class="card"><h2>Classes</h2>
<?php if (!$annees): ?><p class="muted">Ajoutez d’abord une année scolaire.</p><?php else: ?>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Nom</label><input name="nom" placeholder="CP-A" required></div>
  <div><label>Niveau</label><input name="niveau" placeholder="CP, CE1…" required></div>
  <div><label>Année</label><select name="annee_id"><?php foreach ($annees as $a): ?><option value="<?= $a['id'] ?>" <?= $a['courante'] ? 'selected' : '' ?>><?= e($a['libelle']) ?></option><?php endforeach; ?></select></div>
  <div><label>Salle</label><select name="salle_id"><option value="">—</option><?php foreach ($salles as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['nom']) ?></option><?php endforeach; ?></select></div>
  <div><button class="btn" name="add_classe" value="1">Ajouter la classe</button></div></form><?php endif; ?>
<div class="tw" style="margin-top:14px"><table><thead><tr><th>Classe</th><th>Niveau</th><th>Année</th><th>Salle</th><th>Élèves</th><th></th></tr></thead><tbody>
<?php foreach ($classes as $c): ?><tr><td><?= e($c['nom']) ?></td><td><?= e($c['niveau']) ?></td><td><?= e($c['libelle']) ?></td><td><?= e($c['salle'] ?? '—') ?></td><td><?= (int)$c['nb'] ?></td><td><?= $del('del_classe', $c['id'], 'Supprimer cette classe ?') ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'inc/footer.php';
