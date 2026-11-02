<?php
require 'inc/config.php'; require_role('professeur'); check_csrf();
$u = user();
$prof = q('SELECT id FROM professeurs WHERE user_id=?', [$u['id']])->fetchColumn();
$types = ['controle1' => 'Contrôle 1', 'controle2' => 'Contrôle 2', 'devoir' => 'Devoir', 'examen' => 'Examen'];
$affId = (int)($_REQUEST['aff'] ?? 0);
$sem = in_array($_REQUEST['sem'] ?? '', ['S1', 'S2'], true) ? $_REQUEST['sem'] : 'S1';
$type = isset($types[$_REQUEST['type'] ?? '']) ? $_REQUEST['type'] : 'controle1';

$affs = q('SELECT a.id,a.matiere_id,a.classe_id,c.nom classe,m.nom matiere FROM affectations_prof a JOIN classes c ON c.id=a.classe_id JOIN matieres m ON m.id=a.matiere_id WHERE a.professeur_id=? ORDER BY c.nom,m.nom', [$prof ?: 0])->fetchAll();
$cur = null; foreach ($affs as $a) if ($a['id'] === $affId) $cur = $a;   // l'affectation doit appartenir au prof

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cur) {
    try {
        $ids = q('SELECT id FROM etudiants WHERE classe_id=?', [$cur['classe_id']])->fetchAll(PDO::FETCH_COLUMN);
        $n = 0;
        foreach ($ids as $eid) {
            $v = str_replace(',', '.', trim($_POST['n'][$eid] ?? ''));
            if ($v === '') continue;
            if (!is_numeric($v) || $v < 0 || $v > 10) throw new Exception('Les notes doivent être comprises entre 0 et 10.');
            q('INSERT INTO notes(etudiant_id,matiere_id,professeur_id,valeur,type_evaluation,semestre) VALUES(?,?,?,?,?,?)
               ON DUPLICATE KEY UPDATE valeur=VALUES(valeur),professeur_id=VALUES(professeur_id)',
              [$eid, $cur['matiere_id'], $prof, round((float)$v, 2), $type, $sem]); $n++;
        }
        flash("$n note(s) enregistrée(s).");
    } catch (Throwable $x) { flash(err_msg($x), 'err'); }
    redirect("notes.php?aff=$affId&sem=$sem&type=$type");
}
$title = 'Saisie des notes'; require 'inc/header.php';
$rows = [];
if ($cur) $rows = q('SELECT e.id,u.nom,u.prenom,n.valeur FROM etudiants e JOIN users u ON u.id=e.user_id
   LEFT JOIN notes n ON n.etudiant_id=e.id AND n.matiere_id=? AND n.type_evaluation=? AND n.semestre=? WHERE e.classe_id=? ORDER BY u.nom,u.prenom',
   [$cur['matiere_id'], $type, $sem, $cur['classe_id']])->fetchAll();
?>
<div class="card"><h2>Choisir la classe</h2>
<?php if (!$affs): ?><p class="muted">Aucune classe ne vous est affectée. Contactez l’administration.</p><?php else: ?>
<form method="get" class="f">
  <div><label>Classe / matière</label><select name="aff"><?php foreach ($affs as $a): ?><option value="<?= $a['id'] ?>" <?= $a['id'] === $affId ? 'selected' : '' ?>><?= e($a['classe'] . ' — ' . $a['matiere']) ?></option><?php endforeach; ?></select></div>
  <div><label>Semestre</label><select name="sem"><option <?= $sem === 'S1' ? 'selected' : '' ?>>S1</option><option <?= $sem === 'S2' ? 'selected' : '' ?>>S2</option></select></div>
  <div><label>Évaluation</label><select name="type"><?php foreach ($types as $k => $l): ?><option value="<?= $k ?>" <?= $k === $type ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div><button class="btn">Afficher</button></div></form><?php endif; ?></div>
<?php if ($cur): ?>
<div class="card"><h2><?= e($cur['classe'] . ' — ' . $cur['matiere'] . ' — ' . $types[$type] . ' (' . $sem . ')') ?></h2>
<?php if (!$rows): echo '<p class="muted">Aucun étudiant dans cette classe.</p>'; else: ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="aff" value="<?= $affId ?>"><input type="hidden" name="sem" value="<?= $sem ?>"><input type="hidden" name="type" value="<?= $type ?>">
<div class="tw"><table><thead><tr><th>Étudiant</th><th>Note /10</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['nom'] . ' ' . $r['prenom']) ?></td>
<td><input class="notein" name="n[<?= $r['id'] ?>]" inputmode="decimal" pattern="\d{1,2}([.,]\d{1,2})?" value="<?= e($r['valeur']) ?>"></td></tr><?php endforeach; ?></tbody></table></div>
<p><button class="btn">Enregistrer les notes</button></p></form><?php endif; ?></div>
<?php endif; require 'inc/footer.php';
