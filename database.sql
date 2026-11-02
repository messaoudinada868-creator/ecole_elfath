CREATE DATABASE IF NOT EXISTS gestion_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_ecole;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  prenom VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  telephone VARCHAR(20),
  role ENUM('admin','professeur','etudiant') NOT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE annees_scolaires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(9) NOT NULL UNIQUE,
  courante TINYINT(1) NOT NULL DEFAULT 0
);
CREATE TABLE salles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  capacite INT DEFAULT 25
);
CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  niveau VARCHAR(50) NOT NULL,
  salle_id INT NULL,
  annee_id INT NOT NULL,
  FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE SET NULL,
  FOREIGN KEY (annee_id) REFERENCES annees_scolaires(id)
);
CREATE TABLE transports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom_bus VARCHAR(60) NOT NULL,
  matricule VARCHAR(20),
  chauffeur VARCHAR(80),
  tel_chauffeur VARCHAR(20),
  trajet VARCHAR(200),
  tarif_mensuel DECIMAL(8,2) NOT NULL DEFAULT 0
);
CREATE TABLE etudiants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  classe_id INT NOT NULL,
  date_naissance DATE,
  code_massar VARCHAR(20),
  nom_parent VARCHAR(100),
  telephone_parent VARCHAR(20),
  cin_parent VARCHAR(15),
  transport_id INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
  FOREIGN KEY (transport_id) REFERENCES transports(id) ON DELETE SET NULL
);
CREATE TABLE paiements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id INT NOT NULL,
  type ENUM('scolarite','transport','inscription') NOT NULL,
  mois CHAR(7) NOT NULL,
  montant DECIMAL(8,2) NOT NULL,
  date_paiement DATE NOT NULL,
  UNIQUE (etudiant_id, type, mois),
  FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
);
CREATE TABLE professeurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  specialite VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE matieres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  coefficient DECIMAL(3,1) NOT NULL DEFAULT 1
);
CREATE TABLE affectations_prof (
  id INT AUTO_INCREMENT PRIMARY KEY,
  professeur_id INT NOT NULL,
  matiere_id INT NOT NULL,
  classe_id INT NOT NULL,
  UNIQUE (professeur_id, matiere_id, classe_id),
  FOREIGN KEY (professeur_id) REFERENCES professeurs(id) ON DELETE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE,
  FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE
);
CREATE TABLE emploi_du_temps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  affectation_id INT NOT NULL,
  salle_id INT NULL,
  jour ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi') NOT NULL,
  heure_debut TIME NOT NULL,
  heure_fin TIME NOT NULL,
  FOREIGN KEY (affectation_id) REFERENCES affectations_prof(id) ON DELETE CASCADE,
  FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE SET NULL
);
CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id INT NOT NULL,
  matiere_id INT NOT NULL,
  professeur_id INT NOT NULL,
  valeur DECIMAL(4,2) NOT NULL,
  type_evaluation ENUM('controle1','controle2','examen','devoir') NOT NULL,
  semestre ENUM('S1','S2') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (etudiant_id, matiere_id, type_evaluation, semestre),
  CHECK (valeur BETWEEN 0 AND 10),
  FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE,
  FOREIGN KEY (professeur_id) REFERENCES professeurs(id)
);
CREATE TABLE annonces (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  contenu TEXT NOT NULL,
  cible ENUM('tous','etudiants','professeurs') DEFAULT 'tous',
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
