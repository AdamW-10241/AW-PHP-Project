USE mariadb;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS news;
-- Drop junction tables first
DROP TABLE IF EXISTS BoardGame_Artist;
DROP TABLE IF EXISTS BoardGame_Designer;
DROP TABLE IF EXISTS BoardGame_Publisher;
-- Drop main tables after their dependencies
DROP TABLE IF EXISTS BoardGame;
DROP TABLE IF EXISTS Artist;
DROP TABLE IF EXISTS Designer;
DROP TABLE IF EXISTS Publisher;
DROP TABLE IF EXISTS Account;

-- Create Account table if it doesn't exist
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

-- Create reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Insert sample publishers only if table is empty
INSERT INTO Publisher (name)
SELECT * FROM (
    SELECT 'Roxley Games' UNION ALL
    SELECT 'Space Cowboys' UNION ALL
    SELECT 'North Star Games' UNION ALL
    SELECT 'Stonemaier Games' UNION ALL
    SELECT 'CMON Limited' UNION ALL
    SELECT 'Czech Games Edition' UNION ALL
    SELECT 'Fantasy Flight Games' UNION ALL
    SELECT 'Stronghold Games' UNION ALL
    SELECT 'Rio Grande Games' UNION ALL
    SELECT 'Pandasaurus Games' UNION ALL
    SELECT 'Next Move Games' UNION ALL
    SELECT 'Dire Wolf' UNION ALL
    SELECT 'Capstone Games' UNION ALL
    SELECT 'Maestro Media' UNION ALL
    SELECT 'Lookout Games' UNION ALL
    SELECT 'Schmidt Spiele' UNION ALL
    SELECT 'Flatout Games' UNION ALL
    SELECT 'Gamewright' UNION ALL
    SELECT 'Studio71' UNION ALL
    SELECT 'AdMagic Games' UNION ALL
    SELECT 'Ludically'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM Publisher);

-- Insert sample designers only if table is empty
INSERT INTO Designer (first_name, last_name)
SELECT * FROM (
    SELECT 'Martin', 'Wallace' UNION ALL
    SELECT 'Alexander', 'Pfister' UNION ALL
    SELECT 'Wolfgang', 'Warsch' UNION ALL
    SELECT 'Elizabeth', 'Hargrave' UNION ALL
    SELECT 'Vital', 'Lacerda' UNION ALL
    SELECT 'Rob', 'Daviau' UNION ALL
    SELECT 'Corey', 'Konieczka' UNION ALL
    SELECT 'Matt', 'Leacock' UNION ALL
    SELECT 'Jacob', 'Fryxelius' UNION ALL
    SELECT 'Klaus', 'Teuber' UNION ALL
    SELECT 'Michael', 'Kiesling' UNION ALL
    SELECT 'Paul', 'Dennen' UNION ALL
    SELECT 'Dennis', 'Chan' UNION ALL
    SELECT 'Helge', 'Ostertag' UNION ALL
    SELECT 'Edmund', 'McMillen' UNION ALL
    SELECT 'Uwe', 'Rosenberg' UNION ALL
    SELECT 'Kevin', 'Russ' UNION ALL
    SELECT 'Phil', 'Walker-Harding' UNION ALL
    SELECT 'Elan', 'Lee' UNION ALL
    SELECT 'Christophe', 'Boelinger'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM Designer);

-- Insert sample artists only if table is empty
INSERT INTO Artist (first_name, last_name)
SELECT * FROM (
    SELECT 'Lina', 'Cossette' UNION ALL
    SELECT 'Vincent', 'Dutrait' UNION ALL
    SELECT 'Dennis', 'Lohausen' UNION ALL
    SELECT 'Andrew', 'Bosley' UNION ALL
    SELECT 'Fernando', 'Soto' UNION ALL
    SELECT 'Chris', 'Quilliams' UNION ALL
    SELECT 'Kwanchai', 'Moria' UNION ALL
    SELECT 'David', 'Aguilera' UNION ALL
    SELECT 'Jakub', 'Rozalski' UNION ALL
    SELECT 'Andrew', 'Navaro' UNION ALL
    SELECT 'Philippe', 'Guerin' UNION ALL
    SELECT 'Clay', 'Brooks' UNION ALL
    SELECT 'Franz', 'Vohwinkel' UNION ALL
    SELECT 'Lukas', 'Siegmon' UNION ALL
    SELECT 'Krystal', 'Fleming' UNION ALL
    SELECT 'Carrie', 'Cantwell' UNION ALL
    SELECT 'Leon', 'Schiffer' UNION ALL
    SELECT 'Beth', 'Sobel' UNION ALL
    SELECT 'Nan', 'Rangsima' UNION ALL
    SELECT 'Matthew', 'Inman' UNION ALL
    SELECT 'Vincent', 'Boulanger'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM Artist);

