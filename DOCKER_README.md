# GroFresh Docker Setup

This guide will help you run the GroFresh Laravel application using Docker containers.

## Prerequisites

- Docker installed on your system
- Docker Compose installed on your system

## Quick Start

1. **Build and start the application:**
   ```bash
   ./docker-run.sh start
   ```

2. **Access the application:**
   - Web Application: http://localhost:8000
   - phpMyAdmin: http://localhost:8080 (username: root, password: root_password)

## Docker Services

The setup includes the following services:

- **app**: Laravel application (PHP 8.1 + Apache)
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache server
- **phpmyadmin**: Web-based MySQL administration tool

## Management Commands

Use the provided script for easy management:

```bash
# Start the application
./docker-run.sh start

# Stop the application
./docker-run.sh stop

# Restart the application
./docker-run.sh restart

# Build Docker images
./docker-run.sh build

# View application logs
./docker-run.sh logs

# Open shell in app container
./docker-run.sh shell

# Open MySQL shell
./docker-run.sh mysql

# Clean up Docker resources
./docker-run.sh clean
```

## Manual Docker Commands

If you prefer using Docker Compose directly:

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in app container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
```

## Database Setup

The MySQL database will be automatically initialized with:
- Database: `grofresh`
- Username: `grofresh_user`
- Password: `grofresh_password`
- Root password: `root_password`

If you have an SQL dump file at `installation/v4.1.sql`, it will be automatically imported during the first startup.

## Environment Configuration

The application will automatically:
1. Copy `.env.example` to `.env` if `.env` doesn't exist
2. Update database configuration for Docker
3. Generate application key
4. Run migrations
5. Cache configuration for production

## Volumes and Data Persistence

- MySQL data is persisted in a Docker volume
- Application storage and uploaded assets are mounted as volumes
- Logs and cache are stored in the container

## Troubleshooting

### Application not starting
```bash
# Check logs
./docker-run.sh logs

# Rebuild containers
./docker-run.sh build
./docker-run.sh start
```

### Database connection issues
```bash
# Check if MySQL is running
docker-compose ps

# Check MySQL logs
docker-compose logs mysql
```

### Permission issues
```bash
# Fix storage permissions
./docker-run.sh shell
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Clear cache and config
```bash
./docker-run.sh shell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Development vs Production

This setup is configured for production use. For development:

1. Change `APP_ENV=local` and `APP_DEBUG=true` in docker-compose.yml
2. Mount the entire application directory as a volume for live code changes
3. Use `composer install` instead of `composer install --no-dev`

## Security Notes

- Change default passwords in production
- Update the `APP_KEY` in your `.env` file
- Configure proper firewall rules
- Use HTTPS in production with a reverse proxy like Nginx
