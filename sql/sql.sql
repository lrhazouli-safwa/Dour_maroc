-- ===== BASE DE DONNÉES DOUR MAROC =====
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS dourmaroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dourmaroc;

-- ===== TABLE DES ADMINISTRATEURS =====
CREATE TABLE administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nom_complet VARCHAR(100),
    role ENUM('admin', 'moderateur') DEFAULT 'admin',
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
);

-- ===== TABLE DES ARTISANS =====
CREATE TABLE artisans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    ville VARCHAR(100),
    bio TEXT,
    photo VARCHAR(255),
    email VARCHAR(100),
    telephone VARCHAR(20),
    experience_annees INT DEFAULT 0,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

-- ===== TABLE DES CATÉGORIES =====
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icone VARCHAR(100),
    couleur VARCHAR(7) DEFAULT '#d4af37',
    ordre INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE
);

-- ===== TABLE DES PRODUITS =====
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    prix_promo DECIMAL(10,2) NULL,
    categorie_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    artisan_id INT,
    stock INT DEFAULT 0,
    poids DECIMAL(8,2) DEFAULT 0.00,
    dimensions VARCHAR(50),
    materiau VARCHAR(100),
    technique VARCHAR(100),
    temps_fabrication VARCHAR(50),
    note_moyenne DECIMAL(3,2) DEFAULT 0.00,
    nombre_avis INT DEFAULT 0,
    vendu BOOLEAN DEFAULT FALSE,
    en_vedette BOOLEAN DEFAULT FALSE,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE SET NULL
);

-- ===== TABLE DES MESSAGES DE CONTACT =====
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    sujet VARCHAR(200),
    message TEXT NOT NULL,
    ip_adresse VARCHAR(45),
    user_agent TEXT,
    statut ENUM('nouveau', 'lu', 'repondu', 'archivé') DEFAULT 'nouveau',
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_lecture TIMESTAMP NULL,
    reponse TEXT,
    date_reponse TIMESTAMP NULL
);

-- ===== TABLE DES AVIS CLIENTS =====
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    nom_client VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    approuve BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
);

-- ===== TABLE DES NEWSLETTER =====
CREATE TABLE newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    nom VARCHAR(100),
    actif BOOLEAN DEFAULT TRUE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_desinscription TIMESTAMP NULL
);

-- ===== INSERTION DES DONNÉES DE TEST =====

-- Insertion d'un admin de test (mot de passe: admin123)
INSERT INTO administrateurs (username, password, email, nom_complet) 
VALUES('admin', 'admin123', 'admin@dourmaroc.ma', 'Administrateur Principal');

-- Insertion des catégories
INSERT INTO categories (nom, description, icone, couleur, ordre) VALUES
('Céramique', 'Poterie traditionnelle, tajines, vases et objets décoratifs', 'ceramic', '#d4af37', 1),
('Tapis', 'Tapis berbères, kilims et tapis de prière', 'carpet', '#8b4513', 2),
('Lanternes', 'Lanternes en fer forgé et verre coloré', 'lantern', '#ff6b35', 3),
('Bijoux', 'Bijoux berbères en argent et pierres précieuses', 'jewelry', '#e74c3c', 4),
('Bois sculpté', 'Objets en bois sculpté et marqueterie', 'wood', '#27ae60', 5),
('Cuir', 'Babouches, sacs et accessoires en cuir', 'leather', '#f39c12', 6);

