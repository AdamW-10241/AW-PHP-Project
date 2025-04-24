# Board Games Website

A PHP-based web application for managing and reviewing board games.

## Quick Start

1. Clone the repository
2. Start Docker:
   ```bash
   docker-compose up -d
   ```
3. Import the database:
   ```bash
   docker exec -it devcontainer-app-1 php import_db.php
   ```

The application will be available at http://localhost:80

## Default Admin Account
- Email: admin@example.com
- Password: admin123

## Troubleshooting

If you encounter issues:
```bash
# Check Docker logs
docker-compose logs

# Check database connection
docker-compose exec db mysql -u mariadb -pmariadb mariadb

# Check PHP error logs
docker-compose exec app tail -f /var/log/apache2/error.log
```

# AW-PHP-Project
A PHP project for **Advanced Web** - Semester 1 2025.
Developed by Adam Williams & Aleksei Perov.

# Configuration
Rename .env.example to .env and change values to suit your environment.

# Docker Development Setup
The project includes a Docker development environment in the `.devcontainer` directory. There are two ways to run it:

1. **Standard Setup** (Recommended):
   ```bash
   docker-compose -f .devcontainer/docker-compose.yml up -d
   ```
   This will expose:
   - Application: http://localhost:80
   - phpMyAdmin: http://localhost:8082

2. **Alternative Setup** (Original):
   Use the backup configuration file `.devcontainer/docker-compose.yml.backup` if you prefer the original network configuration.

Both setups will work, but the standard setup is recommended for easier access to the application.

# Database Setup
After starting the containers, run the database import script:
```bash
docker exec devcontainer-app-1 php import_db.php
```

This will create all necessary tables and import sample data.