<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
$mois = preg_match('/^\d{4}-\d{2}$/', $_GET['mois'] ?? '') ? $_GET['mois'] : date('Y-m');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            if (!preg_match('/^\d{4}-\d{2}$/', post('mois'))) throw new Exception('Mois invalide.');
            $m = (float)str_replace(',', '.', post('montant'));
            if ($m <= 0) throw new Exception('Montant invalide.');
            q('INSERT INTO paiements(etudiant_id,type,mois,montant,date_paiement) VALUES(?,?,?,?,?)',
              [(int)post('etudiant_id'), post('type'), post('mois'), $m, post('date') ?: date('Y-m-d')]);
            flash('Paiement enregistré.'); redirect('paiements.php?mois=' . post('mois'));
        } elseif (isset($_POST['del'])) { q('DELETE FROM paiements WHERE id=?', [(int)$_POST['id']]); flash('Paiement supprimé.'); }
    } catch (PDOException $x) { flash($x->getCode() === '23000' ? 'Ce paiement existe déjà pour ce mois.' : 'Erreur base de données.', 'err'); }
    catch (Throwable $x) { flash($x->getMessage(), 'err'); }
    redirect('paiements.php?mois=' . $mois);
}
$title = 'Paiements'; require 'inc/header.php';
$etus = q('SELECT e.id,u.nom,u.prenom,c.nom classe,t.tarif_mensuel FROM etudiants e JOIN users u ON u.id=e.user_id JOIN classes c ON c.id=e.classe_id LEFT JOIN transports t ON t.id=e.transport_id ORDER BY u.nom')->fetchAll();
$pay = q('SELECT p.*,u.nom,u.prenom,c.nom classe FROM paiements p JOIN etudiants e ON e.id=p.etudiant_id JOIN users u ON u.id=e.user_id JOIN classes c ON c.id=e.classe_id WHERE p.mois=? ORDER BY p.date_paiement DESC,p.id DESC', [$mois])->fetchAll();
$imp = q("SELECT u.nom,u.prenom,c.nom classe FROM etudiants e JOIN users u ON u.id=e.user_id JOIN classes c ON c.id=e.classe_id
  WHERE NOT EXISTS (SELECT 1 FROM paiements p WHERE p.etudiant_id=e.id AND p.type='scolarite' AND p.mois=?) ORDER BY c.nom,u.nom", [$mois])->fetchAll();
$impT = q("SELECT u.nom,u.prenom,t.nom_bus FROM etudiants e JOIN users u ON u.id=e.user_id JOIN transports t ON t.id=e.transport_id
  WHERE NOT EXISTS (SELECT 1 FROM paiements p WHERE p.etudiant_id=e.id AND p.type='transport' AND p.mois=?) ORDER BY u.nom", [$mois])->fetchAll();
$total = array_sum(array_column($pay, 'montant'));
?>
<div class="card"><h2>Enregistrer un paiement</h2>
<?php if (!$etus): ?><p class="muted">Aucun étudiant.</p><?php else: ?>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Étudiant</label><select name="etudiant_id" id="petu" required>
    <?php foreach ($etus as $s): ?><option value="<?= $s['id'] ?>" data-tarif="<?= e($s['tarif_mensuel']) ?>"><?= e($s['nom'] . ' ' . $s['prenom'] . ' — ' . $s['classe']) ?></option><?php endforeach; ?></select></div>
  <div><label>Type</label><select name="type" id="ptype"><option value="scolarite">Scolarité</option><option value="transport">Transport</option><option value="inscription">Inscription</option></select></div>
  <div><label>Mois</label><input type="month" name="mois" value="<?= e($mois) ?>" required></div>
  <div><label>Montant (DH)</label><input name="montant" id="pmontant" required></div>
  <div><label>Date de paiement</label><input type="date" name="date" value="<?= date('Y-m-d') ?>"></div>
  <div><button class="btn" name="add" value="1">Enregistrer</button></div>
</form><?php endif; ?></div>

<div class="card"><h2>Mois affiché</h2>
<form method="get" class="f"><div><label>Mois</label><input type="month" name="mois" value="<?= e($mois) ?>"></div><div><button class="btn">Afficher</button></div></form></div>

<div class="card"><h2>Paiements de <?= e($mois) ?> — total <?= money($total) ?></h2>
<?php if (!$pay): echo '<p class="muted">Aucun paiement ce mois-ci.</p>'; else: ?>
<div class="tw"><table><thead><tr><th>Étudiant</th><th>Classe</th><th>Type</th><th>Montant</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($pay as $p): ?><tr><td><?= e($p['nom'] . ' ' . $p['prenom']) ?></td><td><?= e($p['classe']) ?></td><td><?= e($p['type']) ?></td><td><?= money($p['montant']) ?></td><td><?= e($p['date_paiement']) ?></td>
<td><form method="post" data-confirm="Supprimer ce paiement ?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn sm red" name="del" value="1">Supprimer</button></form></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></div>

<div class="card"><h2>Scolarité non payée (<?= count($imp) ?>)</h2>
<?php if (!$imp): echo '<p class="muted">Tout le monde a payé.</p>'; else: ?>
<div class="tw"><table><thead><tr><th>Étudiant</th><th>Classe</th></tr></thead><tbody>
<?php foreach ($imp as $i): ?><tr><td><?= e($i['nom'] . ' ' . $i['prenom']) ?></td><td><?= e($i['classe']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>

<div class="card"><h2>Transport non payé (<?= count($impT) ?>)</h2>
<?php if (!$impT): echo '<p class="muted">Rien à signaler.</p>'; else: ?>
<div class="tw"><table><thead><tr><th>Étudiant</th><th>Bus</th></tr></thead><tbody>
<?php foreach ($impT as $i): ?><tr><td><?= e($i['nom'] . ' ' . $i['prenom']) ?></td><td><?= e($i['nom_bus']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require 'inc/footer.php';