-- Insertion des artisans
INSERT INTO artisans (nom, prenom, specialite, region, ville, bio, photo, email, telephone, experience_annees) VALUES
('El Fassi', 'Mohammed', 'Céramique', 'Doukkala-Abda', 'Safi', 'Maître potier à Safi depuis 25 ans, reconnu pour ses tajines peints à la main avec des motifs traditionnels. Chaque pièce raconte une histoire.', 'artisan1.jpg', 'mohammed.elfassi@email.ma', '+212 6 12 34 56 78', 25),
('Aït Lahcen', 'Amina', 'Tissage', 'Moyen Atlas', 'Azrou', 'Tisserande du Moyen Atlas, créatrice de tapis Beni Ouarain en laine pure. Ses tapis sont réputés pour leur qualité et leurs motifs ancestraux.', 'artisan2.jpg', 'amina.aitlahcen@email.ma', '+212 6 23 45 67 89', 18),
('Tazi', 'Youssef', 'Métal forgé', 'Marrakech-Safi', 'Marrakech', 'Forgeron à Marrakech depuis 30 ans, créateur de lanternes en fer forgé et objets décoratifs. Son savoir-faire est transmis de génération en génération.', 'artisan3.jpg', 'youssef.tazi@email.ma', '+212 6 34 56 78 90', 30),
('Benjelloun', 'Fatima', 'Bijoux', 'Fès-Meknès', 'Fès', 'Artisane bijoutière de Fès, spécialisée dans les bijoux berbères traditionnels en argent. Chaque bijou est une œuvre d\'art unique.', 'artisan4.jpg', 'fatima.benjelloun@email.ma', '+212 6 45 67 89 01', 22),
('Alaoui', 'Hassan', 'Bois sculpté', 'Tanger-Tétouan', 'Tétouan', 'Sculpteur sur bois de Tétouan, créateur d\'objets décoratifs et de meubles traditionnels. Son travail allie tradition et modernité.', 'artisan5.jpg', 'hassan.alaoui@email.ma', '+212 6 56 78 90 12', 15);

-- Insertion des produits
INSERT INTO produits (nom, description, prix, categorie_id, image, image_url, artisan_id, stock, poids, dimensions, materiau, technique, temps_fabrication, en_vedette) VALUES
('Tajine traditionnel Safi', 'Tajine en céramique peinte à la main avec motifs géométriques traditionnels. Parfait pour la cuisine traditionnelle marocaine.', 450.00, 1, 'tajine_safi.jpg', 'foto/tajine_safi.jpg', 1, 15, 2.5, '30x30x15 cm', 'Argile naturelle', 'Tournage et peinture manuelle', '3-4 jours', TRUE),
('Tapis Beni Ouarain', 'Tapis fait main 100% laine du Moyen Atlas, motifs géométriques traditionnels. Dimensions: 200x150 cm.', 2800.00, 2, 'tapis_beni_ouarain.jpg', 'foto/tapis_beni_ouarain.jpg', 2, 8, 8.0, '200x150 cm', 'Laine pure', 'Tissage manuel', '2-3 semaines', TRUE),
('Lanterne en fer forgé', 'Lanterne artisanale en fer forgé incrustée de verre coloré. Créée selon les techniques traditionnelles de Marrakech.', 650.00, 3, 'lanterne_fer_forge.jpg', 'foto/lanterne_fer_forge.jpg', 3, 12, 3.2, '25x25x40 cm', 'Fer forgé et verre', 'Forge et soudure', '1 semaine', TRUE),
('Collier berbère argent', 'Collier traditionnel en argent 925 avec pierres semi-précieuses. Motifs berbères ancestraux.', 850.00, 4, 'collier_berbere.jpg', 'foto/collier_berbere.jpg', 4, 20, 0.3, '45 cm', 'Argent 925', 'Ciselure manuelle', '4-5 jours', FALSE),
('Plateau en cuivre martelé', 'Plateau en cuivre martelé à la main avec motifs floraux. Finition soignée et brillante.', 750.00, 3, 'plateau_cuivre.jpg', 'foto/plateau_cuivre.jpg', 3, 10, 1.8, '40x40 cm', 'Cuivre pur', 'Martelage manuel', '1 semaine', FALSE),
('Babouches Fès traditionnelles', 'Babouches traditionnelles en cuir de Fès, brodées à la main. Confortables et élégantes.', 350.00, 6, 'babouches_fes.jpg', 'foto/babouches_fes.jpg', NULL, 25, 0.5, 'Pointures 36-44', 'Cuir de chèvre', 'Broderie manuelle', '2-3 jours', FALSE),
('Vase en céramique bleue', 'Vase en céramique avec motifs bleus traditionnels de Fès. Parfait pour la décoration.', 320.00, 1, 'vase_ceramique.jpg', 'foto/vase_ceramique.jpg', 1, 18, 1.2, '25x15 cm', 'Argile émaillée', 'Tournage et émaillage', '3-4 jours', FALSE),
('Bracelet berbère argent', 'Bracelet en argent avec motifs géométriques traditionnels. Ajustable et élégant.', 280.00, 4, 'bracelet_berbere.jpg', 'foto/bracelet_berbere.jpg', 4, 30, 0.2, '18 cm', 'Argent 925', 'Ciselure et gravure', '2-3 jours', FALSE),
('Boîte en bois sculpté', 'Boîte en bois de thuya sculptée à la main avec motifs floraux. Parfaite pour ranger bijoux ou épices.', 420.00, 5, 'boite_bois_sculpte.jpg', 'foto/boite_bois_sculpte.jpg', 5, 12, 0.8, '15x10x8 cm', 'Bois de thuya', 'Sculpture manuelle', '1 semaine', FALSE),
('Coussin brodé', 'Coussin brodé à la main avec motifs traditionnels. Rembourrage en laine naturelle.', 180.00, 2, 'coussin_brode.jpg', 'foto/coussin_brode.jpg', 2, 35, 0.6, '40x40 cm', 'Laine et coton', 'Broderie manuelle', '3-4 jours', FALSE);

