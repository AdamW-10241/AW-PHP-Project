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