-- Insert sample board games only if table is empty
INSERT INTO BoardGame (title, tagline, year, description, player_range, age_range, playtime_range, image, tags)
SELECT * FROM (
    SELECT 'Brass: Birmingham', 'A strategic economic game set in the heart of the Industrial Revolution', 2018, 'Brass: Birmingham is an economic strategy game sequel to Martin Wallace''s 2007 masterpiece, Brass. Birmingham tells the story of competing entrepreneurs in Birmingham during the industrial revolution, between the years of 1770-1870.', '2-4', '14+', '100-120', 'board_1.png', 'strategy,economic,industrial-revolution' UNION ALL
    SELECT 'Echoes of Time', 'A cooperative time-travel adventure', 2023, 'In Echoes of Time, players work together to solve puzzles and prevent a catastrophic event by traveling through different time periods. Each player has unique abilities that must be used strategically to overcome challenges.', '2-4', '12+', '60-90', 'board_2.png', 'cooperative,puzzle,time-travel' UNION ALL
    SELECT 'Crafting the Cosmos', 'Build your own universe', 2023, 'In this creative tile-laying game, players take on the role of cosmic architects, creating unique galaxies and star systems. Each decision affects the balance and beauty of your cosmic creation.', '2-4', '10+', '45-60', 'board_3.png', 'tile-laying,abstract,space' UNION ALL
    SELECT 'Quacks of Quedlinburg', 'A push-your-luck potion brewing game', 2018, 'Players are quack doctors making potions by drawing ingredients from their bag. The more ingredients you add, the more points you can score, but be careful not to add too many cherry bombs!', '2-4', '10+', '45', 'board_4.png', 'push-your-luck,dice-rolling,set-collection' UNION ALL
    SELECT 'Defenders of Wild', 'Protect the wilderness in this strategic card game', 2022, 'As defenders of the wild, players must work together to protect endangered species and their habitats. Use your unique abilities and resources to combat environmental threats.', '1-4', '10+', '45-60', 'board_5.png', 'cooperative,card-game,environmental' UNION ALL
    SELECT 'Pirates of Maracaibo', 'A swashbuckling adventure on the high seas', 2023, 'Set sail in the Caribbean as a pirate captain, trading goods, recruiting crew members, and engaging in naval battles. Build your reputation and become the most feared pirate on the seas!', '2-4', '12+', '60-90', 'board_6.png', 'adventure,trading,combat' UNION ALL
    SELECT 'ARCS: Conflict and Collapse in the Reach', 'A strategic space opera', 2023, 'In this epic space strategy game, players lead factions in a struggle for control of the Reach. Manage resources, form alliances, and engage in tactical combat in this deep strategic experience.', '2-4', '14+', '120-180', 'board_7.png', 'strategy,space-opera,combat' UNION ALL
    SELECT 'Galactic Cruise', 'A luxury space tourism adventure', 2023, 'Welcome aboard the most luxurious cruise ship in the galaxy! Players manage their own space cruise line, catering to alien tourists and ensuring their satisfaction across various destinations.', '2-4', '10+', '60-90', 'board_8.png', 'economic,management,space' UNION ALL
    SELECT 'SETI: Search for Extraterrestrial Intelligence', 'A scientific exploration game', 2023, 'Players work as SETI researchers, analyzing signals from space and making groundbreaking discoveries. Balance your research between different types of signals and manage your limited resources.', '1-4', '12+', '45-60', 'board_9.png', 'cooperative,science,resource-management' UNION ALL
    SELECT 'Terraforming Mars', 'Transform the Red Planet', 2016, 'In Terraforming Mars, players take on the role of corporations working together to make Mars habitable. Play project cards, build a production engine, and compete for milestones and awards.', '1-5', '12+', '120', 'board_10.png', 'strategy,engine-building,science' UNION ALL
    SELECT 'Monkey Palace', 'A primate management game', 2023, 'Run your own primate sanctuary! Care for different species of monkeys, manage their habitats, and ensure their well-being while attracting visitors to your sanctuary.', '2-4', '10+', '45-60', 'board_11.png', 'management,animals,economic' UNION ALL
    SELECT 'Deep Rock Galactic: The Board Game', 'A cooperative mining adventure', 2023, 'Based on the popular video game, players work together as space dwarves mining valuable resources on a hostile alien planet. Fight off creatures, gather resources, and complete missions.', '1-4', '12+', '60-90', 'board_12.png', 'cooperative,adventure,combat' UNION ALL
    SELECT 'Azul', 'Artfully embellish the walls of your palace by drafting the most beautiful tiles.', 2017, 'In the game Azul, players take turns drafting colored tiles from suppliers to their player board. Later in the round, players score points based on how they placed their tiles to decorate the palace.', '2-4', '8+', '30-45', 'board_13.png', 'tile-laying,abstract,strategy' UNION ALL
    SELECT 'Clank!: Catacombs', 'Deck-building adventure meets tile-laying in the newest incarnation of Clank!', 2022, 'Prisoners are counting on you to free them. Ghosts, once disturbed, may haunt you to death. Despite all that, it is time to leave the board behind with Clank! Catacombs, a standalone deck-building adventure.', '2-4', '13+', '45-90', 'board_14.png', 'strategy,cooperative,adventure' UNION ALL
    SELECT 'Beyond the Sun', 'Collectively develop a tech tree to fuel new discoveries and colonize space.', 2020, 'Beyond the Sun is a space civilization game in which players collectively decide the technological progress of humankind at the dawn of the Spacefaring Era.', '2-4', '14+', '60-120', 'board_15.png', 'strategy,space,management' UNION ALL
    SELECT 'Age of Innovation', 'Terraform the world to expand your faction and create innovations on the way.', 2023, 'Twelve factions, each with unique characteristics, populate this world of varying terrains. Here you will compete to erect buildings and merge them into cities.', '1-5', '14+', '40-200', 'board_16.png', 'strategy,economic,management' UNION ALL
    SELECT 'The Binding of Isaac: Four Souls', 'The Binding of Isaac multiplayer card game.', 2018, 'Experience the haunted and harrowing world of The Binding of Isaac: Four Souls yourself in this faithful adaptation. Collect treasure, gather loot, defeat monsters, and be the first to collect four souls.', '2-4', '13+', '30-60', 'board_17.png', 'push-your-luck,dice-rolling,card-game' UNION ALL
    SELECT 'Patchwork', 'Piece together a quilt and leave no holes to become the button master.', 2014, 'Two players compete to build the most aesthetic (and high-scoring) patchwork quilt on a personal 9x9 game board.', '2', '8+', '15-30', 'board_18.png', 'abstract,economic,environmental' UNION ALL
    SELECT 'That''s Pretty Clever!', 'Draft dice to mark numbers on your scoresheet, comboing bonuses to power your points.', 2018, 'Choose your dice cleverly to enter them into the matching colored areas on your score sheet, putting together tricky chain-scoring opportunities, and racking up the points!', '1-4', '8+', '30', 'board_19.png', 'push-your-luck,dice-rolling,management' UNION ALL
    SELECT 'Calico', 'Sew a quilt, collect buttons, attract cats!', 2020, 'In Calico, players compete to sew the coziest quilt as they collect and place patches of different colors and patterns. Make a pattern and attract the cuddliest cats!.', '1-4', '10+', '30-45', 'board_20.png', 'abstract,animals,family' UNION ALL
    SELECT 'Sushi Go Party!', 'Pass sushi around a bigger table and take the best dishes.', 2016, 'Customize each game by choosing à la carte from a menu of more than twenty delectable dishes.', '2-8', '8+', '20', 'board_21.png', 'family,card-game,strategy' UNION ALL
    SELECT 'Tapeworm', 'Race to be the first to empty your hand by creating & cutting up tapeworms.', 2020, 'In Tapeworm, players race to be the first to get rid of all of their cards by connecting and growing the wriggling masses of different colored worm bodies!', '2-4', '8+', '15-30', 'board_22.png', 'family,management,card-game' UNION ALL
    SELECT 'Exploding Kittens', 'Ask for favors, attack friends, see the future- whatever it takes to avoid exploding!', 2015, 'Exploding Kittens is a kitty-powered version of Russian Roulette. Players take turns drawing cards until someone draws an exploding kitten and loses the game!', '2-5', '7+', '15', 'board_23.png', 'animals,management,card-game' UNION ALL
    SELECT 'Archipelago', 'Settlers work together and compete for resources to survive the new world.', 2012, 'As Renaissance European powers competing in the exploration of a Pacific or Caribbean archipelago, players will explore territories, harvest resources and betray!', '2-5', '14+', '30-240', 'board_24.png', 'strategy,economic,exploration'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM BoardGame);

