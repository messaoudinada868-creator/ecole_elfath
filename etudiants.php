<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            if (strlen(post('pass')) < 6) throw new Exception('Mot de passe : 6 caractères minimum.');
            db()->beginTransaction();
            q("INSERT INTO users(nom,prenom,email,mot_de_passe,telephone,role) VALUES(?,?,?,?,?, 'etudiant')",
              [post('nom'), post('prenom'), post('email'), password_hash(post('pass'), PASSWORD_DEFAULT), post('tel') ?: null]);
            $uid = db()->lastInsertId();
            q('INSERT INTO etudiants(user_id,classe_id,date_naissance,code_massar,nom_parent,telephone_parent,cin_parent,transport_id) VALUES(?,?,?,?,?,?,?,?)',
              [$uid, (int)post('classe_id'), post('dn') ?: null, post('massar') ?: null, post('parent') ?: null, post('tel_parent') ?: null, post('cin') ?: null, post('transport_id') ?: null]);
            db()->commit(); flash('Étudiant ajouté.');
        } elseif (isset($_POST['del'])) {
            q("DELETE FROM users WHERE id=? AND role='etudiant'", [(int)$_POST['uid']]); flash('Étudiant supprimé.');
        }
    } catch (Throwable $x) { if (db()->inTransaction()) db()->rollBack(); flash(err_msg($x), 'err'); }
    redirect('etudiants.php');
}
$title = 'Étudiants'; require 'inc/header.php';
$classes = q('SELECT c.id,c.nom,a.libelle FROM classes c JOIN annees_scolaires a ON a.id=c.annee_id ORDER BY a.libelle DESC,c.nom')->fetchAll();
$bus = q('SELECT id,nom_bus FROM transports ORDER BY nom_bus')->fetchAll();
$list = q('SELECT u.id uid,u.nom,u.prenom,u.email,c.nom classe,e.code_massar,e.nom_parent,e.telephone_parent,t.nom_bus
  FROM etudiants e JOIN users u ON u.id=e.user_id JOIN classes c ON c.id=e.classe_id LEFT JOIN transports t ON t.id=e.transport_id ORDER BY c.nom,u.nom')->fetchAll();
?>
<div class="card"><h2>Nouvel étudiant</h2>
<?php if (!$classes): ?><p class="muted">Créez d’abord une classe (menu « Classes & salles »).</p><?php else: ?>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Nom</label><input name="nom" required></div>
  <div><label>Prénom</label><input name="prenom" required></div>
  <div><label>Email (identifiant)</label><input type="email" name="email" required></div>
  <div><label>Mot de passe</label><input type="password" name="pass" required minlength="6"></div>
  <div><label>Téléphone</label><input name="tel"></div>
  <div><label>Classe</label><select name="classe_id" required>
    <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['nom'] . ' (' . $c['libelle'] . ')') ?></option><?php endforeach; ?></select></div>
  <div><label>Date de naissance</label><input type="date" name="dn"></div>
  <div><label>Code Massar</label><input name="massar"></div>
  <div><label>Nom du parent</label><input name="parent"></div>
  <div><label>Téléphone du parent</label><input name="tel_parent"></div>
  <div><label>CIN du parent</label><input name="cin"></div>
  <div><label>Transport</label><select name="transport_id"><option value="">Aucun</option>
    <?php foreach ($bus as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['nom_bus']) ?></option><?php endforeach; ?></select></div>
  <div><button class="btn" name="add" value="1">Ajouter l’étudiant</button></div>
</form><?php endif; ?></div>

<div class="card"><h2>Liste (<?= count($list) ?>)</h2>
<p><input data-filter="#tb" placeholder="Rechercher un nom, une classe…"></p>
<div class="tw"><table id="tb"><thead><tr><th>Nom</th><th>Classe</th><th>Massar</th><th>Parent</th><th>Tél. parent</th><th>Transport</th><th></th></tr></thead><tbody>
<?php foreach ($list as $s): ?><tr>
  <td><?= e($s['nom'] . ' ' . $s['prenom']) ?><br><small class="muted"><?= e($s['email']) ?></small></td>
  <td><?= e($s['classe']) ?></td><td><?= e($s['code_massar']) ?></td><td><?= e($s['nom_parent']) ?></td>
  <td><?= e($s['telephone_parent']) ?></td><td><?= e($s['nom_bus'] ?? '—') ?></td>
  <td><form method="post" data-confirm="Supprimer cet étudiant et toutes ses données ?"><?= csrf_field() ?><input type="hidden" name="uid" value="<?= $s['uid'] ?>"><button class="btn sm red" name="del" value="1">Supprimer</button></form></td>
</tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'inc/footer.php';
