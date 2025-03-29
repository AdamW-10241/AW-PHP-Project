USE mariadb;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS BoardGame_Artist;
DROP TABLE IF EXISTS BoardGame_Designer;
DROP TABLE IF EXISTS BoardGame_Publisher;
DROP TABLE IF EXISTS Artist;
DROP TABLE IF EXISTS Designer;
DROP TABLE IF EXISTS Publisher;
DROP TABLE IF EXISTS BoardGame;
DROP TABLE IF EXISTS Account;

-- Create Account table
CREATE TABLE IF NOT EXISTS Account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset VARCHAR(255),
    active TINYINT(1) DEFAULT 1,
    last_seen DATETIME,
    created DATETIME,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create BoardGame table
CREATE TABLE IF NOT EXISTS BoardGame (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    tagline TEXT,
    year INT,
    description TEXT,
    player_range VARCHAR(50),
    age_range VARCHAR(50),
    playtime_range VARCHAR(50),
    image VARCHAR(255),
    tags TEXT,
    visible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Publisher table
CREATE TABLE IF NOT EXISTS Publisher (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Designer table
CREATE TABLE IF NOT EXISTS Designer (
    designer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Artist table
CREATE TABLE IF NOT EXISTS Artist (
    artist_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create BoardGame_Publisher junction table
CREATE TABLE IF NOT EXISTS BoardGame_Publisher (
    boardgame_id INT,
    publisher_id INT,
    PRIMARY KEY (boardgame_id, publisher_id),
    FOREIGN KEY (boardgame_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (publisher_id) REFERENCES Publisher(publisher_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create BoardGame_Designer junction table
CREATE TABLE IF NOT EXISTS BoardGame_Designer (
    boardgame_id INT,
    designer_id INT,
    PRIMARY KEY (boardgame_id, designer_id),
    FOREIGN KEY (boardgame_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (designer_id) REFERENCES Designer(designer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create BoardGame_Artist junction table
CREATE TABLE IF NOT EXISTS BoardGame_Artist (
    boardgame_id INT,
    artist_id INT,
    PRIMARY KEY (boardgame_id, artist_id),
    FOREIGN KEY (boardgame_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create news table
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Account(id) ON DELETE CASCADE
);

-- Create favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Account(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, game_id)
);

-- Create blog_posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES Account(id) ON DELETE CASCADE
);

-- Create comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Account(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);

-- Create newsletter_subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert sample publishers
INSERT INTO Publisher (name) VALUES
('Roxley Games'),
('Space Cowboys'),
('North Star Games'),
('Stonemaier Games'),
('CMON Limited'),
('Czech Games Edition'),
('Fantasy Flight Games'),
('Stronghold Games'),
('Rio Grande Games'),
('Pandasaurus Games');

-- Insert sample designers
INSERT INTO Designer (first_name, last_name) VALUES
('Martin', 'Wallace'),
('Alexander', 'Pfister'),
('Wolfgang', 'Warsch'),
('Elizabeth', 'Hargrave'),
('Vital', 'Lacerda'),
('Rob', 'Daviau'),
('Corey', 'Konieczka'),
('Matt', 'Leacock'),
('Jacob', 'Fryxelius'),
('Klaus', 'Teuber');

-- Insert sample artists
INSERT INTO Artist (first_name, last_name) VALUES
('Lina', 'Cossette'),
('Vincent', 'Dutrait'),
('Dennis', 'Lohausen'),
('Andrew', 'Bosley'),
('Fernando', 'Soto'),
('Chris', 'Quilliams'),
('Kwanchai', 'Moria'),
('David', 'Aguilera'),
('Jakub', 'Rozalski'),
('Andrew', 'Navaro');

-- Insert sample board games
INSERT INTO BoardGame (title, tagline, year, description, player_range, age_range, playtime_range, image, tags) VALUES
('Brass: Birmingham', 'A strategic economic game set in the heart of the Industrial Revolution', 2018, 'Brass: Birmingham is an economic strategy game sequel to Martin Wallace''s 2007 masterpiece, Brass. Birmingham tells the story of competing entrepreneurs in Birmingham during the industrial revolution, between the years of 1770-1870.', '2-4', '14+', '100-120', 'board_1.png', 'strategy,economic,industrial-revolution'),
('Echoes of Time', 'A cooperative time-travel adventure', 2023, 'In Echoes of Time, players work together to solve puzzles and prevent a catastrophic event by traveling through different time periods. Each player has unique abilities that must be used strategically to overcome challenges.', '2-4', '12+', '60-90', 'board_2.png', 'cooperative,puzzle,time-travel'),
('Crafting the Cosmos', 'Build your own universe', 2023, 'In this creative tile-laying game, players take on the role of cosmic architects, creating unique galaxies and star systems. Each decision affects the balance and beauty of your cosmic creation.', '2-4', '10+', '45-60', 'board_3.png', 'tile-laying,abstract,space'),
('Quacks of Quedlinburg', 'A push-your-luck potion brewing game', 2018, 'Players are quack doctors making potions by drawing ingredients from their bag. The more ingredients you add, the more points you can score, but be careful not to add too many cherry bombs!', '2-4', '10+', '45', 'board_4.png', 'push-your-luck,dice-rolling,set-collection'),
('Defenders of Wild', 'Protect the wilderness in this strategic card game', 2022, 'As defenders of the wild, players must work together to protect endangered species and their habitats. Use your unique abilities and resources to combat environmental threats.', '1-4', '10+', '45-60', 'board_5.png', 'cooperative,card-game,environmental'),
('Pirates of Maracaibo', 'A swashbuckling adventure on the high seas', 2023, 'Set sail in the Caribbean as a pirate captain, trading goods, recruiting crew members, and engaging in naval battles. Build your reputation and become the most feared pirate on the seas!', '2-4', '12+', '60-90', 'board_6.png', 'adventure,trading,combat'),
('ARCS: Conflict and Collapse in the Reach', 'A strategic space opera', 2023, 'In this epic space strategy game, players lead factions in a struggle for control of the Reach. Manage resources, form alliances, and engage in tactical combat in this deep strategic experience.', '2-4', '14+', '120-180', 'board_7.png', 'strategy,space-opera,combat'),
('Galactic Cruise', 'A luxury space tourism adventure', 2023, 'Welcome aboard the most luxurious cruise ship in the galaxy! Players manage their own space cruise line, catering to alien tourists and ensuring their satisfaction across various destinations.', '2-4', '10+', '60-90', 'board_8.png', 'economic,management,space'),
('SETI: Search for Extraterrestrial Intelligence', 'A scientific exploration game', 2023, 'Players work as SETI researchers, analyzing signals from space and making groundbreaking discoveries. Balance your research between different types of signals and manage your limited resources.', '1-4', '12+', '45-60', 'board_9.png', 'cooperative,science,resource-management'),
('Terraforming Mars', 'Transform the Red Planet', 2016, 'In Terraforming Mars, players take on the role of corporations working together to make Mars habitable. Play project cards, build a production engine, and compete for milestones and awards.', '1-5', '12+', '120', 'board_10.png', 'strategy,engine-building,science'),
('Monkey Palace', 'A primate management game', 2023, 'Run your own primate sanctuary! Care for different species of monkeys, manage their habitats, and ensure their well-being while attracting visitors to your sanctuary.', '2-4', '10+', '45-60', 'board_11.png', 'management,animals,economic'),
('Deep Rock Galactic: The Board Game', 'A cooperative mining adventure', 2023, 'Based on the popular video game, players work together as space dwarves mining valuable resources on a hostile alien planet. Fight off creatures, gather resources, and complete missions.', '1-4', '12+', '60-90', 'board_12.png', 'cooperative,adventure,combat');

-- Link board games with publishers
INSERT INTO BoardGame_Publisher (boardgame_id, publisher_id) VALUES
(1, 1), -- Brass: Birmingham - Roxley Games
(2, 2), -- Echoes of Time - Space Cowboys
(3, 6), -- Crafting the Cosmos - Czech Games Edition
(4, 3), -- Quacks - North Star Games
(5, 4), -- Defenders of Wild - Stonemaier Games
(6, 5), -- Pirates of Maracaibo - CMON Limited
(7, 7), -- ARCS - Fantasy Flight Games
(8, 8), -- Galactic Cruise - Stronghold Games
(9, 9), -- SETI - Rio Grande Games
(10, 10), -- Terraforming Mars - Pandasaurus Games
(11, 1), -- Monkey Palace - Roxley Games
(12, 5); -- Deep Rock Galactic - CMON Limited

-- Link board games with designers
INSERT INTO BoardGame_Designer (boardgame_id, designer_id) VALUES
(1, 1), -- Brass: Birmingham - Martin Wallace
(2, 2), -- Echoes of Time - Alexander Pfister
(3, 6), -- Crafting the Cosmos - Rob Daviau
(4, 3), -- Quacks - Wolfgang Warsch
(5, 4), -- Defenders of Wild - Elizabeth Hargrave
(6, 5), -- Pirates of Maracaibo - Vital Lacerda
(7, 7), -- ARCS - Corey Konieczka
(8, 8), -- Galactic Cruise - Matt Leacock
(9, 9), -- SETI - Jacob Fryxelius
(10, 10), -- Terraforming Mars - Klaus Teuber
(11, 1), -- Monkey Palace - Martin Wallace
(12, 2); -- Deep Rock Galactic - Alexander Pfister

-- Link board games with artists
INSERT INTO BoardGame_Artist (boardgame_id, artist_id) VALUES
(1, 1), -- Brass: Birmingham - Lina Cossette
(2, 2), -- Echoes of Time - Vincent Dutrait
(3, 6), -- Crafting the Cosmos - Chris Quilliams
(4, 3), -- Quacks - Dennis Lohausen
(5, 4), -- Defenders of Wild - Andrew Bosley
(6, 5), -- Pirates of Maracaibo - Fernando Soto
(7, 7), -- ARCS - Kwanchai Moria
(8, 8), -- Galactic Cruise - David Aguilera
(9, 9), -- SETI - Jakub Rozalski
(10, 10), -- Terraforming Mars - Andrew Navaro
(11, 1), -- Monkey Palace - Lina Cossette
(12, 2); -- Deep Rock Galactic - Vincent Dutrait 