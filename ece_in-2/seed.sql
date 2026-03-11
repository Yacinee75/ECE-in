USE ece_in;
INSERT INTO users (name, email, password, role, bio) VALUES
('Admin ECE',   'admin@ece.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',  'Administrateur de la plateforme ECE In.'),
('Alice Martin','alice@ece.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author', 'Étudiante en ING3 spécialité Data & IA.'),
('Bob Leroy',   'bob@ece.fr',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author', 'Ingénieur logiciel passionné par le cloud.');

INSERT INTO posts (user_id, content, type, visibility) VALUES
(1, 'Bienvenue sur ECE In, le réseau professionnel de la communauté ECE Paris !', 'status', 'public'),
(2, 'Je viens de terminer mon stage chez Capgemini, une expérience incroyable !', 'status', 'public'),
(3, 'Quelqu'un a des retours sur les offres chez Thales cette année ?', 'status', 'public');

INSERT INTO connections (user_id, friend_id) VALUES (1,2),(1,3),(2,3);

INSERT INTO jobs (user_id, title, company, location, description) VALUES
(1, 'Ingénieur DevOps', 'Capgemini', 'Paris', 'Rejoignez notre équipe DevOps pour des projets d'envergure nationale.'),
(1, 'Data Scientist', 'Thales', 'Massy', 'Poste en CDI pour analyser des données complexes dans le domaine défense.');

INSERT INTO notifications (user_id, message) VALUES
(2, 'Bob Leroy a aimé votre publication.'),
(3, 'Alice Martin vous a ajouté à son réseau.');