-- Insertion de quelques avis
INSERT INTO avis (produit_id, nom_client, email, note, commentaire, approuve) VALUES
(1, 'Marie Dubois', 'marie.dubois@email.com', 5, 'Superbe tajine, très belle qualité et finition parfaite. Je recommande !', TRUE),
(2, 'Ahmed Benali', 'ahmed.benali@email.com', 5, 'Tapis magnifique, exactement comme sur la photo. Livraison rapide.', TRUE),
(3, 'Sophie Martin', 'sophie.martin@email.com', 4, 'Belle lanterne, très bien finie. Un peu plus petite que prévu mais très jolie.', TRUE),
(1, 'Jean Dupont', 'jean.dupont@email.com', 5, 'Excellent rapport qualité-prix. Le tajine est parfait pour la cuisine.', TRUE);

-- Mise à jour des notes moyennes des produits
UPDATE produits p SET 
    note_moyenne = (SELECT AVG(note) FROM avis a WHERE a.produit_id = p.id AND a.approuve = TRUE),
    nombre_avis = (SELECT COUNT(*) FROM avis a WHERE a.produit_id = p.id AND a.approuve = TRUE);

-- Insertion de quelques messages de contact
INSERT INTO messages (nom, email, telephone, sujet, message) VALUES
('Karim Alami', 'karim.alami@email.com', '+212 6 11 22 33 44', 'Demande de renseignements', 'Bonjour, je souhaiterais avoir plus d\'informations sur vos tapis Beni Ouarain. Pouvez-vous me contacter ?'),
('Sarah Johnson', 'sarah.johnson@email.com', '+1 555 123 4567', 'Commande spéciale', 'Hello, I would like to order a custom lantern. Is it possible to make one with specific colors ?');

-- Insertion d'abonnés newsletter
INSERT INTO newsletter (email, nom) VALUES
('client1@email.com', 'Client 1'),
('client2@email.com', 'Client 2'),
('client3@email.com', 'Client 3');

-- ===== CRÉATION DES INDEX POUR LES PERFORMANCES =====
CREATE INDEX idx_produits_categorie ON produits(categorie_id);
CREATE INDEX idx_produits_artisan ON produits(artisan_id);
CREATE INDEX idx_produits_prix ON produits(prix);
CREATE INDEX idx_produits_vedette ON produits(en_vedette);
CREATE INDEX idx_avis_produit ON avis(produit_id);
CREATE INDEX idx_messages_statut ON messages(statut);
CREATE INDEX idx_messages_date ON messages(date_envoi);

-- ===== CRÉATION D'UN UTILISATEUR DÉDIÉ (optionnel) =====
-- DÉCOMMENTEZ LES LIGNES SUIVANTES SI VOUS VOULEZ CRÉER UN UTILISATEUR DÉDIÉ
-- CREATE USER 'dourmaroc_user'@'localhost' IDENTIFIED BY 'motdepasse123';
-- GRANT ALL PRIVILEGES ON dourmaroc.* TO 'dourmaroc_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ===== MESSAGE DE CONFIRMATION =====
SELECT 'Base de données Dour Maroc créée avec succès !' AS message;
SELECT COUNT(*) AS nombre_produits FROM produits;
SELECT COUNT(*) AS nombre_artisans FROM artisans;