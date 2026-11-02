<?php
require 'inc/config.php'; require_login();
$title = 'Tableau de bord'; require 'inc/header.php';
$cibles = ['admin' => ['tous','etudiants','professeurs'], 'professeur' => ['tous','professeurs'], 'etudiant' => ['tous','etudiants']][$role];
$in = implode(',', array_fill(0, count($cibles), '?'));
$anns = q("SELECT * FROM annonces WHERE cible IN ($in) ORDER BY created_at DESC LIMIT 4", $cibles)->fetchAll();

if ($role === 'admin'):
  $n = fn($t) => q("SELECT COUNT(*) FROM $t")->fetchColumn();
  $mois = date('Y-m');
  $enc = q('SELECT COALESCE(SUM(montant),0) FROM paiements WHERE mois=?', [$mois])->fetchColumn();
  $an = annee_courante();
?>
<div class="grid">
  <div class="stat"><b><?= $n('etudiants') ?></b><span>Étudiants</span></div>
  <div class="stat"><b><?= $n('professeurs') ?></b><span>Professeurs</span></div>
  <div class="stat"><b><?= $n('classes') ?></b><span>Classes</span></div>
  <div class="stat"><b><?= money($enc) ?></b><span>Encaissé en <?= $mois ?></span></div>
</div>
<?php if (!$an): ?><div class="alert err">Aucune année scolaire courante : définissez-la dans « Classes & salles ».</div><?php endif;
elseif ($role === 'professeur'):
  $aff = q('SELECT c.nom classe, m.nom matiere FROM affectations_prof a JOIN professeurs p ON p.id=a.professeur_id JOIN classes c ON c.id=a.classe_id JOIN matieres m ON m.id=a.matiere_id WHERE p.user_id=? ORDER BY c.nom', [$u['id']])->fetchAll();
?>
<div class="card"><h2>Mes classes</h2>
<?php if (!$aff): ?><p class="muted">Aucune affectation pour le moment.</p><?php else: ?>
<div class="tw"><table><thead><tr><th>Classe</th><th>Matière</th></tr></thead><tbody>
<?php foreach ($aff as $a): ?><tr><td><?= e($a['classe']) ?></td><td><?= e($a['matiere']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<?php else:
  $et = q('SELECT c.nom classe, t.nom_bus FROM etudiants e JOIN classes c ON c.id=e.classe_id LEFT JOIN transports t ON t.id=e.transport_id WHERE e.user_id=?', [$u['id']])->fetch();
?>
<div class="grid">
  <div class="stat"><b><?= e($et['classe'] ?? '—') ?></b><span>Ma classe</span></div>
  <div class="stat"><b><?= e($et['nom_bus'] ?? 'Aucun') ?></b><span>Transport</span></div>
</div>
<?php endif; ?>
<div class="card"><h2>Dernières annonces</h2>
<?php if (!$anns) echo '<p class="muted">Aucune annonce.</p>'; foreach ($anns as $a): ?>
  <div class="ann"><h3><?= e($a['titre']) ?></h3><small><?= e($a['created_at']) ?></small><p><?= nl2br(e($a['contenu'])) ?></p></div>
<?php endforeach; ?></div>
<?php require 'inc/footer.php';
