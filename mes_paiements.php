<?php
require 'inc/config.php'; require_role('etudiant');
$title = 'Mes paiements'; require 'inc/header.php';
$eid = q('SELECT id FROM etudiants WHERE user_id=?', [$u['id']])->fetchColumn();
$pay = q('SELECT * FROM paiements WHERE etudiant_id=? ORDER BY mois DESC,type', [$eid ?: 0])->fetchAll();
$mois = date('Y-m');
$ok = fn($t) => (bool)q('SELECT 1 FROM paiements WHERE etudiant_id=? AND type=? AND mois=?', [$eid ?: 0, $t, $mois])->fetch();
?>
<div class="card"><h2>Mois en cours (<?= $mois ?>)</h2>
<p>Scolarité : <?= $ok('scolarite') ? '<span class="pill ok">Payée</span>' : '<span class="pill no">Non payée</span>' ?></p></div>
<div class="card"><h2>Historique</h2>
<?php if (!$pay): echo '<p class="muted">Aucun paiement enregistré.</p>'; else: ?>
<div class="tw"><table><thead><tr><th>Mois</th><th>Type</th><th>Montant</th><th>Date</th></tr></thead><tbody>
<?php foreach ($pay as $p): ?><tr><td><?= e($p['mois']) ?></td><td><?= e($p['type']) ?></td><td><?= money($p['montant']) ?></td><td><?= e($p['date_paiement']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require 'inc/footer.php';