-- Link board games with publishers only if no links exist
INSERT INTO BoardGame_Publisher (boardgame_id, publisher_id)
VALUES
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
    (12, 5), -- Deep Rock Galactic - CMON Limited
    (13, 11), -- Azul - Next Move Games
    (14, 12), -- Clank!: Catacombs - Dire Wolf
    (15, 9), -- Beyond the Sun - Rio Grande Games
    (16, 13), -- Age of Innovation - Capstone Games
    (17, 14), -- The Binding of Isaac: Four Souls - Maestro Media
    (18, 15), -- Patchwork - Lookout Games
    (19, 16), -- That's Pretty Clever! - Schmidt Spiele
    (20, 17), -- Calico - Flatout Games
    (21, 18), -- Sushi Go Party! - Gamewright
    (22, 19), -- Tapeworm - Studio71
    (23, 20), -- Exploding Kittens - AdMagic Games
    (24, 21); -- Archipelago - Ludically

-- Link board games with designers only if no links exist
INSERT INTO BoardGame_Designer (boardgame_id, designer_id)
VALUES
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
    (12, 2), -- Deep Rock Galactic - Alexander Pfister
    (13, 11), -- Azul - Michael Kiesling
    (14, 12), -- Clank!: Catacombs - Paul Dennen
    (15, 13), -- Beyond the Sun - Dennis Chan
    (16, 14), -- Age of Innovation - Helge Ostertag
    (17, 15), -- The Binding of Isaac: Four Souls - Edmund McMillen
    (18, 16), -- Patchwork - Uwe Rosenberg
    (19, 3), -- That's Pretty Clever! - Wolfgang Warsch
    (20, 17), -- Calico - Kevin Russ
    (21, 18), -- Sushi Go Party! - Phil Walker-Harding
    (22, 15), -- Tapeworm - Edmund McMillen
    (23, 19), -- Exploding Kittens - Elan Lee
    (24, 20); -- Archipelago - Christophe Boelinger

