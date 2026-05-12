CREATE DATABASE IF NOT EXISTS bibliotheque_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bibliotheque_db;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('visiteur','adherent','bibliothecaire','administrateur') DEFAULT 'visiteur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE adherent (
    id_utilisateur INT PRIMARY KEY,
    telephone VARCHAR(20),
    status ENUM('actif','inactif') DEFAULT 'actif',
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
);

CREATE TABLE abonnement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_adherent INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_adherent) REFERENCES adherent(id_utilisateur)
);

CREATE TABLE auteur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    adresse_travail VARCHAR(255),
    origine VARCHAR(100),
    centre_interet1 VARCHAR(100),
    centre_interet2 VARCHAR(100),
    centre_interet3 VARCHAR(100)
);

CREATE TABLE maison_edition (
    id_edition INT AUTO_INCREMENT PRIMARY KEY,
    raison_social VARCHAR(200) NOT NULL,
    adresse VARCHAR(255),
    nom_directeur VARCHAR(150),
    nom_responsable VARCHAR(150)
);

CREATE TABLE document (
    code_doc INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(300) NOT NULL,
    date_parution DATE,
    nombre_exemplaires_acquis INT DEFAULT 1,
    nombre_exemplaires_pretes INT DEFAULT 0,
    mots_cles TEXT,
    type_doc ENUM('livre','revue') NOT NULL,
    id_edition INT,
    FOREIGN KEY (id_edition) REFERENCES maison_edition(id_edition)
);

CREATE TABLE livre (
    code_doc INT PRIMARY KEY,
    isbn VARCHAR(20),
    genre VARCHAR(100),
    FOREIGN KEY (code_doc) REFERENCES document(code_doc) ON DELETE CASCADE
);

CREATE TABLE revue (
    code_doc INT PRIMARY KEY,
    periodicite VARCHAR(50),
    date_abonnement DATE,
    montant_abonnement DECIMAL(10,2),
    issn VARCHAR(20),
    FOREIGN KEY (code_doc) REFERENCES document(code_doc) ON DELETE CASCADE
);

CREATE TABLE document_auteur (
    code_doc INT,
    id_auteur INT,
    PRIMARY KEY (code_doc, id_auteur),
    FOREIGN KEY (code_doc) REFERENCES document(code_doc) ON DELETE CASCADE,
    FOREIGN KEY (id_auteur) REFERENCES auteur(id)
);

CREATE TABLE emprunt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_adherent INT NOT NULL,
    code_doc INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL,
    date_retour_effective DATE NULL,
    statut ENUM('en_cours','retourne','en_retard') DEFAULT 'en_cours',
    FOREIGN KEY (id_adherent) REFERENCES adherent(id_utilisateur),
    FOREIGN KEY (code_doc) REFERENCES document(code_doc)
);

-- Données de test
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES
('Système', 'Admin', 'admin@biblio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrateur'),
('Martin', 'Sophie', 'biblio@biblio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bibliothecaire'),
('Benali', 'Ahmed', 'ahmed@etudiant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent'),
('Trabelsi', 'Sana', 'sana@etudiant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent'),
('Mansouri', 'Karim', 'karim@etudiant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent');

INSERT INTO adherent (id_utilisateur, telephone, status) VALUES
(3, '55123456', 'actif'),
(4, '55789012', 'actif'),
(5, '55345678', 'inactif');

INSERT INTO abonnement (id_adherent, date_debut, date_fin, montant) VALUES
(3, '2024-01-01', '2026-12-31', 50.00),
(4, '2024-03-01', '2026-12-31', 50.00);

INSERT INTO auteur (nom, prenom, adresse_travail, origine, centre_interet1, centre_interet2) VALUES
('Knuth', 'Donald', 'Stanford University', 'USA', 'Algorithmique', 'Programmation'),
('Dijkstra', 'Edsger', 'University of Texas', 'Pays-Bas', 'Théorie des graphes', 'Systèmes'),
('Codd', 'Edgar', 'IBM Research', 'UK', 'Base de données', 'Algèbre relationnelle'),
('Martin', 'Robert', 'Clean Code Inc.', 'USA', 'Génie logiciel', 'Méthodologie'),
('Sommerville', 'Ian', 'Lancaster University', 'UK', 'Génie logiciel', 'Méthodologie');

INSERT INTO maison_edition (raison_social, adresse, nom_directeur, nom_responsable) VALUES
('Addison-Wesley', '1 Jacob Way, Reading MA', 'Brian Kernighan', 'Mary Johnson'),
('O''Reilly Media', '1005 Gravenstein Hwy N', 'Tim O''Reilly', 'Laura Baldwin'),
('Pearson Education', '221 River Street, Hoboken', 'Chris Zurn', 'Susan Hartman'),
('Dunod', '11 rue Paul Bert, Paris', 'Pierre Faure', 'Anne Durand');

INSERT INTO document (titre, date_parution, nombre_exemplaires_acquis, nombre_exemplaires_pretes, mots_cles, type_doc, id_edition) VALUES
('The Art of Computer Programming', '1968-01-01', 5, 2, 'algorithmique,programmation,informatique', 'livre', 1),
('Clean Code: A Handbook of Agile Software', '2008-08-01', 4, 1, 'clean code,génie logiciel,programmation', 'livre', 2),
('Software Engineering', '2015-06-01', 6, 0, 'génie logiciel,ingénierie,systèmes', 'livre', 3),
('Introduction aux bases de données', '2010-09-01', 3, 1, 'base de données,SQL,relationnelle', 'livre', 4),
('Design Patterns', '1994-10-01', 4, 2, 'design patterns,orienté objet,conception', 'livre', 1),
('Revue Informatique et Systèmes', '2023-01-01', 2, 0, 'informatique,systèmes,recherche', 'revue', 2),
('Journal of Software Engineering', '2023-03-01', 1, 0, 'génie logiciel,recherche,publications', 'revue', 3),
('Algorithmes Avancés', '2019-02-01', 3, 0, 'algorithmes,complexité,structures de données', 'livre', 4);

INSERT INTO livre (code_doc, isbn, genre) VALUES
(1, '978-0-201-03801-0', 'Informatique'),
(2, '978-0-13-235088-4', 'Génie Logiciel'),
(3, '978-0-13-394303-0', 'Ingénierie'),
(4, '978-2-10-049297-0', 'Base de données'),
(5, '978-0-20-163361-5', 'Conception'),
(8, '978-2-10-078234-1', 'Algorithmique');

INSERT INTO revue (code_doc, periodicite, date_abonnement, montant_abonnement, issn) VALUES
(6, 'Mensuelle', '2023-01-01', 120.00, '1234-5678'),
(7, 'Trimestrielle', '2023-03-01', 80.00, '8765-4321');

INSERT INTO document_auteur (code_doc, id_auteur) VALUES
(1,1),(2,4),(3,5),(4,3),(5,4),(8,1),(8,2);

INSERT INTO emprunt (id_adherent, code_doc, date_emprunt, date_retour_prevue, statut) VALUES
(3, 1, '2025-11-01', '2025-11-15', 'en_retard'),
(3, 2, '2025-12-01', '2025-12-15', 'retourne'),
(4, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'en_cours'),
(3, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 'en_cours');