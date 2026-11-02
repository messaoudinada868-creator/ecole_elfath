<?php
require 'inc/config.php'; require_role('admin'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            if (strlen(post('pass')) < 6) throw new Exception('Mot de passe : 6 caractères minimum.');
            db()->beginTransaction();
            q("INSERT INTO users(nom,prenom,email,mot_de_passe,telephone,role) VALUES(?,?,?,?,?, 'professeur')",
              [post('nom'), post('prenom'), post('email'), password_hash(post('pass'), PASSWORD_DEFAULT), post('tel') ?: null]);
            q('INSERT INTO professeurs(user_id,specialite) VALUES(?,?)', [db()->lastInsertId(), post('specialite') ?: null]);
            db()->commit(); flash('Professeur ajouté.');
        } elseif (isset($_POST['del'])) {
            q("DELETE FROM users WHERE id=? AND role='professeur'", [(int)$_POST['uid']]); flash('Professeur supprimé.');
        }
    } catch (Throwable $x) { if (db()->inTransaction()) db()->rollBack(); flash(err_msg($x), 'err'); }
    redirect('professeurs.php');
}
$title = 'Professeurs'; require 'inc/header.php';
$list = q('SELECT u.id uid,u.nom,u.prenom,u.email,u.telephone,p.specialite FROM professeurs p JOIN users u ON u.id=p.user_id ORDER BY u.nom')->fetchAll();
?>
<div class="card"><h2>Nouveau professeur</h2>
<form method="post" class="f"><?= csrf_field() ?>
  <div><label>Nom</label><input name="nom" required></div><div><label>Prénom</label><input name="prenom" required></div>
  <div><label>Email (identifiant)</label><input type="email" name="email" required></div>
  <div><label>Mot de passe</label><input type="password" name="pass" required minlength="6"></div>
  <div><label>Téléphone</label><input name="tel"></div><div><label>Spécialité</label><input name="specialite"></div>
  <div><button class="btn" name="add" value="1">Ajouter le professeur</button></div>
</form></div>
<div class="card"><h2>Liste (<?= count($list) ?>)</h2><div class="tw"><table><thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Spécialité</th><th></th></tr></thead><tbody>
<?php foreach ($list as $p): ?><tr><td><?= e($p['nom'] . ' ' . $p['prenom']) ?></td><td><?= e($p['email']) ?></td><td><?= e($p['telephone']) ?></td><td><?= e($p['specialite']) ?></td>
<td><form method="post" data-confirm="Supprimer ce professeur ?"><?= csrf_field() ?><input type="hidden" name="uid" value="<?= $p['uid'] ?>"><button class="btn sm red" name="del" value="1">Supprimer</button></form></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<?php require 'inc/footer.php';
