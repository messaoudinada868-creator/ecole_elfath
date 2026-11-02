<?php
require 'inc/config.php'; require_role('etudiant');
$title = 'Mes notes'; require 'inc/header.php';
$eid = q('SELECT id FROM etudiants WHERE user_id=?', [$u['id']])->fetchColumn();
$rows = q('SELECT n.semestre,n.type_evaluation,n.valeur,m.nom,m.coefficient FROM notes n JOIN matieres m ON m.id=n.matiere_id WHERE n.etudiant_id=? ORDER BY n.semestre,m.nom', [$eid ?: 0])->fetchAll();
$data = [];
foreach ($rows as $r) {
    $data[$r['semestre']][$r['nom']]['coef'] = $r['coefficient'];
    $data[$r['semestre']][$r['nom']]['n'][$r['type_evaluation']] = (float)$r['valeur'];
}
$cols = ['controle1' => 'Contrôle 1', 'controle2' => 'Contrôle 2', 'devoir' => 'Devoir', 'examen' => 'Examen'];
if (!$data) echo '<div class="card"><p class="muted">Aucune note disponible pour le moment.</p></div>';
foreach ($data as $sem => $mats): $sp = 0; $sc = 0; ?>
<div class="card"><h2>Semestre <?= e(substr($sem, 1)) ?></h2><div class="tw"><table>
<thead><tr><th>Matière</th><th>Coef.</th><?php foreach ($cols as $l): ?><th><?= $l ?></th><?php endforeach; ?><th>Moyenne /10</th></tr></thead><tbody>
<?php foreach ($mats as $nom => $m): $avg = array_sum($m['n']) / count($m['n']); $sp += $avg * $m['coef']; $sc += $m['coef']; ?>
<tr><td><?= e($nom) ?></td><td><?= e($m['coef']) ?></td>
<?php foreach ($cols as $k => $l): ?><td><?= isset($m['n'][$k]) ? number_format($m['n'][$k], 2) : '—' ?></td><?php endforeach; ?>
<td><b><?= number_format($avg, 2) ?></b></td></tr><?php endforeach; ?>
</tbody></table></div>
<p><b>Moyenne générale : <?= number_format($sc ? $sp / $sc : 0, 2) ?> / 10</b> <span class="muted">(pondérée par les coefficients)</span></p></div>
<?php endforeach; require 'inc/footer.php';
