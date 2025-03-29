# Board Games Website

A PHP-based web application for managing and reviewing board games.

## Features

- User authentication (login/signup)
- Board game catalog with detailed information
- Game reviews and ratings
- User favorites
- News and blog sections
- Newsletter subscription

## Requirements

- PHP 8.0 or higher
- MariaDB 10.4 or higher
- Composer for PHP dependencies
- Web server (Apache/Nginx)

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up your database:
   - Create a MariaDB database
   - Import the database schema:
     ```bash
     mysql -u your_username -p your_database < database.sql
     ```
4. Configure your environment:
   - Copy `.env.example` to `.env`
   - Update database credentials in `.env`

## Database Structure

The application uses the following main tables:
- `Account` - User accounts and authentication
- `BoardGame` - Main board game information
- `games` - Featured and popular games
- `news` - Website news and announcements
- `reviews` - User reviews and ratings
- `favorites` - User's favorite games
- `blog_posts` - Blog content
- `comments` - User comments on blog posts

## Development

The project uses:
- Twig for templating
- Bootstrap 5 for styling
- Font Awesome for icons

## License

This project is licensed under the MIT License.

## About This Project

This is a PHP web application for board game enthusiasts to discover, review, and discuss their favorite tabletop games. I built this to create a community space where gamers can track their collections and share their experiences.

## Getting Started

### Prerequisites

Before you begin, make sure you have:
- PHP 8.2+ (I recommend 8.2.8 or later)
- MariaDB 10.4+ (works with 10.4.28)
- Composer (for dependency management)
- Docker & Docker Compose (optional but makes setup easier)

### Installation Steps

1. **Configure Environment**
   ```bash
   cp .env.dist .env
   ```
   Edit the `.env` file with your preferred settings. For Docker, these defaults work well:
   ```ini
   DBHOST="db"
   DBUSER="mariadb"
   DBPASSWORD="mariadb"
   DBNAME="mariadb"
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   Run the initialization script:
   ```bash
   php setup_database.php
   ```

   This creates all tables and populates them with sample board game data.

### Running the Application

#### Docker Method (Recommended)
```bash
docker-compose up -d
```
The site will be live at `http://localhost` with phpMyAdmin at `http://localhost:8082`

#### Manual Setup
1. Configure your web server (Apache/Nginx)
2. Update `.env` with your local DB credentials
3. Access through your configured domain/port

## What's Included

### Sample Data
We've pre-loaded the database with 12 popular games:

Each game has:
- High-quality cover art
- Descriptions
- Player count and duration info
- Related designers/publishers

### Key Features
- User authentication system
- Game database browser
- Review functionality
- Community discussion area
- Blog section

## Development Notes

### VS Code Users
We've included dev container configuration - just:
1. Install the Remote-Containers extension
2. Open the project
3. Click "Reopen in Container"

### Common Issues

**Database Problems?**
- Check container status: `docker ps`
- Verify `.env` credentials match your DB
- Try re-running `setup_database.php`

**Missing Images?**
- Ensure `assets/cover_images` exists
- Check file permissions

**phpMyAdmin Not Working?**
- Confirm port 8082 is free
- Try `docker-compose restart`

## Why I Built This

As a board game enthusiast, I wanted to create a better way to track my collection and share reviews with friends. This project combines my love for gaming with my passion for web development.

Feel free to contribute or use this as a base for your own projects!