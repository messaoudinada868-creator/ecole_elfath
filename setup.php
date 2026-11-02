<?php
// À exécuter UNE FOIS après import de database.sql, puis SUPPRIMER ce fichier.
require 'inc/config.php';
try { $done = q("SELECT COUNT(*) FROM users WHERE email='admin@elfath.ma'")->fetchColumn() > 0; }
catch (Throwable $x) { exit('Importez d’abord database.sql dans phpMyAdmin.'); }

const COMPTES = [
  ['Administrateur', 'admin@elfath.ma', 'Admin@2026', 'Direction'],
  ['Professeur',     'prof.math@elfath.ma', 'Prof@2026', 'Karim Benali — Mathématiques'],
  ['Professeur',     'prof.fr@elfath.ma', 'Prof@2026', 'Salma Idrissi — Français'],
  ['Étudiant',       'ahmed.alaoui@elfath.ma', 'Etu@2026', 'Ahmed Alaoui — CP-A'],
  ['Étudiant',       'sara.mansouri@elfath.ma', 'Etu@2026', 'Sara Mansouri — CP-A'],
  ['Étudiant',       'youssef.berrada@elfath.ma', 'Etu@2026', 'Youssef Berrada — CE1-A (bus)'],
];
$ok = false;
if (!$done && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $h = fn($p) => password_hash($p, PASSWORD_DEFAULT);
    $user = function ($nom, $pre, $mail, $pw, $tel, $role) use ($h) {
        q('INSERT INTO users(nom,prenom,email,mot_de_passe,telephone,role) VALUES(?,?,?,?,?,?)', [$nom, $pre, $mail, $h($pw), $tel, $role]);
        return (int)db()->lastInsertId();
    };
    $id = fn() => (int)db()->lastInsertId();
    db()->beginTransaction();
    try {
        $y = (int)date('n') >= 8 ? (int)date('Y') : (int)date('Y') - 1;
        $lib = $y . '-' . ($y + 1);
        q('UPDATE annees_scolaires SET courante=0');
        q('INSERT INTO annees_scolaires(libelle,courante) VALUES(?,1) ON DUPLICATE KEY UPDATE courante=1', [$lib]);
        $an = (int)q('SELECT id FROM annees_scolaires WHERE libelle=?', [$lib])->fetchColumn();

        $admin = $user('Direction', 'Elfath', 'admin@elfath.ma', 'Admin@2026', '0600000000', 'admin');
        $pm = $user('Benali', 'Karim', 'prof.math@elfath.ma', 'Prof@2026', '0611111111', 'professeur');
        q('INSERT INTO professeurs(user_id,specialite) VALUES(?,?)', [$pm, 'Mathématiques']); $pmId = $id();
        $pf = $user('Idrissi', 'Salma', 'prof.fr@elfath.ma', 'Prof@2026', '0622222222', 'professeur');
        q('INSERT INTO professeurs(user_id,specialite) VALUES(?,?)', [$pf, 'Français']); $pfId = $id();

        q('INSERT INTO salles(nom,capacite) VALUES("Salle 1",25)'); $s1 = $id();
        q('INSERT INTO salles(nom,capacite) VALUES("Salle 2",25)'); $s2 = $id();
        q('INSERT INTO classes(nom,niveau,salle_id,annee_id) VALUES("CP-A","CP",?,?)', [$s1, $an]); $cp = $id();
        q('INSERT INTO classes(nom,niveau,salle_id,annee_id) VALUES("CE1-A","CE1",?,?)', [$s2, $an]); $ce = $id();
        q('INSERT INTO transports(nom_bus,matricule,chauffeur,tel_chauffeur,trajet,tarif_mensuel) VALUES("Bus 1","12345-A-1","Hassan","0633333333","Centre ville - École",200)'); $bus = $id();

        foreach ([['Mathématiques', 3], ['Français', 3], ['Arabe', 2]] as [$n, $c]) { q('INSERT INTO matieres(nom,coefficient) VALUES(?,?)', [$n, $c]); $m[$n] = $id(); }
        foreach ([[$pmId, 'Mathématiques', $cp], [$pmId, 'Mathématiques', $ce], [$pfId, 'Français', $cp]] as [$p, $mn, $c])
            q('INSERT INTO affectations_prof(professeur_id,matiere_id,classe_id) VALUES(?,?,?)', [$p, $m[$mn], $c]);

        $etu = [['Alaoui', 'Ahmed', 'ahmed.alaoui@elfath.ma', $cp, 'M130000001', null], ['Mansouri', 'Sara', 'sara.mansouri@elfath.ma', $cp, 'M130000002', null], ['Berrada', 'Youssef', 'youssef.berrada@elfath.ma', $ce, 'M120000003', $bus]];
        foreach ($etu as $i => [$nom, $pre, $mail, $cl, $mas, $tr]) {
            $uid = $user($nom, $pre, $mail, 'Etu@2026', '0644444444', 'etudiant');
            q('INSERT INTO etudiants(user_id,classe_id,date_naissance,code_massar,nom_parent,telephone_parent,transport_id) VALUES(?,?,?,?,?,?,?)',
              [$uid, $cl, '2019-05-1' . ($i + 1), $mas, 'Parent ' . $nom, '0655555555', $tr]);
            $E[$i] = $id();
        }
        $notes = [[0, 'Mathématiques', $pmId, [8.5, 7.5, 9]], [1, 'Mathématiques', $pmId, [6, 7, 6.5]], [2, 'Mathématiques', $pmId, [9, 9.5, 8.5]],
                  [0, 'Français', $pfId, [7, 8, 7.5]], [1, 'Français', $pfId, [9, 8.5, 9.5]]];
        foreach ($notes as [$e, $mn, $p, $vals]) foreach (['controle1', 'controle2', 'examen'] as $k => $t)
            q('INSERT INTO notes(etudiant_id,matiere_id,professeur_id,valeur,type_evaluation,semestre) VALUES(?,?,?,?,?,"S1")', [$E[$e], $m[$mn], $p, $vals[$k], $t]);

        $mois = date('Y-m');
        foreach ([[0, 'scolarite', 400], [1, 'scolarite', 400], [2, 'transport', 200]] as [$e, $t, $mt])
            q('INSERT INTO paiements(etudiant_id,type,mois,montant,date_paiement) VALUES(?,?,?,?,CURDATE())', [$E[$e], $t, $mois, $mt]);
        q('INSERT INTO annonces(titre,contenu,cible,created_by) VALUES(?,?,?,?)',
          ['Bienvenue sur l’espace Elfath', 'Retrouvez ici vos notes, vos paiements et les annonces de l’école.', 'tous', $admin]);
        db()->commit(); $ok = true;
    } catch (Throwable $x) { db()->rollBack(); exit('Erreur : ' . e($x->getMessage())); }
}
?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installation</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login"><div class="lbox" style="max-width:620px"><img src="assets/img/logo.png" alt=""><h1>Installation</h1>
<?php if ($done): ?><p style="direction:ltr">Les données de démonstration existent déjà. Supprimez <b>setup.php</b>.</p>
<?php else: ?>
  <?php if (!$ok): ?><p style="direction:ltr">Crée l’année en cours, 2 classes, 3 matières, 1 bus, 2 professeurs, 3 étudiants, des notes, paiements et une annonce.</p>
  <form method="post"><?= csrf_field() ?><button class="btn">Installer les données de démonstration</button></form>
  <?php else: ?><div class="alert ok">Installation terminée. Supprimez <b>setup.php</b>.</div><?php endif; ?>
<?php endif; ?>
<?php if ($ok || $done): ?>
<div class="tw" style="text-align:left;margin-top:12px"><table><thead><tr><th>Rôle</th><th>Email</th><th>Mot de passe</th><th>Qui</th></tr></thead><tbody>
<?php foreach (COMPTES as [$r, $m, $p, $w]): ?><tr><td><?= $r ?></td><td><?= $m ?></td><td><code><?= $p ?></code></td><td><?= $w ?></td></tr><?php endforeach; ?>
</tbody></table></div><p style="direction:ltr"><a href="login.php">Aller à la connexion</a></p>
<?php endif; ?>
</div></body></html>