-- Link board games with artists only if no links exist
INSERT INTO BoardGame_Artist (boardgame_id, artist_id)
VALUES
    (1, 1), -- Brass: Birmingham - Lina Cossette
    (2, 2), -- Echoes of Time - Vincent Dutrait
    (3, 3), -- Crafting the Cosmos - Dennis Lohausen
    (4, 4), -- Quacks - Andrew Bosley
    (5, 5), -- Defenders of Wild - Fernando Soto
    (6, 6), -- Pirates of Maracaibo - Chris Quilliams
    (7, 7), -- ARCS - Kwanchai Moria
    (8, 8), -- Galactic Cruise - David Aguilera
    (9, 9), -- SETI - Jakub Rozalski
    (10, 10), -- Terraforming Mars - Andrew Navaro
    (11, 1), -- Monkey Palace - Lina Cossette
    (12, 2), -- Deep Rock Galactic - Vincent Dutrait
    (13, 6), -- Azul - Chris Quilliams
    (13, 11), -- Azul - Philippe Guerin
    (14, 12), -- Clank!: Catacombs - Clay Brooks
    (15, 13), -- Beyond the Sun - Franz Vohwinkel
    (16, 14), -- Age of Innovation - Lukas Siegmon
    (17, 15), -- The Binding of Isaac: Four Souls - Krystal Fleming
    (18, 16), -- Patchwork - Carrie Cantwell
    (19, 17), -- That's Pretty Clever! - Leon Schiffer
    (20, 18), -- Calico - Beth Sobel
    (21, 19), -- Sushi Go Party! - Nan Rangsima
    (22, 15), -- Tapeworm - Krystal Fleming
    (23, 20), -- Exploding Kittens - Matthew Inman
    (24, 21); -- Archipelago - Vincent Boulanger

-- Create news table
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES BoardGame(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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